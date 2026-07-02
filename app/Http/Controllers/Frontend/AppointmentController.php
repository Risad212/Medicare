<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\Doctor;

class AppointmentController extends Controller
{

    public function index()
     {
        $doctors = Doctor::where('status', 1)->get();
        return view('frontend.appointment', compact('doctors'));
     }

    public function store(Request $request)
     { 
       $request->validate([
        'doctor_id'  => 'required',
        'name'       => 'required',
        'phone'      => 'required',
        'visit_type' => 'required',
        'date'       => 'required|date',
        'gender'     => 'required',
    ]);
       Appointment::create([
        'doctor_id'        => $request->doctor_id,
        'patient_name'     => $request->name,
        'age'              => $request->age,
        'gender'           => $request->gender,
        'phone'            => $request->phone,
        'visit_type'       => $request->visit_type,
        'appointment_date' => $request->date,
        'status'           => 0,
    ]);
     return back()->with('success', 'Appointment saved');
    }
}