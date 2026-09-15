<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentBookedMail;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function index()
    {
        $doctors = Doctor::where('status', 1)->get();
        $availableSlots = TimeSlot::where('status', 1)->orderBy('time')->get();

        return view('frontend.appointment', compact('doctors', 'availableSlots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_name' => 'required|string|max:255',
            'age' => 'nullable|integer|min:0|max:120',
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s]+$/',
            'email' => 'nullable|email|max:255',
            'visit_type' => 'required|in:1,2,3',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot_id' => 'required|exists:time_slots,id',
            'gender' => 'required|in:1,2,3',
        ]);

        // Ensure doctor is active and slot is active
        $doctor = Doctor::where('id', $request->doctor_id)->where('status', 1)->firstOrFail();
        $slot = TimeSlot::where('id', $request->time_slot_id)->where('status', 1)->firstOrFail();

        // Guard: the slot must be in this doctor's schedule for that date.
        // off-days and weekly availability are enforced server-side so a
        // crafted request can't bypass the frontend picker.
        if (! $doctor->openSlotIdsForDate($request->appointment_date)->contains((int) $slot->id)) {
            throw ValidationException::withMessages(['time_slot_id' => 'The selected slot is not available for this doctor on the chosen date.']);
        }

        // Guard: a slot whose time has already passed today can't be booked
        // (only applies when the chosen date is today). Parse instead of
        // strict string compare so 'Y-m-d H:i' inputs can't bypass it.
        if ($slot->time && $this->isTodayDate($request->appointment_date)) {
            try {
                $slotTime = Carbon::parse($slot->time);
                if ($slotTime->lt(now())) {
                    throw ValidationException::withMessages(['time_slot_id' => 'The selected time has already passed for today.']);
                }
            } catch (\Throwable $e) {
                if ($e instanceof ValidationException) {
                    throw $e;
                }
                // Unparseable time format — fall through and let booking proceed.
            }
        }

        // Prevent double-booking same doctor/date/slot — check + create
        // inside a transaction with a row lock so two concurrent POSTs
        // can't both pass the check (TOCTOU). The unique index
        // (appointments_booking_unique) is the final guard: if two
        // requests race past the check, the loser hits a 23000
        // QueryException which we convert to a validation error.
        try {
            $appointment = DB::transaction(function () use ($request) {
                $exists = Appointment::where('doctor_id', $request->doctor_id)
                    ->where('appointment_date', $request->appointment_date)
                    ->where('time_slot_id', $request->time_slot_id)
                    ->where('status', '!=', 3)
                    ->lockForUpdate()
                    ->exists();
                if ($exists) {
                    throw ValidationException::withMessages(['time_slot_id' => 'This slot is already booked.']);
                }

                return Appointment::create([
                    'user_id' => auth()->id(),
                    'doctor_id' => $request->doctor_id,
                    'patient_name' => $request->patient_name,
                    'age' => $request->age,
                    'gender' => $request->gender,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'cancellation_token' => Str::random(40),
                    'visit_type' => $request->visit_type,
                    'appointment_date' => $request->appointment_date,
                    'time_slot_id' => $request->time_slot_id,
                    'status' => 0,
                ]);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'appointments_booking_unique')) {
                throw ValidationException::withMessages(['time_slot_id' => 'This slot is already booked.']);
            }
            throw $e;
        }

        // Queue the confirmation email with the cancellation link, only if
        // the patient provided an email (guests without an account and
        // without email won't get one — they'd need to log in instead).
        // Queued, not sent: the booking is already committed and an SMTP
        // outage must not surface as a 500 after the slot is taken.
        if ($appointment->email) {
            Mail::to($appointment->email)->queue(new AppointmentBookedMail($appointment));
        }

        return back()->with('success', 'Appointment saved');
    }

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'nullable|exists:doctors,id',
            'date' => 'nullable|date|after_or_equal:today',
        ]);

        $allSlots = TimeSlot::where('status', 1)->orderBy('time')->get();

        $bookedSlotIds = [];
        $unavailableSlotIds = collect();

        if ($request->doctor_id && $request->date) {
            $doctor = Doctor::find($request->doctor_id);

            if ($doctor) {
                $bookedSlotIds = Appointment::where('doctor_id', $request->doctor_id)
                    ->where('appointment_date', $request->date)
                    ->where('status', '!=', 3)
                    ->pluck('time_slot_id')
                    ->toArray();

                // Slots closed by the doctor's weekly schedule or off-days
                // are reported separately from booked ones.
                $openIds = $doctor->openSlotIdsForDate($request->date);
                $unavailableSlotIds = $allSlots
                    ->pluck('id')
                    ->diff($openIds)
                    ->values();
            }
        }

        // A slot whose time has already passed today can no longer be booked.
        if ($request->date && $this->isTodayDate($request->date)) {
            $passedIds = $allSlots->filter(function ($slot) {
                if (! $slot->time) {
                    return false;
                }
                try {
                    return Carbon::parse($slot->time)->lt(now());
                } catch (\Throwable) {
                    return false;
                }
            })->pluck('id')->all();

            if ($passedIds) {
                $unavailableSlotIds = $unavailableSlotIds->merge($passedIds)->unique()->values();
            }
        }

        return response()->json([
            'slots' => $allSlots,
            'bookedSlotIds' => $bookedSlotIds,
            'unavailableSlotIds' => $unavailableSlotIds,
        ]);
    }

    /**
     * Cancel by a logged-in patient (owns the appointment via user_id).
     */
    public function cancel(Appointment $appointment)
    {
        if ($appointment->user_id !== auth()->id()) {
            abort(403);
        }

        if (! in_array($appointment->status, [0, 1])) {
            return back()->with('error', 'This appointment can no longer be cancelled.');
        }

        $appointment->update([
            'status' => 3,
            'cancellation_token' => null,
        ]);

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    /**
     * Show a guest cancellation confirmation page via the emailed token link.
     */
    public function showCancelByToken(string $token)
    {
        $appointment = Appointment::where('cancellation_token', $token)->firstOrFail();

        // Expiry: token invalid 48h after appointment date or if already cancelled
        if ($appointment->appointment_date && now()->diffInHours($appointment->appointment_date, false) < -48) {
            abort(410, 'Cancellation link has expired.');
        }

        if (! in_array($appointment->status, [0, 1])) {
            abort(410, 'This appointment can no longer be cancelled.');
        }

        return view('frontend.appointment-cancel', compact('appointment'));
    }

    /**
     * Cancel by guest (no account) via the unique emailed token.
     */
    public function cancelByToken(string $token)
    {
        $appointment = Appointment::where('cancellation_token', $token)->firstOrFail();

        if ($appointment->appointment_date && now()->diffInHours($appointment->appointment_date, false) < -48) {
            return redirect()->route('appointment')->with('error', 'Cancellation link has expired.');
        }

        if (! in_array($appointment->status, [0, 1])) {
            return redirect()->route('appointment')->with('error', 'This appointment can no longer be cancelled.');
        }

        $appointment->update(['status' => 3, 'cancellation_token' => null]);

        return redirect()->route('appointment')->with('success', 'Your appointment has been cancelled.');
    }

    private function isTodayDate(mixed $value): bool
    {
        if (empty($value)) {
            return false;
        }
        try {
            return Carbon::parse($value)->isToday();
        } catch (\Throwable) {
            return false;
        }
    }
}
