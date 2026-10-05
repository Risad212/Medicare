<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Support\AdminNavigation;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function appointment(): Response
    {
        $seo = SeoSetting::where('page', 'appointment')->first();

        return Inertia::render('Admin/Settings/Form', [
            'routes' => AdminNavigation::routes(),
            'type' => 'seo',
            'title' => 'Appointment SEO',
            'seo' => $seo?->only('page', 'meta_title', 'meta_description', 'meta_keywords') ?? ['page' => 'appointment'],
            'seoSubmitUrl' => route('admin.seo-settings.update'),
        ]);
    }
}
