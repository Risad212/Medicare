<?php

namespace App\Modules\Publishing\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogComment;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    public function store(Request $request, $blog_id)
    {
        // Comments are only accepted on published posts.
        $blog = Blog::where('status', 1)->findOrFail($blog_id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'comment' => 'required|string|max:2000',
        ]);

        // Honeypot: humans leave the hidden "website" field empty. If it is
        // filled, a bot filled the form — accept it silently and store nothing
        // so the bot does not learn it was caught.
        if ($request->filled('website')) {
            return back()->with('success', 'Comment submitted — waiting for approval.');
        }

        BlogComment::create([
            'blog_id' => $blog->id,
            'name' => $request->name,
            'email' => $request->email,
            'comment' => $request->comment,
            'status' => 0,
        ]);

        return back()->with('success', 'Comment submitted — waiting for approval.');
    }
}
