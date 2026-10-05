<?php

namespace App\Modules\BloodBank\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Models\BloodGroup;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BloodGroupController extends Controller
{
    /**
     * Display a listing of blood groups.
     */
    public function index(): Response
    {
        $bloodGroups = BloodGroup::withCount(['donors', 'donations', 'requests'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/BloodBank/Groups/Index', [
            'bloodGroups' => $bloodGroups->map(fn (BloodGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'status' => $group->status,
                'donorsCount' => $group->donors_count,
                'donationsCount' => $group->donations_count,
                'requestsCount' => $group->requests_count,
            ]),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-groups.index'),
                'create' => route('admin.blood-groups.create'),
                'editBase' => url('/admin/blood-groups'),
                'toggleBase' => url('/admin/blood-groups'),
                'deleteBase' => url('/admin/blood-groups'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new blood group.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/BloodBank/Groups/Form', [
            'mode' => 'create',
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-groups.index'),
                'store' => route('admin.blood-groups.store'),
            ],
        ]);
    }

    /**
     * Store a newly created blood group.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:10|unique:blood_groups,name',
            'status' => 'nullable',
        ]);

        BloodGroup::create([
            'name' => strtoupper($validated['name']),
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.blood-groups.index')->with('success', 'Blood group added successfully.');
    }

    /**
     * Show the form for editing the specified blood group.
     */
    public function edit(BloodGroup $bloodGroup): Response
    {
        return Inertia::render('Admin/BloodBank/Groups/Form', [
            'mode' => 'edit',
            'bloodGroup' => [
                'id' => $bloodGroup->id,
                'name' => $bloodGroup->name,
                'status' => $bloodGroup->status,
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-groups.index'),
                'update' => route('admin.blood-groups.update', $bloodGroup),
            ],
        ]);
    }

    /**
     * Update the specified blood group.
     */
    public function update(Request $request, BloodGroup $bloodGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:10|unique:blood_groups,name,'.$bloodGroup->id,
            'status' => 'nullable',
        ]);

        $bloodGroup->update([
            'name' => strtoupper($validated['name']),
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.blood-groups.index')->with('success', 'Blood group updated successfully.');
    }

    /**
     * Toggle a blood group active/inactive.
     */
    public function toggle(BloodGroup $bloodGroup)
    {
        $bloodGroup->update(['status' => ! $bloodGroup->status]);

        return back()->with('success', 'Blood group status updated.');
    }

    /**
     * Remove the specified blood group. Deletion is blocked whenever it is
     * referenced by donors, donations, requests or issues so history is
     * never silently orphaned.
     */
    public function destroy(BloodGroup $bloodGroup)
    {
        $inUse = $bloodGroup->donors()->exists()
            || $bloodGroup->donations()->exists()
            || $bloodGroup->requests()->exists()
            || $bloodGroup->issues()->exists();

        if ($inUse) {
            return back()->with('error', 'This blood group is in use and cannot be deleted. Disable it instead.');
        }

        $bloodGroup->delete();

        return redirect()->route('admin.blood-groups.index')->with('success', 'Blood group deleted successfully.');
    }
}
