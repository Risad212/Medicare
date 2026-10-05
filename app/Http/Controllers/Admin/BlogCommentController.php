<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use App\Support\AdminNavigation;
use Inertia\Inertia;
use Inertia\Response;

class BlogCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $comments = BlogComment::with('blog')->latest()->get();

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments->map(fn (BlogComment $comment) => [
                'id' => $comment->id,
                'blogTitle' => $comment->blog?->title ?? 'Deleted post',
                'name' => $comment->name,
                'email' => $comment->email,
                'comment' => $comment->comment,
                'status' => (int) $comment->status,
                'date' => $comment->created_at?->format('M d, Y'),
            ])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'approveBase' => url('/admin/comments'),
                'deleteBase' => url('/admin/comments'),
            ],
        ]);
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
