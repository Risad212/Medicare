<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageSection;

class PageController extends Controller
{
    public function home()
    {
        $section = PageSection::find(3);

        return view('frontend.index', compact('section'));
    }
}
