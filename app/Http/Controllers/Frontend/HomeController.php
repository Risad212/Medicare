<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\HomeSetting;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\Slider;

class HomeController extends Controller
{
    public function index()
    {
        $homeSetting = HomeSetting::first();
        $sliders = Slider::all();
        $doctors = Doctor::where('status', 1)->latest()->take(3)->get();
        $recentBlogs = Blog::where('status', 1)->latest()->take(4)->get();
        $serviceItems = Service::where('status', 1)->orderBy('order')->get();
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
