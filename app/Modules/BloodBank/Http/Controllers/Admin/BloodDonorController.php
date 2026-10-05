<?php

namespace App\Modules\BloodBank\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\BloodGroup;
use App\Modules\BloodBank\Models\BloodInventorySetting;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BloodDonorController extends Controller
{
    /**
     * Display a filterable, paginated donor list.
     */
    public function index(Request $request): Response
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

        $donors->through(fn (BloodDonor $donor) => [
            'id' => $donor->id,
            'name' => $donor->name,
            'email' => $donor->email,
            'phone' => $donor->phone,
            'bloodGroup' => $donor->bloodGroup?->name,
            'lastDonationDate' => $donor->last_donation_date?->format('Y-m-d'),
            'status' => $donor->status,
        ]);

        return Inertia::render('Admin/BloodBank/Donors/Index', [
            'donors' => [
                'data' => $donors->items(),
                'currentPage' => $donors->currentPage(),
                'lastPage' => $donors->lastPage(),
                'firstItem' => $donors->firstItem(),
                'lastItem' => $donors->lastItem(),
                'total' => $donors->total(),
                'previousPageUrl' => $donors->previousPageUrl(),
                'nextPageUrl' => $donors->nextPageUrl(),
            ],
            'bloodGroups' => BloodGroup::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'bloodGroupId' => (string) $request->query('blood_group_id', ''),
                'gender' => (string) $request->query('gender', ''),
                'status' => (string) $request->query('status', ''),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-donors.index'),
                'create' => route('admin.blood-donors.create'),
                'showBase' => url('/admin/blood-donors'),
                'editBase' => url('/admin/blood-donors'),
                'deleteBase' => url('/admin/blood-donors'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new donor.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/BloodBank/Donors/Form', [
            'mode' => 'create',
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(['id', 'name']),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-donors.index'),
                'store' => route('admin.blood-donors.store'),
            ],
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
    public function show(BloodDonor $donor): Response
    {
        $donor->load('bloodGroup', 'donations.bloodGroup');

        return Inertia::render('Admin/BloodBank/Donors/Show', [
            'donor' => [
                'id' => $donor->id,
                'name' => $donor->name,
                'phone' => $donor->phone,
                'email' => $donor->email,
                'gender' => $donor->gender,
                'dateOfBirth' => $donor->date_of_birth?->format('Y-m-d'),
                'address' => $donor->address,
                'lastDonationDate' => $donor->last_donation_date?->format('Y-m-d'),
                'notes' => $donor->notes,
                'status' => $donor->status,
                'bloodGroup' => $donor->bloodGroup?->name,
                'totalDonations' => $donor->totalDonations(),
                'eligible' => $donor->isEligible(BloodInventorySetting::setting()->minDonationDays()),
                'donations' => $donor->donations->map(fn ($donation) => [
                    'id' => $donation->id,
                    'date' => $donation->donation_date->format('Y-m-d'),
                    'bagNumber' => $donation->bag_number,
                    'quantity' => $donation->quantity,
                    'unit' => $donation->unit,
                    'expiryDate' => $donation->expiry_date->format('Y-m-d'),
                    'status' => $donation->status,
                ]),
            ],
            'minDonationDays' => BloodInventorySetting::setting()->minDonationDays(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-donors.index'),
                'edit' => route('admin.blood-donors.edit', $donor),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified donor.
     */
    public function edit(BloodDonor $donor): Response
    {
        return Inertia::render('Admin/BloodBank/Donors/Form', [
            'mode' => 'edit',
            'donor' => [
                'id' => $donor->id,
                'name' => $donor->name,
                'bloodGroupId' => $donor->blood_group_id,
                'phone' => $donor->phone,
                'email' => $donor->email,
                'dateOfBirth' => $donor->date_of_birth?->format('Y-m-d'),
                'gender' => $donor->gender,
                'address' => $donor->address,
                'lastDonationDate' => $donor->last_donation_date?->format('Y-m-d'),
                'status' => $donor->status,
                'notes' => $donor->notes,
            ],
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(['id', 'name']),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-donors.index'),
                'show' => route('admin.blood-donors.show', $donor),
                'update' => route('admin.blood-donors.update', $donor),
            ],
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
