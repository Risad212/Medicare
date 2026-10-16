<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\HomeSetting;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\Slider;
use App\Support\Module;

class HomeController extends Controller
{
    public function index()
    {
        $homeSetting = HomeSetting::first();
        $directoryOn = Module::enabled('directory');
        $sliders = $directoryOn ? Slider::all() : collect();
        $doctors = Doctor::where('status', 1)->latest()->take(3)->get();
        $recentBlogs = Blog::where('status', 1)->latest()->take(4)->get();
        $serviceItems = $directoryOn ? Service::where('status', 1)->orderBy('order')->get() : collect();
        $seo = SeoSetting::where('page', 'home')->first();

        return view('frontend.index', compact(
            'homeSetting',
            'sliders',
            'doctors',
            'recentBlogs',
            'serviceItems',
            'seo'
        ));
    }
}
