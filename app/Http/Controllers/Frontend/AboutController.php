<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\SeoSetting;

class AboutController extends Controller
{
     public function index()
      {
        $doctors = Doctor::latest()->take(3)->get();
        $seo     = SeoSetting::where('page', 'about')->first();

        return view('frontend.about', compact('doctors', 'seo'));
     }
}
