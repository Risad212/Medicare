<?php

namespace App\Modules\Directory\Http\Controllers\Settings;

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

        return view('directory.admin-settings-appointment', compact(
            'seo'
        ));
    }
}
