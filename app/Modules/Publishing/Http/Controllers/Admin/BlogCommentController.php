<?php

namespace App\Modules\Publishing\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use App\Modules\Publishing\Http\Requests\UpdateBlogCommentRequest;

class BlogCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = BlogComment::with('blog')->latest()->get();

        return view('publishing.admin-comments.index', compact('comments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BlogComment $comment)
    {
        return view('publishing.admin-comments.edit', compact('comment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlogCommentRequest $request, BlogComment $comment)
    {
        $validated = $request->validated();

        if (isset($validated['name'])) {
            $comment->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'comment' => $validated['comment'],
                'status' => $request->boolean('status'),
            ]);

            return redirect()->route('admin.comments.index')->with('success', 'Comment updated!');
        }

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
