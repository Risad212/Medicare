<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    /**
     * General Admin Form UI Show
     *
     * @return void
     */
    public function general()
    {
        $setting = GeneralSetting::first();

        return view('backend.settings.general', compact('setting'));
    }

    /**
     * General Data Save In DB
     *
     * @return void
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'working_hours' => 'nullable|string|max:255',
            'facebook' => 'nullable|url|max:500',
            'twitter' => 'nullable|url|max:500',
            'linkedin' => 'nullable|url|max:500',
            'youtube' => 'nullable|url|max:500',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'footer_description' => 'nullable|string|max:1000',
            'copyright' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,jpeg,png,ico,webp,svg|max:1024',
            'footer_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        $data = $validated;

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        if ($request->hasFile('footer_logo')) {
            $data['footer_logo'] = $request->file('footer_logo')->store('settings', 'public');
        }

        $setting = GeneralSetting::first();

        if ($setting) {
            $setting->update($data);
        } else {
            GeneralSetting::create($data);
        }

        return back();
    }
}
