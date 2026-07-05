<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;

class BlogCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = BlogComment::with('blog')->latest()->get();
        return view('backend.comments.index', compact('comments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BlogComment $comment)
    {
        $comment->update(['status' => 1]);
        return back()->with('success', 'Comment approved!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlogComment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Comment deleted!');
    }
}