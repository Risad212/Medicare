<?php

namespace App\Modules\Lab\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Lab\Models\LabTest;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class LabTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->search ? str_replace(['%', '_'], ['\%', '\_'], $request->search) : null;
        $labTests = LabTest::when($search, fn ($query) => $query->where(fn ($filter) => $filter
            ->where('name', 'like', '%'.$search.'%')
            ->orWhere('category', 'like', '%'.$search.'%')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $labTests->through(fn (LabTest $test) => [
            'id' => $test->id,
            'name' => $test->name,
            'description' => $test->description ? Str::limit(strip_tags($test->description), 60) : null,
            'category' => $test->category,
            'price' => number_format((float) $test->price, 2),
            'normalRange' => $test->normal_range,
            'unit' => $test->unit,
            'status' => (bool) $test->status,
        ]);

        return Inertia::render('Admin/Lab/Tests/Index', [
            'labTests' => [
                'data' => $labTests->items(),
                'currentPage' => $labTests->currentPage(),
                'lastPage' => $labTests->lastPage(),
                'firstItem' => $labTests->firstItem(),
                'lastItem' => $labTests->lastItem(),
                'total' => $labTests->total(),
                'previousPageUrl' => $labTests->previousPageUrl(),
                'nextPageUrl' => $labTests->nextPageUrl(),
            ],
            'filters' => ['search' => (string) $request->query('search', '')],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.lab-tests.index'),
                'create' => route('admin.lab-tests.create'),
                'editBase' => url('/admin/lab-tests'),
                'deleteBase' => url('/admin/lab-tests'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Lab/Tests/Create', [
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.lab-tests.index'),
                'store' => route('admin.lab-tests.store'),
            ],
        ]);
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
    public function edit(string $id): Response
    {
        $labTest = LabTest::findOrFail($id);

        return Inertia::render('Admin/Lab/Tests/Edit', [
            'labTest' => [
                'id' => $labTest->id,
                'name' => $labTest->name,
                'category' => $labTest->category,
                'description' => $labTest->description,
                'price' => $labTest->price,
                'normalRange' => $labTest->normal_range,
                'unit' => $labTest->unit,
                'status' => (bool) $labTest->status,
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.lab-tests.index'),
                'update' => route('admin.lab-tests.update', $labTest),
            ],
        ]);
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
