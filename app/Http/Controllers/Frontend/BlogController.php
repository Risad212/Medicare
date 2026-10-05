<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\SeoSetting;
use App\Models\Tag;
use App\Support\BlogContentSanitizer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Blog::where('status', 1)->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('tag')) {
            $query->where('tags', 'like', '%'.$request->tag.'%');
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

        $blogs->setCollection($blogs->getCollection()->map(fn (Blog $blog) => [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'image' => $blog->image,
            'author' => $blog->author,
            'category' => $blog->category,
            'date' => $blog->created_at?->format('M d, Y'),
        ]));

        return Inertia::render('Public/Blog/Index', [
            'blogs' => [
                'data' => $blogs->items(),
                'currentPage' => $blogs->currentPage(),
                'lastPage' => $blogs->lastPage(),
                'total' => $blogs->total(),
                'links' => $blogs->linkCollection(),
            ],
            'categories' => $categories->map(fn ($category) => [
                'name' => $category->category,
                'count' => $category->count,
            ])->values(),
            'tags' => $tags->map(fn (Tag $tag) => [
                'name' => $tag->name,
            ])->values(),
            'filters' => [
                'category' => (string) $request->query('category', ''),
                'tag' => (string) $request->query('tag', ''),
            ],
            'seo' => [
                'title' => $seo?->meta_title,
                'description' => $seo?->meta_description,
                'keywords' => $seo?->meta_keywords,
            ],
        ]);
    }

    public function show($slug, BlogContentSanitizer $contentSanitizer): Response
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

        return Inertia::render('Public/Blog/Show', [
            'blog' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'excerpt' => $blog->excerpt,
                'content' => $contentSanitizer->sanitize($blog->content),
                'image' => $blog->image,
                'author' => $blog->author,
                'category' => $blog->category,
                'date' => $blog->created_at?->format('d M, Y'),
            ],
            'categories' => $categories->map(fn ($category) => [
                'name' => $category->category,
                'count' => $category->count,
            ])->values(),
            'tags' => $tags->map(fn (Tag $tag) => [
                'name' => $tag->name,
            ])->values(),
            'recentPosts' => $recentPosts->map(fn (Blog $post) => [
                'title' => $post->title,
                'slug' => $post->slug,
                'image' => $post->image,
                'date' => $post->created_at?->format('F d, Y'),
            ])->values(),
            'comments' => $comments->map(fn ($comment) => [
                'name' => $comment->name,
                'comment' => $comment->comment,
                'date' => $comment->created_at?->format('F d, Y'),
            ])->values(),
            'seo' => [
                'title' => $blog->title,
                'description' => $blog->excerpt,
                'keywords' => '',
            ],
        ]);
    }
}
