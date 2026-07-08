<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SeoSetting;

class BlogSettingController extends Controller
{
    /**
     * Setting Form UI Show
     *
     * @return void
     */
    public function blog()
    {
       $seo = SeoSetting::where('page', 'blog')->first();

       return view('backend.settings.blog', compact(
            'seo'
        ));
    }
}