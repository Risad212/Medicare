<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodGroup;
use Illuminate\Http\Request;

class BloodGroupController extends Controller
{
    /**
     * Display a listing of blood groups.
     */
    public function index()
    {
        $bloodGroups = BloodGroup::withCount(['donors', 'donations', 'requests'])
            ->orderBy('name')
            ->get();

        return view('backend.blood-groups.index', compact('bloodGroups'));
    }

    /**
     * Show the form for creating a new blood group.
     */
    public function create()
    {
        return view('backend.blood-groups.create');
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
    public function edit(BloodGroup $bloodGroup)
    {
        return view('backend.blood-groups.edit', compact('bloodGroup'));
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
