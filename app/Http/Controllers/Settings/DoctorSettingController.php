<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;

class DoctorSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function doctor()
    {
        $seo = SeoSetting::where('page', 'doctor')->first();

       return view('backend.settings.doctor', compact(
            'seo'
        ));
    }
}