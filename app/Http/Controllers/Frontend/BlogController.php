<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Tag;
use App\Models\SeoSetting;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Blog::where('status', 1)->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('tag')) {
            $query->where('tags', 'like', '%' . $request->tag . '%');
        }

        $blogs       = $query->paginate(9);
        $categories  = Blog::where('status', 1)
                            ->whereNotNull('category')
                            ->selectRaw('category, count(*) as count')
                            ->groupBy('category')
                            ->get();
        $tags        = Tag::latest()->get();

        $recentPosts = Blog::where('status', 1)->latest()->take(5)->get();

        $seo     = SeoSetting::where('page', 'blog')->first();

        return view('frontend.blog', compact('blogs', 'categories', 'tags', 'recentPosts', 'seo'));
    }

    public function show($slug)
    {
        $blog        = Blog::where('slug', $slug)->where('status', 1)->firstOrFail();
        $categories  = Blog::where('status', 1)
                            ->whereNotNull('category')
                            ->selectRaw('category, count(*) as count')
                            ->groupBy('category')
                            ->get();
        $tags        = Tag::latest()->get();
        $recentPosts = Blog::where('status', 1)->latest()->take(5)->get();
        $comments    = $blog->comments()->latest()->get();

        return view('frontend.blogs.show', compact('blog', 'categories', 'tags', 'recentPosts', 'comments'));
    }
}