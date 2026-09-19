<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use App\Models\ServiceSetting;
use Illuminate\Http\Request;

class ServiceSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function service()
    {
        $serviceSetting = ServiceSetting::first();
        $seo = SeoSetting::where('page', 'service')->first();

        return view('backend.settings.service', compact(
            'serviceSetting',
            'seo'
        ));
    }

    /**
     * Service Setting Data Save In DB
     *
     * @return void
     */
    public function update(Request $request)
    {
        $rules = [
            'emergency_subtitle' => 'nullable|string|max:255',
            'emergency_title' => 'nullable|string|max:255',
            'emergency_description' => 'nullable|string|max:5000',
            'emergency_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'emergency_phone' => 'nullable|string|max:50',
            'emergency_email' => 'nullable|email|max:255',
            'prevention_subtitle' => 'nullable|string|max:255',
            'prevention_title' => 'nullable|string|max:255',
        ];

        for ($i = 1; $i <= 8; $i++) {
            $rules["prevention_{$i}_title"] = 'nullable|string|max:255';
            $rules["prevention_{$i}_desc"] = 'nullable|string|max:2000';
        }

        $validated = $request->validate($rules);

        $data = $validated;

        if ($request->hasFile('emergency_image')) {
            $data['emergency_image'] = $request->file('emergency_image')->store('service', 'public');
        }

        $setting = ServiceSetting::first();

        if ($setting) {
            $setting->update($data);
        } else {
            ServiceSetting::create($data);
        }

        return back()->with('success', 'Service settings updated successfully!');
    }
}
