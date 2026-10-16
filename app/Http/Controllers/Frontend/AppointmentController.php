<?php

namespace App\Http\Controllers\Frontend;

use App\Events\AppointmentBooked;
use App\Events\AppointmentCancelled;
use App\Http\Controllers\Controller;
use App\Mail\AppointmentBookedMail;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\SeoSetting;
use App\Models\TimeSlot;
use App\Services\PatientNotifier;
use App\Services\StaffNotifier;
use App\Support\Module;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function index()
    {
        $doctors = Doctor::where('status', 1)->orderBy('name')->get();
        $availableSlots = TimeSlot::where('status', 1)->orderBy('time')->get();
        $seo = SeoSetting::where('page', 'appointment')->first();

        return view('frontend.appointment', compact('doctors', 'availableSlots', 'seo'));
    }

    public function store(Request $request)
    {
        $messages = [
            'doctor_id.required' => __('messages.appointment.validation.required'),
            'doctor_id.exists' => __('messages.appointment.validation.invalid'),
            'patient_name.required' => __('messages.appointment.validation.required'),
            'patient_name.string' => __('messages.appointment.validation.invalid'),
            'patient_name.max' => __('messages.appointment.validation.invalid'),
            'age.integer' => __('messages.appointment.validation.invalid'),
            'age.min' => __('messages.appointment.validation.age_range'),
            'age.max' => __('messages.appointment.validation.age_range'),
            'phone.required' => __('messages.appointment.validation.required'),
            'phone.max' => __('messages.appointment.validation.invalid'),
            'phone.regex' => __('messages.appointment.validation.phone_format'),
            'visit_type.required' => __('messages.appointment.validation.required'),
            'visit_type.in' => __('messages.appointment.validation.invalid'),
            'consultation_mode.required' => __('messages.appointment.validation.required'),
            'consultation_mode.in' => __('messages.appointment.validation.invalid'),
            'email.required_if' => __('messages.appointment.validation.email_required'),
            'email.email' => __('messages.appointment.validation.email_invalid'),
            'email.max' => __('messages.appointment.validation.email_invalid'),
            'appointment_date.required' => __('messages.appointment.validation.required'),
            'appointment_date.date' => __('messages.appointment.validation.invalid'),
            'appointment_date.after_or_equal' => __('messages.appointment.validation.date_future'),
            'time_slot_id.required' => __('messages.appointment.validation.required'),
            'time_slot_id.exists' => __('messages.appointment.validation.invalid'),
            'gender.required' => __('messages.appointment.validation.required'),
            'gender.in' => __('messages.appointment.validation.invalid'),
        ];

        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_name' => 'required|string|max:255',
            'age' => 'nullable|integer|min:0|max:120',
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s]+$/',
            'visit_type' => 'required|in:1,2,3',
            'consultation_mode' => [Module::enabled('zoom') ? 'required' : 'sometimes', Rule::in(
                Module::enabled('zoom') ? ['in_person', 'telehealth'] : ['in_person'],
            )],
            'email' => 'required_if:consultation_mode,telehealth|nullable|email|max:255',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot_id' => 'required|exists:time_slots,id',
            'gender' => 'required|in:1,2,3',
        ], $messages);

        // Ensure doctor is active and slot is active
        $doctor = Doctor::where('id', $request->doctor_id)->where('status', 1)->firstOrFail();
        $slot = TimeSlot::where('id', $request->time_slot_id)->where('status', 1)->firstOrFail();

        // Guard: the slot must be in this doctor's schedule for that date.
        // off-days and weekly availability are enforced server-side so a
        // crafted request can't bypass the frontend picker.
        if (! $doctor->openSlotIdsForDate($request->appointment_date)->contains((int) $slot->id)) {
            throw ValidationException::withMessages(['time_slot_id' => __('messages.appointment.validation.slot_unavailable')]);
        }

        // Guard: a slot whose time has already passed today can't be booked
        // (only applies when the chosen date is today). Parse instead of
        // strict string compare so 'Y-m-d H:i' inputs can't bypass it.
        if ($slot->time && $this->isTodayDate($request->appointment_date)) {
            try {
                $slotTime = Carbon::parse($slot->time);
                if ($slotTime->lt(now())) {
                    throw ValidationException::withMessages(['time_slot_id' => __('messages.appointment.validation.time_passed')]);
                }
            } catch (\Throwable $e) {
                if ($e instanceof ValidationException) {
                    throw $e;
                }
                // Unparseable time format — fail closed so a bad slot
                // value can never bypass the past-slot guard.
                throw ValidationException::withMessages(['time_slot_id' => __('messages.appointment.validation.slot_unavailable')]);
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
                // Serialize concurrent bookings for the same doctor: the
                // exists() check below locks nothing when zero rows match,
                // so lock the parent doctor row first. The unique index
                // (appointments_booking_unique) remains the final guard.
                Doctor::whereKey($request->doctor_id)->lockForUpdate()->first();

                $exists = Appointment::where('doctor_id', $request->doctor_id)
                    ->where('appointment_date', $request->appointment_date)
                    ->where('time_slot_id', $request->time_slot_id)
                    ->where('status', '!=', 3)
                    ->lockForUpdate()
                    ->exists();
                if ($exists) {
                    throw ValidationException::withMessages(['time_slot_id' => __('messages.appointment.validation.slot_booked')]);
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
                    'consultation_mode' => $request->input('consultation_mode', 'in_person'),
                    'appointment_date' => $request->appointment_date,
                    'time_slot_id' => $request->time_slot_id,
                    'status' => 0,
                ]);
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'appointments_booking_unique')) {
                throw ValidationException::withMessages(['time_slot_id' => __('messages.appointment.validation.slot_booked')]);
            }
            throw $e;
        }

        // Queue the confirmation email with the cancellation link, only if
        // the patient provided an email (guests without an account and
        // without email won't get one — they'd need to log in instead).
        // Queued, not sent: the booking is already committed and an SMTP
        // outage must not surface as a 500 after the slot is taken.
        if ($appointment->email) {
            Mail::to($appointment->email)
                ->locale($appointment->user?->locale ?? app()->getLocale())
                ->queue(new AppointmentBookedMail($appointment));
        }

        // In-app alert for staff (admins + the appointment's doctor).
        StaffNotifier::appointmentBooked($appointment);

        // In-app alert for the patient's own account (if they have one).
        PatientNotifier::appointmentBooked($appointment);

        event(new AppointmentBooked($appointment));

        return back()->with('success', __('messages.appointment.success'));
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
            // Inactive doctors must not expose bookable slots: the store
            // endpoint rejects them (404), so the picker has to agree and
            // report every slot as unavailable instead of "open".
            $doctor = Doctor::where('status', 1)->find($request->doctor_id);

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
            } else {
                $unavailableSlotIds = $allSlots->pluck('id')->values();
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
                    // Fail closed: an unparseable slot time is treated as
                    // passed so it can never be booked via the picker.
                    return true;
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
            return back()->with('error', __('messages.public.cancel_closed'));
        }

        $appointment->update([
            'status' => 3,
            'cancellation_token' => null,
        ]);

        // Let staff know the slot is free again.
        StaffNotifier::appointmentStatusChanged($appointment->refresh(), 3);

        // Let the cancelling patient's account know (no-op for guests).
        PatientNotifier::appointmentStatusChanged($appointment, 3);

        event(new AppointmentCancelled($appointment));

        return back()->with('success', __('messages.public.cancel_success'));
    }

    /**
     * Show a guest cancellation confirmation page via the emailed token link.
     */
    public function showCancelByToken(string $token)
    {
        $appointment = Appointment::where('cancellation_token', $token)->firstOrFail();

        // Expiry: token invalid 48h after the end of the appointment day.
        // The date column stores midnight, so anchor on endOfDay for a
        // deterministic window regardless of slot time.
        if ($this->cancelTokenExpired($appointment)) {
            abort(410, __('messages.public.link_expired'));
        }

        if (! in_array($appointment->status, [0, 1])) {
            abort(410, __('messages.public.cancel_closed'));
        }

        return view('frontend.appointment-cancel', compact('appointment'));
    }

    /**
     * Cancel by guest (no account) via the unique emailed token.
     */
    public function cancelByToken(string $token)
    {
        $appointment = Appointment::where('cancellation_token', $token)->firstOrFail();

        if ($this->cancelTokenExpired($appointment)) {
            return redirect()->route('appointment')->with('error', __('messages.public.link_expired'));
        }

        if (! in_array($appointment->status, [0, 1])) {
            return redirect()->route('appointment')->with('error', __('messages.public.cancel_closed'));
        }

        $appointment->update(['status' => 3, 'cancellation_token' => null]);

        // Let staff know the slot is free again.
        StaffNotifier::appointmentStatusChanged($appointment->refresh(), 3);

        // In-app alert for a linked patient account (guests skip this).
        PatientNotifier::appointmentStatusChanged($appointment, 3);

        event(new AppointmentCancelled($appointment));

        return redirect()->route('appointment')->with('success', __('messages.public.cancel_success'));
    }

    private function cancelTokenExpired(Appointment $appointment): bool
    {
        if (! $appointment->appointment_date) {
            return false;
        }

        try {
            $deadline = Carbon::parse($appointment->appointment_date)->endOfDay()->addHours(48);
        } catch (\Throwable) {
            return false;
        }

        return now()->greaterThan($deadline);
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
