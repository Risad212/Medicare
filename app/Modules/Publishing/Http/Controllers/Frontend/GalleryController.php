<?php

namespace App\Modules\Publishing\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\SeoSetting;

class GalleryController extends Controller
{
    public function index()
    {
        $images = GalleryImage::active()->orderBy('sort_order')->orderBy('id')->get();

        $seo = SeoSetting::where('page', 'gallery')->first();

        return view('publishing.site-gallery', compact('images', 'seo'));
    }
}
