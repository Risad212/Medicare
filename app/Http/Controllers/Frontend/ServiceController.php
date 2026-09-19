<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\ServiceSetting;

class ServiceController extends Controller
{
    public function index()
    {
        $seo = SeoSetting::where('page', 'service')->first();
        $service = ServiceSetting::first() ?? new ServiceSetting;
        $serviceItems = Service::where('status', 1)->orderBy('order')->get();
        $pageTitle = 'Our Services';

        return view('frontend.service', compact('seo', 'service', 'serviceItems', 'pageTitle'));
    }
}
