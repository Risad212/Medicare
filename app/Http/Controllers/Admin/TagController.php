<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $tags = Tag::latest()->get();

        return Inertia::render('Admin/Taxonomy/Index', [
            'kind' => 'tags',
            'records' => $tags->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.tags.index'),
                'create' => route('admin.tags.create'),
                'editBase' => url('/admin/blog/tags'),
                'deleteBase' => url('/admin/blog/tags'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Taxonomy/Form', [
            'kind' => 'tags',
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.tags.index'),
                'store' => route('admin.tags.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        Tag::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return back()->with('success', 'Tag added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag): Response
    {
        return Inertia::render('Admin/Taxonomy/Form', [
            'kind' => 'tags',
            'record' => ['id' => $tag->id, 'name' => $tag->name],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.tags.index'),
                'update' => route('admin.tags.update', $tag),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,'.$tag->id,
        ]);

        $tag->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return back()->with('success', 'Tag updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return back()->with('success', 'Tag deleted successfully!');
    }
}
