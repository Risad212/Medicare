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
        $request->validate([
            'page' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        $data = $request->except('_token');

        $seoSetting = SeoSetting::where('page', $request->page)->first();

        if ($seoSetting) {
            $seoSetting->update($data);
        } else {
            SeoSetting::create($data);
        }

        return back()->with('seo_success', 'SEO settings updated successfully.');
    }
}