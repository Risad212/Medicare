<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;
use Illuminate\Http\Request;

class SeoSettingController extends Controller
{
    /**
     * Save / Update SEO Settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'page' => 'required|string|in:home,about,service,doctor,blog,contact,appointment',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        $seoSetting = SeoSetting::where('page', $validated['page'])->first();

        if ($seoSetting) {
            $seoSetting->update($validated);
        } else {
            SeoSetting::create($validated);
        }

        return back()->with('seo_success', 'SEO settings updated successfully.');
    }
}
