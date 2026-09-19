<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\SeoSetting;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::where('status', 1)->latest()->paginate(12);
        $seo = SeoSetting::where('page', 'doctor')->first();
        $pageTitle = 'Our Doctors';
        $noDoctorsMessage = 'No doctors found.';

        return view('frontend.doctor', compact('doctors', 'seo', 'pageTitle', 'noDoctorsMessage'));
    }

    public function show($id)
    {
        $doctor = Doctor::where('id', $id)->where('status', 1)->firstOrFail();

        return view('frontend.doctors.show', compact('doctor'));
    }
}
