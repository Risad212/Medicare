<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;

class ContactSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function contact()
    {
        $seo = SeoSetting::where('page', 'contact')->first();

       return view('backend.settings.contact', compact(
            'seo'
        ));
    }
}