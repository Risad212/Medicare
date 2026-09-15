<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Services\AppointmentNotifier;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ? str_replace(['%', '_'], ['\%', '\_'], $request->search) : null;
        $appointments = Appointment::with('doctor')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('patient_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhereHas('doctor', function ($doctor) use ($search) {
                            $doctor->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.appointments.index', compact('appointments'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create()
    {
        $doctors = Doctor::where('status', 1)->get();
        $timeSlots = TimeSlot::where('status', 1)->get();

        return view('backend.appointments.create', compact('doctors', 'timeSlots'));
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'name' => 'required|string|max:255',
            'age' => 'nullable|integer|min:0|max:120',
            'gender' => 'required|in:1,2,3',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'visit_type' => 'required|in:1,2,3',
            'date' => 'required|date|after_or_equal:today',
        ]);

        $doctor = Doctor::find($validated['doctor_id']);
        if ($doctor && ! $doctor->openSlotIdsForDate($validated['date'])->contains((int) $validated['time_slot_id'])) {
            return back()->withErrors([
                'time_slot_id' => 'This slot is not available for the selected doctor on that date.',
            ])->withInput();
        }

        // Prevent double-booking the same doctor/date/slot — check + create
        // inside a transaction with a row lock so two concurrent POSTs
        // can't both pass the check (TOCTOU). The unique index is the
        // final guard; convert a race loss into a validation error.
        try {
            $appointment = DB::transaction(function () use ($validated) {
                $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
                    ->where('appointment_date', $validated['date'])
                    ->where('time_slot_id', $validated['time_slot_id'])
                    ->where('status', '!=', 3)
                    ->lockForUpdate()
                    ->exists();

                if ($conflict) {
                    throw ValidationException::withMessages(['doctor_id' => 'This doctor/date/slot is already booked.']);
                }

                return Appointment::create([
                    'doctor_id' => $validated['doctor_id'],
                    'time_slot_id' => $validated['time_slot_id'],
                    'patient_name' => $validated['name'],
                    'age' => $validated['age'] ?? null,
                    'gender' => $validated['gender'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'visit_type' => $validated['visit_type'],
                    'appointment_date' => $validated['date'],
                    'status' => 0,
                ]);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'appointments_booking_unique')) {
                return back()->withErrors(['doctor_id' => 'This doctor/date/slot is already booked.'])->withInput();
            }
            throw $e;
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $doctors = Doctor::where('status', 1)->get();

        return view('backend.appointments.edit', compact('appointment', 'doctors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'name' => 'required|string|max:255',
            'age' => 'nullable|integer|min:0|max:120',
            'gender' => 'required|in:1,2,3',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'visit_type' => 'required|in:1,2,3',
            // No after_or_equal:today here: admins must be able to finish or
            // correct past appointments (e.g. mark yesterday's visit completed).
            'date' => 'required|date',
            'status' => 'required|in:0,1,2,3',
        ]);

        $appointment = Appointment::findOrFail($id);

        $wasAlreadyApproved = (int) $appointment->getOriginal('status') === 1;

        // Guard: moving to a non-cancelled status must not double-book
        // the same doctor/date/slot (slot itself isn't editable here, so
        // reuse the appointment's current time_slot_id).
        if ((int) $validated['status'] !== 3) {
            $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
                ->where('appointment_date', $validated['date'])
                ->where('time_slot_id', $appointment->time_slot_id)
                ->where('status', '!=', 3)
                ->where('id', '!=', $appointment->id)
                ->exists();
            if ($conflict) {
                return back()->withErrors(['doctor_id' => 'This doctor/date/slot is already booked.'])->withInput();
            }

            // Enforce the doctor's weekly schedule / off-days when the slot
            // actually changes or a cancelled booking is being re-activated.
            // Routine completion of an unchanged past booking stays allowed.
            // getOriginal() returns a Carbon instance under the date cast, so
            // compare normalised date strings instead of mixed types.
            $slotChanged = (int) $validated['doctor_id'] !== (int) $appointment->getOriginal('doctor_id')
                || Carbon::parse($validated['date'])->toDateString()
                    !== Carbon::parse($appointment->getOriginal('appointment_date'))->toDateString();
            $reActivating = (int) $appointment->getOriginal('status') === 3;

            if ($slotChanged || $reActivating) {
                $doctor = Doctor::find($validated['doctor_id']);
                if ($doctor && ! $doctor->openSlotIdsForDate($validated['date'])->contains((int) $appointment->time_slot_id)) {
                    return back()->withErrors([
                        'time_slot_id' => 'This slot is not available for the selected doctor on that date.',
                    ])->withInput();
                }
            }
        }

        try {
            DB::transaction(function () use ($appointment, $validated) {
                $data = [
                    'doctor_id' => $validated['doctor_id'],
                    'patient_name' => $validated['name'],
                    'age' => $validated['age'] ?? null,
                    'gender' => $validated['gender'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'visit_type' => $validated['visit_type'],
                    'appointment_date' => $validated['date'],
                    'status' => $validated['status'],
                ];
                // Invalidate the emailed guest-cancel link once cancelled.
                if ((int) $validated['status'] === 3) {
                    $data['cancellation_token'] = null;
                } elseif ((int) $appointment->getOriginal('status') === 3 && empty($appointment->cancellation_token)) {
                    // Re-activating: issue a fresh cancel link since the old one was nulled.
                    $data['cancellation_token'] = Str::random(40);
                }
                $appointment->update($data);
            });
        } catch (QueryException $e) {
            // Race loser hits the unique index after passing the exists()
            // check above — report as validation, not a 500.
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'appointments_booking_unique')) {
                return back()->withErrors(['doctor_id' => 'This doctor/date/slot is already booked.'])->withInput();
            }
            throw $e;
        }

        if ((int) $validated['status'] === 1 && ! $wasAlreadyApproved) {
            app(AppointmentNotifier::class)->notifyApproved($appointment->refresh());
        }

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment updated successfully!');
    }

    /**
     * Quick status change from the dashboard queue (approve / cancel).
     */
    public function updateStatus(Request $request, string $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:0,1,2,3',
        ]);

        $appointment = Appointment::findOrFail($id);

        $wasAlreadyApproved = (int) $appointment->getOriginal('status') === 1;

        // Re-activating a cancelled booking must pass the same guards as a
        // full update: no double-book, and the slot must still be open.
        if ((int) $appointment->getOriginal('status') === 3 && (int) $validated['status'] !== 3) {
            $conflict = Appointment::where('doctor_id', $appointment->doctor_id)
                ->where('appointment_date', $appointment->appointment_date->toDateString())
                ->where('time_slot_id', $appointment->time_slot_id)
                ->where('status', '!=', 3)
                ->where('id', '!=', $appointment->id)
                ->exists();
            if ($conflict) {
                return back()->withErrors(['status' => 'This doctor/date/slot is already booked.'])->withInput();
            }

            $doctor = Doctor::find($appointment->doctor_id);
            if ($doctor && ! $doctor->openSlotIdsForDate($appointment->appointment_date->toDateString())->contains((int) $appointment->time_slot_id)) {
                return back()->withErrors([
                    'status' => 'This slot is not available for the selected doctor on that date.',
                ])->withInput();
            }
        }

        $data = ['status' => $validated['status']];
        if ((int) $validated['status'] === 3) {
            $data['cancellation_token'] = null;
        } elseif ((int) $appointment->getOriginal('status') === 3 && empty($appointment->cancellation_token)) {
            $data['cancellation_token'] = Str::random(40);
        }
        try {
            $appointment->update($data);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'appointments_booking_unique')) {
                return back()->withErrors(['status' => 'This doctor/date/slot is already booked.'])->withInput();
            }
            throw $e;
        }

        if ((int) $validated['status'] === 1 && ! $wasAlreadyApproved) {
            app(AppointmentNotifier::class)->notifyApproved($appointment->refresh());
        }

        return back()->with('success', 'Appointment status updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return back()->with('success', 'Appointment deleted successfully!');
    }
}
