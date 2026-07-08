<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HomeSetting;
use App\Models\Slider;
use App\Models\Doctor;
use App\Models\SeoSetting;

class HomeController extends Controller
{
    public function index()
    {
        $homeSetting = HomeSetting::first();
        $sliders     = Slider::all();
        $doctors     = Doctor::latest()->take(3)->get();
        $seo         = SeoSetting::where('page', 'home')->first();

        return view('frontend.index', compact(
            'homeSetting',
            'sliders',
            'doctors',
            'seo'
        ));
    }
}