<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HomeSetting;
use App\Models\Slider;

class HomeController extends Controller
{
    public function index()
    {
        $homeSetting = HomeSetting::first();
        $sliders     = Slider::all();

        return view('frontend.index', compact('homeSetting', 'sliders'));
    }
}