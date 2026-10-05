<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Tag;
use App\Services\BlogSanitizer;
use App\Support\AdminNavigation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {

        $blogs = Blog::latest()->paginate(20)->withQueryString();
        $categories = Category::latest()->get();
        $tags = Tag::latest()->get();

        $blogs->through(fn (Blog $blog) => [
            'id' => $blog->id,
            'title' => $blog->title,
            'excerpt' => $blog->excerpt,
            'image' => $blog->image,
            'author' => $blog->author,
            'category' => $blog->category,
            'tags' => $blog->tags,
            'status' => (int) $blog->status,
            'date' => $blog->created_at?->format('M d, Y'),
        ]);

        return Inertia::render('Admin/Blogs/Index', [
            'blogs' => [
                'data' => $blogs->items(),
                'currentPage' => $blogs->currentPage(),
                'lastPage' => $blogs->lastPage(),
                'firstItem' => $blogs->firstItem(),
                'lastItem' => $blogs->lastItem(),
                'total' => $blogs->total(),
                'previousPageUrl' => $blogs->previousPageUrl(),
                'nextPageUrl' => $blogs->nextPageUrl(),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blogs.index'),
                'create' => route('admin.blogs.create'),
                'editBase' => url('/admin/blogs'),
                'deleteBase' => url('/admin/blogs'),
            ],
            'storageUrl' => asset('storage'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $categories = Category::latest()->get();
        $tags = Tag::latest()->get();

        return Inertia::render('Admin/Blogs/Create', [
            'categories' => $categories->map(fn (Category $category) => ['name' => $category->name])->values(),
            'tags' => $tags->map(fn (Tag $tag) => ['name' => $tag->name])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blogs.index'),
                'store' => route('admin.blogs.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlogRequest $request)
    {
        $validated = $request->validated();

        $data = array_intersect_key($validated, array_flip(['title', 'excerpt', 'content', 'order', 'category', 'tags']));
        // Strip dangerous tags/attributes while allowing basic formatting - XSS prevention
        if (isset($data['content'])) {
            $data['content'] = BlogSanitizer::sanitize($data['content']);
        }
        if (isset($data['excerpt'])) {
            $data['excerpt'] = strip_tags($data['excerpt']);
        }
        $data['slug'] = Str::slug($request->title).'-'.uniqid();
        $data['status'] = $request->has('status') ? 1 : 0;
        $data['author'] = auth()->user()->name;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('blogs', 'public');
        }

        Blog::create($data);

        return back()->with('success', 'Blog post added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog): Response
    {
        $categories = Category::latest()->get();
        $tags = Tag::latest()->get();

        return Inertia::render('Admin/Blogs/Edit', [
            'blog' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'excerpt' => $blog->excerpt,
                'content' => $blog->content,
                'image' => $blog->image,
                'author' => $blog->author,
                'category' => $blog->category,
                'tags' => $blog->tags,
                'status' => (bool) $blog->status,
                'date' => $blog->created_at?->format('M d, Y'),
            ],
            'categories' => $categories->map(fn (Category $category) => ['name' => $category->name])->values(),
            'tags' => $tags->map(fn (Tag $tag) => ['name' => $tag->name])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blogs.index'),
                'update' => route('admin.blogs.update', $blog),
            ],
            'storageUrl' => asset('storage'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BlogRequest $request, Blog $blog)
    {
        $validated = $request->validated();

        $data = array_intersect_key($validated, array_flip(['title', 'excerpt', 'content', 'order', 'category', 'tags']));
        if (isset($data['content'])) {
            $data['content'] = BlogSanitizer::sanitize($data['content']);
        }
        if (isset($data['excerpt'])) {
            $data['excerpt'] = strip_tags($data['excerpt']);
        }
        $data['status'] = $request->has('status') ? 1 : 0;

        if ($request->hasFile('image')) {
            if ($blog->image) {
                Storage::disk('public')->delete($blog->image);
            }
            $data['image'] = $request->file('image')->store('blogs', 'public');
        }

        $blog->update($data);

        return back()->with('success', 'Blog post updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        if ($blog->image) {
            Storage::disk('public')->delete($blog->image);
        }
        $blog->delete();

        return back()->with('success', 'Blog post deleted successfully!');
    }
}
