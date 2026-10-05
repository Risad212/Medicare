<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\ServiceSetting;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        $seo = SeoSetting::where('page', 'service')->first();
        $service = ServiceSetting::first() ?? new ServiceSetting;
        $serviceItems = Service::where('status', 1)->orderBy('order')->get();
        $pageTitle = 'Our Services';

        return Inertia::render('Public/Services/Index', [
            'pageTitle' => $pageTitle,
            'serviceSettings' => $service->toArray(),
            'services' => $serviceItems->map(fn (Service $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'description' => $item->description,
                'icon' => $item->icon,
                'buttonText' => $item->button_text,
                'buttonUrl' => $item->button_url,
            ])->values(),
            'seo' => [
                'title' => $seo?->meta_title,
                'description' => $seo?->meta_description,
                'keywords' => $seo?->meta_keywords,
            ],
        ]);
    }

    public function show(Service $service): Response
    {
        abort_if((int) $service->status !== 1, 404);

        $others = Service::where('status', 1)->where('id', '!=', $service->id)->orderBy('order')->take(3)->get();
        $pageTitle = $service->title;
        $seo = (object) [
            'meta_title' => $service->title.' — MediCare Hospital',
            'meta_description' => Str::limit((string) $service->description, 150),
            'meta_keywords' => '',
        ];

        return Inertia::render('Public/Services/Show', [
            'service' => [
                'id' => $service->id,
                'title' => $service->title,
                'slug' => $service->slug,
                'description' => $service->description,
                'icon' => $service->icon,
            ],
            'others' => $others->map(fn (Service $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug,
                'description' => $item->description,
                'icon' => $item->icon,
                'buttonText' => $item->button_text,
                'buttonUrl' => $item->button_url,
            ])->values(),
            'seo' => [
                'title' => $seo->meta_title,
                'description' => $seo->meta_description,
                'keywords' => $seo->meta_keywords,
            ],
        ]);
    }
}
