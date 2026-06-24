<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminPageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Frontend Page CMS For Home
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('backend.pages.index');
    }

    /**
     * Frontend Page CMS For About
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function about()
    {
        return view('backend.pages.about');
    }

    /**
     * Frontend Page CMS For Service
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function service()
    {
        return view('backend.pages.service');
    }

    /**
     * Frontend Page CMS For Doctor
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function doctor()
    {
        return view('backend.pages.doctor');
    }

    /**
     * Frontend Page CMS For Doctor
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function contact()
    {
        return view('backend.pages.contact');
    }

    /**
     * Frontend Page CMS For blog
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function blog()
    {
        return view('backend.pages.blog');
    }
}