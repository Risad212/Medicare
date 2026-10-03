<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\AboutSetting;
use App\Models\SeoSetting;
use Illuminate\Http\Request;

class AboutSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function about()
    {
        $about = AboutSetting::first();
        $seo = SeoSetting::where('page', 'about')->first();

        return view('backend.settings.about', compact(
            'about',
            'seo'
        ));
    }

    /**
     * About Setting Data Save In DB
     *
     * @return void
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'subtitle' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:500',
            'mission_title' => 'nullable|string|max:255',
            'mission_description' => 'nullable|string|max:3000',
            'planning_title' => 'nullable|string|max:255',
            'planning_description' => 'nullable|string|max:3000',
            'vision_title' => 'nullable|string|max:255',
            'vision_description' => 'nullable|string|max:3000',
            'image_one' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_two' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $validated;

        if ($request->hasFile('image_one')) {
            $data['image_one'] = $request->file('image_one')->store('about', 'public');
        }

        if ($request->hasFile('image_two')) {
            $data['image_two'] = $request->file('image_two')->store('about', 'public');
        }

        $about = AboutSetting::first();

        if ($about) {
            $about->update($data);
        } else {
            AboutSetting::create($data);
        }

        return back()->with('success', 'About settings updated successfully.');
    }
}
