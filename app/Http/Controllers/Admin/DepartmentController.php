<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $departments = Department::latest()->get();

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments->map(fn (Department $department) => [
                'id' => $department->id,
                'name' => $department->name,
                'description' => $department->description,
                'status' => (int) $department->status,
            ])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.departments.index'),
                'create' => route('admin.departments.create'),
                'editBase' => url('/admin/departments'),
                'deleteBase' => url('/admin/departments'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Departments/Create', [
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.departments.index'),
                'store' => route('admin.departments.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return back()->with('success', 'Department added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department): Response
    {
        return Inertia::render('Admin/Departments/Edit', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'description' => $department->description,
                'status' => (bool) $department->status,
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.departments.index'),
                'update' => route('admin.departments.update', $department),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,'.$department->id,
        ]);

        $department->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return back()->with('success', 'Department updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return back()->with('success', 'Department deleted successfully!');
    }
}
