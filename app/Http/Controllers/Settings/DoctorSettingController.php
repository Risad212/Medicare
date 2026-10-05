<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Support\AdminNavigation;
use Inertia\Inertia;
use Inertia\Response;

class DoctorSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function doctor(): Response
    {
        $seo = SeoSetting::where('page', 'doctor')->first();

        return Inertia::render('Admin/Settings/Form', [
            'routes' => AdminNavigation::routes(),
            'type' => 'seo',
            'title' => 'Doctor SEO',
            'seo' => $seo?->only('page', 'meta_title', 'meta_description', 'meta_keywords') ?? ['page' => 'doctor'],
            'seoSubmitUrl' => route('admin.seo-settings.update'),
        ]);
    }
}
