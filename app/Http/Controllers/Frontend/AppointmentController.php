<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentBookedMail;

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
            'doctor_id'        => 'required',
            'patient_name'     => 'required',
            'phone'            => 'required',
            'email'            => 'nullable|email',
            'visit_type'       => 'required',
            'appointment_date' => 'required|date',
            'time_slot_id'     => 'required|exists:time_slots,id',   
            'gender'           => 'required',
        ]);

        $appointment = Appointment::create([
            'user_id'            => auth()->id(),
            'doctor_id'          => $request->doctor_id,
            'patient_name'       => $request->patient_name,
            'age'                => $request->age,
            'gender'             => $request->gender,
            'phone'              => $request->phone,
            'email'              => $request->email,
            'cancellation_token' => Str::random(40),
            'visit_type'         => $request->visit_type,
            'appointment_date'   => $request->appointment_date,
            'time_slot_id'       => $request->time_slot_id,  
            'status'             => 0,
        ]);

        // Send a confirmation email with the cancellation link, only if
        // the patient provided an email (guests without an account and
        // without email won't get one — they'd need to log in instead).
        if ($appointment->email) {
            Mail::to($appointment->email)->send(new AppointmentBookedMail($appointment));
        }

        return back()->with('success', 'Appointment saved');
    }

    public function getAvailableSlots(Request $request)
    {
        $allSlots = TimeSlot::where('status', 1)->orderBy('time')->get();

        $bookedSlotIds = [];

        if ($request->doctor_id && $request->date) {
            $bookedSlotIds = Appointment::where('doctor_id', $request->doctor_id)
                                        ->where('appointment_date', $request->date)
                                        ->where('status', '!=', 3)
                                        ->pluck('time_slot_id')
                                        ->toArray();
        }

        return response()->json([
            'slots'         => $allSlots,
            'bookedSlotIds' => $bookedSlotIds
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

        if (!in_array($appointment->status, [0, 1])) {
            return back()->with('error', 'This appointment can no longer be cancelled.');
        }

        $appointment->update([
            'status' => 3,
        ]);

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    /**
     * Show a guest cancellation confirmation page via the emailed token link.
     */
    public function showCancelByToken(string $token)
    {
        $appointment = Appointment::where('cancellation_token', $token)->firstOrFail();

        return view('frontend.appointment-cancel', compact('appointment'));
    }

    /**
     * Cancel by guest (no account) via the unique emailed token.
     */
    public function cancelByToken(string $token)
    {
        $appointment = Appointment::where('cancellation_token', $token)->firstOrFail();

        if (!in_array($appointment->status, [0, 1])) {
            return redirect()->route('appointment')->with('error', 'This appointment can no longer be cancelled.');
        }

        $appointment->update(['status' => 3]);

        return redirect()->route('appointment')->with('success', 'Your appointment has been cancelled.');
    }
}