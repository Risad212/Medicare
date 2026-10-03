<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\ServiceSetting;
use Illuminate\Support\Str;

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

    public function show(Service $service)
    {
        abort_if((int) $service->status !== 1, 404);

        $others = Service::where('status', 1)->where('id', '!=', $service->id)->orderBy('order')->take(3)->get();
        $pageTitle = $service->title;
        $seo = (object) [
            'meta_title' => $service->title.' — MediCare Hospital',
            'meta_description' => Str::limit((string) $service->description, 150),
            'meta_keywords' => '',
        ];

        return view('frontend.services.show', compact('service', 'others', 'seo', 'pageTitle'));
    }
}
