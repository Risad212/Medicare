<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;

class AboutController extends Controller
{
     public function index()
      {
        $doctors     = Doctor::all();
        return view('frontend.about', compact('doctors'));
     }
}
