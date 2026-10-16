<?php

namespace App\Modules\Directory\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::latest()->get();

        return view('directory.admin-departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('directory.admin-departments.create');
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
    public function edit(Department $department)
    {
        return view('directory.admin-departments.edit', compact('department'));
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
        if (Doctor::where('department', $department->name)->exists()) {
            return back()->with('error', 'Department is assigned to doctors and cannot be deleted.');
        }

        $department->delete();

        return back()->with('success', 'Department deleted successfully!');
    }
}
