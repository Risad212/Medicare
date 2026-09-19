<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AboutSetting;
use App\Models\Doctor;
use App\Models\SeoSetting;

class AboutController extends Controller
{
    public function index()
    {
        $about = AboutSetting::first() ?? new AboutSetting;
        $doctors = Doctor::where('status', 1)->latest()->take(3)->get();
        $seo = SeoSetting::where('page', 'about')->first();

        return view('frontend.about', compact('about', 'doctors', 'seo'));
    }
}
