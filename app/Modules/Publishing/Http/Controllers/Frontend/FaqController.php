<?php

namespace App\Modules\Publishing\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\SeoSetting;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::active()->orderBy('sort_order')->orderBy('id')->get();

        $seo = SeoSetting::where('page', 'faq')->first();

        return view('publishing.site-faq', compact('faqs', 'seo'));
    }
}
