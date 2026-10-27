<?php

namespace App\Modules\Directory\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
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
        $pageTitle = __('messages.public.our_services');

        return view('directory.site-service', compact('seo', 'service', 'serviceItems', 'pageTitle'));
    }

    public function show(Service $service)
    {
        abort_if((int) $service->status !== 1, 404);

        $others = Service::where('status', 1)->where('id', '!=', $service->id)->orderBy('order')->take(3)->get();

        // "Doctors in this department": only resolvable once an admin has
        // linked the service to a department name (services.department).
        // The section stays hidden for every empty/unknown match — no
        // invented doctor data is ever rendered.
        $department = trim((string) $service->department);
        $departmentDoctors = $department !== ''
            ? Doctor::where('status', 1)->where('department', $department)->orderBy('name')->get()
            : collect();

        $pageTitle = $service->title;
        $seo = (object) [
            'meta_title' => $service->title.' — MediCare Hospital',
            'meta_description' => Str::limit((string) $service->description, 150),
            'meta_keywords' => '',
        ];

        return view('directory.site-services-show', compact('service', 'others', 'seo', 'pageTitle', 'departmentDoctors', 'department'));
    }
}
