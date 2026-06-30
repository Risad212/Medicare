<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\HomeSetting;

class HomeController extends Controller
{
    public function index()
    {
        $homeSetting = HomeSetting::first();

        return view('frontend.index', compact('homeSetting'));
    }
}