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
     * Home page about section cms ui show
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function about()
    {
        return view('backend.pages.home.about');
    }
}