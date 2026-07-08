<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;

class ServiceSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function service()
    {
        $seo = SeoSetting::where('page', 'service')->first();

       return view('backend.settings.service', compact(
            'seo'
        ));
    }
}