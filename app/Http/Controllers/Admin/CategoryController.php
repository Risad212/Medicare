<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $categories = Category::latest()->get();

        return Inertia::render('Admin/Taxonomy/Index', [
            'kind' => 'categories',
            'records' => $categories->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.categories.index'),
                'create' => route('admin.categories.create'),
                'editBase' => url('/admin/blog/categories'),
                'deleteBase' => url('/admin/blog/categories'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Taxonomy/Form', [
            'kind' => 'categories',
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.categories.index'),
                'store' => route('admin.categories.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return back()->with('success', 'Category added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): Response
    {
        return Inertia::render('Admin/Taxonomy/Form', [
            'kind' => 'categories',
            'record' => ['id' => $category->id, 'name' => $category->name],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.categories.index'),
                'update' => route('admin.categories.update', $category),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return back()->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Category deleted successfully!');
    }
}
