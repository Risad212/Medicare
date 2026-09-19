<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrescriptionRequest;
use App\Mail\PrescriptionReadyMail;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Services\PatientNotifier;
use App\Services\PdfService;
use App\Services\PrescriptionService;
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

        return view('backend.doctor-dashboard.prescriptions.index', compact('prescriptions'));
    }

    /**
     * Show the form for creating a new prescription.
     */
    public function create()
    {
        $doctor = $this->doctor();

        $appointments = Appointment::with('timeSlot')
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', [1, 2])
            ->latest()
            ->get();

        return view('backend.doctor-dashboard.prescriptions.create', compact('doctor', 'appointments'));
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

        $prescription->load(['items', 'appointment.timeSlot']);

        return view('backend.doctor-dashboard.prescriptions.show', compact('prescription'));
    }

    /**
     * Show the form for editing a prescription.
     */
    public function edit(Prescription $prescription)
    {
        $doctor = $this->doctor();
        abort_if($prescription->doctor_id !== $doctor->id, 403, 'Unauthorized prescription access.');

        $appointments = Appointment::with('timeSlot')
            ->where('doctor_id', $doctor->id)
            ->whereIn('status', [1, 2])
            ->latest()
            ->get();

        $prescription->load(['items']);

        return view('backend.doctor-dashboard.prescriptions.edit', compact('doctor', 'appointments', 'prescription'));
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

    private function notifyPatient(Prescription $prescription): void
    {
        // In-app alert for the patient's own account (if linked).
        PatientNotifier::prescriptionReady($prescription);

        if ($prescription->email) {
            Mail::to($prescription->email)->queue(new PrescriptionReadyMail($prescription->load('items', 'doctor')));
        }
    }
}
