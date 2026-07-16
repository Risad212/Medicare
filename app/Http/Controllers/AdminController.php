<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Blog;
use App\Models\BlogComment;

class AdminController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $totalDoctors        = Doctor::count();
        $totalAppointments   = Appointment::count();
        $totalBlogs          = Blog::count();
        $pendingComments     = BlogComment::where('status', 0)->count();

        return view('backend.home', compact(
            'totalDoctors',
            'totalAppointments',
            'totalBlogs',
            'pendingComments'
        ));
    }
}