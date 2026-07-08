<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;

class AboutSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function about()
    {
        $seo = SeoSetting::where('page', 'about')->first();

       return view('backend.settings.about', compact(
            'seo'
        ));
    }
}