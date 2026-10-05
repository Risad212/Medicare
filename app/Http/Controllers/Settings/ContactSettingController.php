<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Support\AdminNavigation;
use Inertia\Inertia;
use Inertia\Response;

class ContactSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function contact(): Response
    {
        $seo = SeoSetting::where('page', 'contact')->first();

        return Inertia::render('Admin/Settings/Form', [
            'routes' => AdminNavigation::routes(),
            'type' => 'seo',
            'title' => 'Contact SEO',
            'seo' => $seo?->only('page', 'meta_title', 'meta_description', 'meta_keywords') ?? ['page' => 'contact'],
            'seoSubmitUrl' => route('admin.seo-settings.update'),
        ]);
    }
}
