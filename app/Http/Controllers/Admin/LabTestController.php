<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabTest;
use Illuminate\Http\Request;

class LabTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ? str_replace(['%', '_'], ['\%', '\_'], $request->search) : null;
        $labTests = LabTest::when($search, function ($query) use ($search) {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('category', 'like', '%'.$search.'%');
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.lab-tests.index', compact('labTests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.lab-tests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'normal_range' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:100',
            'status' => 'nullable|in:0,1',
        ]);

        $validated['status'] = $request->has('status') ? (int) $request->status : 1;

        LabTest::create($validated);

        return redirect()->route('admin.lab-tests.index')
            ->with('success', 'Laboratory test added successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $labTest = LabTest::findOrFail($id);

        return view('backend.lab-tests.edit', compact('labTest'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $labTest = LabTest::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'normal_range' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:100',
            'status' => 'nullable|in:0,1',
        ]);

        $validated['status'] = $request->has('status') ? (int) $request->status : 1;

        $labTest->update($validated);

        return redirect()->route('admin.lab-tests.index')
            ->with('success', 'Laboratory test updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $labTest = LabTest::findOrFail($id);
        $labTest->delete();

        return redirect()->route('admin.lab-tests.index')
            ->with('success', 'Laboratory test deleted successfully!');
    }
}
