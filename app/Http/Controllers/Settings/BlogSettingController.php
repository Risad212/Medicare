<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Support\AdminNavigation;
use Inertia\Inertia;
use Inertia\Response;

class BlogSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function blog(): Response
    {
        $seo = SeoSetting::where('page', 'blog')->first();

        return Inertia::render('Admin/Settings/Form', [
            'routes' => AdminNavigation::routes(),
            'type' => 'seo',
            'title' => 'Blog SEO',
            'seo' => $seo?->only('page', 'meta_title', 'meta_description', 'meta_keywords') ?? ['page' => 'blog'],
            'seoSubmitUrl' => route('admin.seo-settings.update'),
        ]);
    }
}
