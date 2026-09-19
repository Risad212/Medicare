<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\AppointmentNotifier;
use App\Services\PatientNotifier;
use App\Services\StaffNotifier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        $todayAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where('appointment_date', today())
            ->count();

        $totalAppointments = Appointment::where('doctor_id', $doctor->id)->count();

        $pendingAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 0)
            ->count();

        $todayAppointmentsList = Appointment::with('timeSlot')
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', today())
            ->get();

        return view('backend.doctor-dashboard.dashboard', compact(
            'doctor',
            'todayAppointments',
            'totalAppointments',
            'pendingAppointments',
            'todayAppointmentsList'
        ));
    }

    public function appointments()
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        $appointments = Appointment::with('timeSlot')
            ->where('doctor_id', $doctor->id)
            ->latest()
            ->get();

        return view('backend.doctor-dashboard.appointments', compact('appointments'));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:1,2,3',
        ]);

        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        abort_if($appointment->doctor_id !== $doctor->id, 403, 'Unauthorized appointment access.');

        $wasAlreadyApproved = (int) $appointment->getOriginal('status') === 1;
        $wasStatus = (int) $appointment->getOriginal('status');

        // Re-activating a cancelled booking must pass the same guards as a
        // full update: no double-book, and the slot must still be open.
        if ((int) $appointment->getOriginal('status') === 3 && (int) $request->status !== 3) {
            $conflict = Appointment::where('doctor_id', $appointment->doctor_id)
                ->where('appointment_date', $appointment->appointment_date->toDateString())
                ->where('time_slot_id', $appointment->time_slot_id)
                ->where('status', '!=', 3)
                ->where('id', '!=', $appointment->id)
                ->exists();
            if ($conflict) {
                return back()->withErrors(['status' => 'This slot is already booked.'])->withInput();
            }

            if (! $doctor->openSlotIdsForDate($appointment->appointment_date->toDateString())->contains((int) $appointment->time_slot_id)) {
                return back()->withErrors([
                    'status' => 'This slot is not available on that date.',
                ])->withInput();
            }
        }

        $update = ['status' => $request->status];
        if ((int) $request->status === 3) {
            $update['cancellation_token'] = null;
        } elseif ((int) $appointment->getOriginal('status') === 3 && empty($appointment->cancellation_token)) {
            $update['cancellation_token'] = Str::random(40);
        }
        try {
            $appointment->update($update);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'appointments_booking_unique')) {
                return back()->withErrors(['status' => 'This slot is already booked.'])->withInput();
            }
            throw $e;
        }

        if ((int) $request->status === 1 && ! $wasAlreadyApproved) {
            app(AppointmentNotifier::class)->notifyApproved($appointment);
        }

        if ((int) $request->status !== $wasStatus) {
            StaffNotifier::appointmentStatusChanged($appointment, (int) $request->status, (int) auth()->id());
            PatientNotifier::appointmentStatusChanged($appointment, (int) $request->status);
        }

        return back()->with('success', 'Appointment status updated!');
    }
}
