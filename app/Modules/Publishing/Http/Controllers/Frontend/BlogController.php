<?php

namespace App\Modules\Publishing\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\SeoSetting;
use App\Models\Tag;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::where('status', 1)->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('tag')) {
            // Exact tag (the column holds a single tag name, or possibly a
            // comma-separated list) — never a substring match, so "health"
            // no longer pulls in "healthy".
            $tag = trim((string) $request->tag);
            $query->where(function ($q) use ($tag) {
                $q->where('tags', $tag)
                    ->orWhere('tags', 'like', $tag.',%')
                    ->orWhere('tags', 'like', '%,'.$tag)
                    ->orWhere('tags', 'like', '%,'.$tag.',%');
            });
        }

        $blogs = $query->paginate(6)->withQueryString();
        $categories = Blog::where('status', 1)
            ->whereNotNull('category')
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->get();
        $tags = Tag::latest()->get();

        $recentPosts = Blog::where('status', 1)->latest()->take(5)->get();

        $seo = SeoSetting::where('page', 'blog')->first();

        return view('publishing.site-blog', compact('blogs', 'categories', 'tags', 'recentPosts', 'seo'));
    }

    public function show($slug)
    {
        $blog = Blog::where('slug', $slug)->where('status', 1)->firstOrFail();
        $categories = Blog::where('status', 1)
            ->whereNotNull('category')
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->get();
        $tags = Tag::latest()->get();
        $recentPosts = Blog::where('status', 1)->latest()->take(5)->get();
        $comments = $blog->comments()->latest()->get();

        return view('publishing.site-blogs-show', compact('blog', 'categories', 'tags', 'recentPosts', 'comments'));
    }
}
