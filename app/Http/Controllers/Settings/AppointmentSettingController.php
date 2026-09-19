<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;

class AppointmentSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function appointment()
    {
        $seo = SeoSetting::where('page', 'appointment')->first();

        return view('backend.settings.appointment', compact(
            'seo'
        ));
    }
}
