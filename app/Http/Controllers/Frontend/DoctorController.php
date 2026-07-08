<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\SeoSetting;

class DoctorController extends Controller
{
    public function index()
      {
        $doctors = Doctor::latest()->paginate(12);
        $seo     = SeoSetting::where('page', 'doctor')->first();

        return view('frontend.doctor', compact('doctors', 'seo'));
     }

    public function show($id)
    {
        $doctor = Doctor::findOrFail($id);
        return view('frontend.doctors.show', compact('doctor'));
    }
}