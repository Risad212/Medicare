<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodDonor;
use App\Models\BloodGroup;
use App\Models\BloodInventorySetting;
use Illuminate\Http\Request;

class BloodDonorController extends Controller
{
    /**
     * Display a filterable, paginated donor list.
     */
    public function index(Request $request)
    {
        $query = BloodDonor::with('bloodGroup');

        if ($search = $request->query('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], trim($search));
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('phone', 'like', "%{$escaped}%")
                    ->orWhere('email', 'like', "%{$escaped}%");
            });
        }

        if ($request->filled('blood_group_id')) {
            $query->where('blood_group_id', $request->query('blood_group_id'));
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->query('gender'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $donors = $query->latest()->paginate(10)->withQueryString();

        return view('backend.blood-donors.index', [
            'donors' => $donors,
            'bloodGroups' => BloodGroup::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new donor.
     */
    public function create()
    {
        return view('backend.blood-donors.create', [
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created donor.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blood_group_id' => 'required|exists:blood_groups,id',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:1000',
            'last_donation_date' => 'nullable|date|before_or_equal:today',
            'status' => 'nullable',
            'notes' => 'nullable|string|max:2000',
        ]);

        BloodDonor::create([
            'name' => $validated['name'],
            'blood_group_id' => $validated['blood_group_id'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'last_donation_date' => $validated['last_donation_date'] ?? null,
            'status' => $request->has('status') ? 1 : 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.blood-donors.index')->with('success', 'Donor registered successfully.');
    }

    /**
     * Display a donor profile with donation history and eligibility.
     */
    public function show(BloodDonor $donor)
    {
        $donor->load('bloodGroup', 'donations.bloodGroup');

        return view('backend.blood-donors.show', [
            'donor' => $donor,
            'minDonationDays' => BloodInventorySetting::setting()->minDonationDays(),
        ]);
    }

    /**
     * Show the form for editing the specified donor.
     */
    public function edit(BloodDonor $donor)
    {
        return view('backend.blood-donors.edit', [
            'donor' => $donor,
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified donor.
     */
    public function update(Request $request, BloodDonor $donor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'blood_group_id' => 'required|exists:blood_groups,id',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:1000',
            'last_donation_date' => 'nullable|date|before_or_equal:today',
            'status' => 'nullable',
            'notes' => 'nullable|string|max:2000',
        ]);

        $donor->update([
            'name' => $validated['name'],
            'blood_group_id' => $validated['blood_group_id'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'last_donation_date' => $validated['last_donation_date'] ?? null,
            'status' => $request->has('status') ? 1 : 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.blood-donors.index')->with('success', 'Donor updated successfully.');
    }

    /**
     * Remove the specified donor. Cleanup is safe: cancelling the donor
     * cascades its donations, their issues stay linked to the request.
     */
    public function destroy(BloodDonor $donor)
    {
        $donor->delete();

        return redirect()->route('admin.blood-donors.index')->with('success', 'Donor removed successfully.');
    }
}
