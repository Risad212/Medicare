<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doctor;

class DoctorController extends Controller
{
    public function show($id)
    {
        $doctor = Doctor::findOrFail($id);
        return view('frontend.doctors.show', compact('doctor'));
    }
}