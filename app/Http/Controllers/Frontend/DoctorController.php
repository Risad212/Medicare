<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doctor;

class DoctorController extends Controller
{
    public function index()
      {
        $doctors     = Doctor::all();
        return view('frontend.doctor', compact('doctors'));
     }

    public function show($id)
    {
        $doctor = Doctor::findOrFail($id);
        return view('frontend.doctors.show', compact('doctor'));
    }
}