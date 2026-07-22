<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\TimeSlot;

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
            'doctor_id'     => 'required',
            'patient_name'  => 'required',
            'phone'         => 'required',
            'visit_type'    => 'required',
            'appointment_date' => 'required|date',
            'time_slot_id'  => 'required|exists:time_slots,id',   
            'gender'        => 'required',
        ]);

        Appointment::create([
            'doctor_id'        => $request->doctor_id,
            'patient_name'     => $request->patient_name,
            'age'              => $request->age,
            'gender'           => $request->gender,
            'phone'            => $request->phone,
            'visit_type'       => $request->visit_type,
            'appointment_date' => $request->appointment_date,
            'time_slot_id'     => $request->time_slot_id,  
            'status'           => 0,
        ]);

        return back()->with('success', 'Appointment saved');
    }

    public function getAvailableSlots(Request $request)
    {
        $allSlots = TimeSlot::where('status', 1)->orderBy('time')->get();

        $bookedSlotIds = [];

        if ($request->doctor_id && $request->date) {
            $bookedSlotIds = Appointment::where('doctor_id', $request->doctor_id)
                                        ->where('appointment_date', $request->date)
                                        ->where('status', '!=', 2)
                                        ->pluck('time_slot_id')
                                        ->toArray();
        }

        return response()->json([
            'slots'         => $allSlots,
            'bookedSlotIds' => $bookedSlotIds
        ]);
    }
}