<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'map_embed_url' => ['nullable', 'string', 'max:2000', 'url', function ($attr, $value, $fail) {
                $host = strtolower((string) parse_url($value, PHP_URL_HOST));

                if (! str_starts_with($value, 'https://')
                    || ! in_array($host, ['www.google.com', 'maps.google.com', 'google.com'], true)) {
                    $fail('Only https Google Maps embed URLs are allowed.');
                }
            }],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'footer_description' => 'nullable|string|max:1000',
            'copyright' => 'nullable|string|max:500',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'favicon' => 'nullable|file|mimes:jpg,jpeg,png,webp,ico|max:1024',
            'footer_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $validated;

        $setting = GeneralSetting::first();

        if ($request->hasFile('logo')) {
            if ($setting?->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($setting?->favicon) {
                Storage::disk('public')->delete($setting->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        }

        if ($request->hasFile('footer_logo')) {
            if ($setting?->footer_logo) {
                Storage::disk('public')->delete($setting->footer_logo);
            }
            $data['footer_logo'] = $request->file('footer_logo')->store('settings', 'public');
        }

        if ($setting) {
            $setting->update($data);
        } else {
            GeneralSetting::create($data);
        }

        return back();
    }
}
