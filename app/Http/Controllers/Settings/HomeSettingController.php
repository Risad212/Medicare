<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\HomeSetting;
use App\Models\SeoSetting;
use Illuminate\Http\Request;

class HomeSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function home()
    {
        $homeSetting = HomeSetting::first();
        $seo = SeoSetting::where('page', 'home')->first();

        return view('backend.settings.home', compact(
            'homeSetting',
            'seo'
        ));
    }

    /**
     * Home Setting Data Save In DB
     *
     * @return void
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'about_title' => 'nullable|string|max:255',
            'about_description' => 'nullable|string|max:5000',
            'about_button_text' => 'nullable|string|max:100',
            'counter_one_text' => 'nullable|string|max:100',
            'counter_one_number' => 'nullable|integer|min:0',
            'counter_two_text' => 'nullable|string|max:100',
            'counter_two_number' => 'nullable|integer|min:0',
            'counter_three_text' => 'nullable|string|max:100',
            'counter_three_number' => 'nullable|integer|min:0',
            'counter_four_text' => 'nullable|string|max:100',
            'counter_four_number' => 'nullable|integer|min:0',
            'about_image_one' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'about_image_two' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'about_image_three' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $validated;

        if ($request->hasFile('about_image_one')) {
            $data['about_image_one'] = $request->file('about_image_one')->store('home', 'public');
        }

        if ($request->hasFile('about_image_two')) {
            $data['about_image_two'] = $request->file('about_image_two')->store('home', 'public');
        }

        if ($request->hasFile('about_image_three')) {
            $data['about_image_three'] = $request->file('about_image_three')->store('home', 'public');
        }

        $setting = HomeSetting::first();

        if ($setting) {
            $setting->update($data);
        } else {
            HomeSetting::create($data);
        }

        return back();
    }
}
