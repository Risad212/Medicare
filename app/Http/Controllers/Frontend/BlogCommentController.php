<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    public function store(Request $request, $blog_id)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'comment' => 'required|string',
        ]);

        BlogComment::create([
            'blog_id' => $blog_id,
            'name'    => $request->name,
            'email'   => $request->email,
            'comment' => $request->comment,
            'status'  => 0,
        ]);

        return back()->with('success', 'Comment submitted — waiting for approval.');
    }
}