<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminPageController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Frontend Page Admin CMS For Home
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function about()
    {
        return view('backend.pages.home.about');
    }
}