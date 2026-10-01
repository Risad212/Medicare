<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Tag;
use App\Services\BlogSanitizer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $blogs = Blog::latest()->paginate(20);
        $categories = Category::latest()->get();
        $tags = Tag::latest()->get();

        return view('backend.blogs.index', compact('blogs', 'categories', 'tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::latest()->get();
        $tags = Tag::latest()->get();

        return view('backend.blogs.create', compact('categories', 'tags'));
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
    public function edit(Blog $blog)
    {
        $categories = Category::latest()->get();
        $tags = Tag::latest()->get();

        return view('backend.blogs.edit', compact('blog', 'categories', 'tags'));
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
