<?php

namespace App\Modules\Prescriptions\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Mail\PrescriptionReadyMail;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Modules\Prescriptions\Http\Requests\PrescriptionRequest;
use App\Services\ClinicalReviewEngine;
use App\Services\PatientNotifier;
use App\Services\PdfService;
use App\Services\PrescriptionService;
use App\Support\Module;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PrescriptionController extends Controller
{
    /**
     * List prescriptions written by the current doctor.
     */
    public function index()
    {
        $doctor = $this->doctor();

        $prescriptions = Prescription::with(['appointment.timeSlot', 'items'])
            ->where('doctor_id', $doctor->id)
            ->latest()
            ->paginate(10);

        return view('prescriptions.doctor-portal.index', compact('prescriptions'));
    }

    /**
     * Searchable appointment options for the prescription picker (JSON).
     *
     * Scoped to the current doctor and capped server-side so the picker can
     * load a small, searchable window instead of every past appointment.
     */
    public function appointmentPicker(Request $request): JsonResponse
    {
        $doctor = $this->doctor();

        $q = trim((string) $request->query('q'));

        $appointments = Appointment::with(['timeSlot', 'user'])
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', [1, 2])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('patient_name', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%');
                });
            })
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn (Appointment $appointment) => [
                'id' => $appointment->id,
                'label' => '#'.$appointment->id.' - '.$appointment->patient_name.' ('.$appointment->appointment_date.')',
                'name' => $appointment->patient_name,
                'age' => $appointment->age,
                'gender' => $appointment->gender,
                'phone' => $appointment->phone,
                'email' => $appointment->email,
                'allergies' => $appointment->user?->allergies ?? '',
            ])
            ->values();

        return response()->json(['results' => $appointments]);
    }

    /**
     * Show the form for creating a new prescription.
     */
    public function create()
    {
        $doctor = $this->doctor();

        $appointments = Appointment::with(['timeSlot', 'user'])
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', [1, 2])
            ->latest()
            ->limit(50)
            ->get();

        $patientAllergies = null;

        return view('prescriptions.doctor-portal.create', compact('doctor', 'appointments', 'patientAllergies'));
    }

    /**
     * Store a newly created prescription.
     */
    public function store(PrescriptionRequest $request, PrescriptionService $service)
    {
        $doctor = $this->doctor();

        $validated = $request->validated();
        $appointment = $this->resolveAppointment($doctor, $validated['appointment_id'] ?? null);

        $prescription = $service->create($doctor, $appointment, $validated);

        $this->notifyPatient($prescription);

        return redirect()->route('doctor.prescriptions.show', $prescription)
            ->with('success', 'Prescription created successfully.');
    }

    /**
     * Display a single prescription.
     */
    public function show(Prescription $prescription)
    {
        $doctor = $this->doctor();
        abort_if($prescription->doctor_id !== $doctor->id, 403, 'Unauthorized prescription access.');

        $prescription->load(['items', 'appointment.timeSlot', 'appointment.user', 'patient']);

        $patientAllergies = $this->resolvePatientAllergies($prescription->appointment, $prescription);
        $clinicalAlerts = ClinicalReviewEngine::review($prescription->items->toArray(), $patientAllergies);

        return view('prescriptions.doctor-portal.show', compact('prescription', 'patientAllergies', 'clinicalAlerts'));
    }

    /**
     * Show the form for editing a prescription.
     */
    public function edit(Prescription $prescription)
    {
        $doctor = $this->doctor();
        abort_if($prescription->doctor_id !== $doctor->id, 403, 'Unauthorized prescription access.');

        $appointments = Appointment::with(['timeSlot', 'user'])
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', [1, 2])
            ->latest()
            ->limit(50)
            ->get();

        $prescription->load(['items', 'appointment.user', 'patient']);

        $patientAllergies = $this->resolvePatientAllergies($prescription->appointment, $prescription);
        $clinicalAlerts = ClinicalReviewEngine::review($prescription->items->toArray(), $patientAllergies);

        return view('prescriptions.doctor-portal.edit', compact('doctor', 'appointments', 'prescription', 'patientAllergies', 'clinicalAlerts'));
    }

    /**
     * Update the specified prescription in storage.
     */
    public function update(PrescriptionRequest $request, Prescription $prescription, PrescriptionService $service)
    {
        $doctor = $this->doctor();
        abort_if($prescription->doctor_id !== $doctor->id, 403, 'Unauthorized prescription access.');

        $validated = $request->validated();
        $appointment = $this->resolveAppointment($doctor, $validated['appointment_id'] ?? null);

        $service->update($prescription, $appointment, $validated);

        $this->notifyPatient($prescription);

        return redirect()->route('doctor.prescriptions.show', $prescription)
            ->with('success', 'Prescription updated successfully.');
    }

    /**
     * Remove the specified prescription from storage.
     */
    public function destroy(Prescription $prescription, PrescriptionService $service)
    {
        $doctor = $this->doctor();
        abort_if($prescription->doctor_id !== $doctor->id, 403, 'Unauthorized prescription access.');

        // Let the patient know before the record disappears from their profile.
        $prescription->loadMissing('doctor');
        PatientNotifier::prescriptionWithdrawn($prescription);

        $service->delete($prescription);

        return redirect()->route('doctor.prescriptions.index')
            ->with('success', 'Prescription deleted successfully.');
    }

    /**
     * Stream a printable PDF of the prescription.
     */
    public function pdf(Prescription $prescription, PdfService $pdf)
    {
        $doctor = $this->doctor();
        abort_if($prescription->doctor_id !== $doctor->id, 403, 'Unauthorized prescription access.');

        $prescription->load(['items', 'appointment.timeSlot']);

        return $pdf->stream('pdf.prescription', ['prescription' => $prescription], 'prescription-'.$prescription->id.'.pdf');
    }

    private function doctor(): Doctor
    {
        return Doctor::where('user_id', auth()->id())->firstOrFail();
    }

    private function resolveAppointment(Doctor $doctor, mixed $appointmentId): ?Appointment
    {
        if (! $appointmentId) {
            return null;
        }

        $appointment = Appointment::find($appointmentId);
        abort_if(! $appointment || $appointment->doctor_id !== $doctor->id, 403, 'Unauthorized appointment access.');

        return $appointment;
    }

    /**
     * Resolve allergy text for the banner. Linked account wins;
     * guest/walk-in bookings (user_id = null) return null = no banner.
     */
    private function resolvePatientAllergies(?Appointment $appointment, ?Prescription $prescription = null): ?string
    {
        if (! Module::enabled('allergy')) {
            return null;
        }

        $fromPrescription = $prescription?->patient?->allergies;
        if (is_string($fromPrescription) && trim($fromPrescription) !== '') {
            return $fromPrescription;
        }

        $fromAppointment = $appointment?->user?->allergies;
        if (is_string($fromAppointment) && trim($fromAppointment) !== '') {
            return $fromAppointment;
        }

        return null;
    }

    private function notifyPatient(Prescription $prescription): void
    {
        // In-app alert for the patient's own account (if linked).
        PatientNotifier::prescriptionReady($prescription);

        if ($prescription->email) {
            Mail::to($prescription->email)
                ->locale($prescription->patient?->locale ?? app()->getLocale())
                ->queue(new PrescriptionReadyMail($prescription->load('items', 'doctor')));
        }
    }
}
