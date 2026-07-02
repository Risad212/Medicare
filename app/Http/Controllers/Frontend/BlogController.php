<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
      {
        $blogs = Blog::all();
        return view('frontend.blog', compact('blogs'));
     }

    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();

        return view('frontend.blogs.show', compact('blog'));
    }
}
