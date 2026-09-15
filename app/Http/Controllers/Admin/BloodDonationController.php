<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodDonation;
use App\Models\BloodDonor;
use App\Models\BloodGroup;
use App\Services\BloodBankService;
use Illuminate\Http\Request;

class BloodDonationController extends Controller
{
    public function __construct(
        private readonly BloodBankService $bankService,
    ) {}

    /**
     * Filterable, paginated list of blood donations.
     */
    public function index(Request $request)
    {
        $query = BloodDonation::with(['donor', 'bloodGroup']);

        if ($request->filled('blood_group_id')) {
            $query->where('blood_group_id', $request->query('blood_group_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('donation_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('donation_date', '<=', $request->query('to'));
        }

        $donations = $query->latest('donation_date')->paginate(10)->withQueryString();

        return view('backend.blood-donations.index', [
            'donations' => $donations,
            'bloodGroups' => BloodGroup::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for recording a new donation.
     */
    public function create()
    {
        return view('backend.blood-donations.create', [
            'donors' => BloodDonor::where('status', true)->orderBy('name')->get(),
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly recorded donation. Starts at 'collected'.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'donor_id' => 'required|exists:blood_donors,id',
            'blood_group_id' => 'required|exists:blood_groups,id',
            'donation_date' => 'required|date|before_or_equal:today',
            'quantity' => 'required|integer|min:1|max:2000',
            'bag_number' => 'nullable|string|max:50',
            'collection_location' => 'nullable|string|max:255',
            'expiry_date' => 'required|date|after:donation_date',
            'notes' => 'nullable|string|max:2000',
        ]);

        BloodDonation::create([
            ...$validated,
            'unit' => 'ml',
            'status' => BloodDonation::STATUS_COLLECTED,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.blood-donations.index')->with('success', 'Donation recorded. Mark it available once testing passes.');
    }

    /**
     * Display a single donation with links to issue history.
     */
    public function show(BloodDonation $donation)
    {
        $donation->load(['donor', 'bloodGroup', 'creator', 'issues.request', 'issues.patient']);

        return view('backend.blood-donations.show', compact('donation'));
    }

    /**
     * Show the form for editing the specified donation.
     */
    public function edit(BloodDonation $donation)
    {
        return view('backend.blood-donations.edit', [
            'donation' => $donation,
            'donors' => BloodDonor::where('status', true)->orderBy('name')->get(),
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified donation.
     */
    public function update(Request $request, BloodDonation $donation)
    {
        $validated = $request->validate([
            'donor_id' => 'required|exists:blood_donors,id',
            'blood_group_id' => 'required|exists:blood_groups,id',
            'donation_date' => 'required|date|before_or_equal:today',
            'quantity' => 'required|integer|min:1|max:2000',
            'bag_number' => 'nullable|string|max:50',
            'collection_location' => 'nullable|string|max:255',
            'expiry_date' => 'required|date|after:donation_date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $donation->update($validated);

        return redirect()->route('admin.blood-donations.index')->with('success', 'Donation updated successfully.');
    }

    /**
     * Transition a donation status.
     * Only collected/testing donations may be made available.
     */
    public function updateStatus(Request $request, BloodDonation $donation)
    {
        $validated = $request->validate([
            'status' => 'required|in:testing,available,rejected',
        ]);

        try {
            if ($validated['status'] === BloodDonation::STATUS_AVAILABLE) {
                $this->bankService->makeAvailable($donation);
            } else {
                if (! in_array($donation->status, [BloodDonation::STATUS_COLLECTED, BloodDonation::STATUS_TESTING], true)) {
                    return back()->with('error', 'Cannot change status from the current state.');
                }
                $donation->update(['status' => $validated['status']]);
            }

            return back()->with('success', 'Donation status updated.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified donation. Only collected/testing donations
     * may be deleted; approved/issued ones must stay for the audit trail.
     */
    public function destroy(BloodDonation $donation)
    {
        if (! in_array($donation->status, [BloodDonation::STATUS_COLLECTED, BloodDonation::STATUS_TESTING, BloodDonation::STATUS_EXPIRED], true)) {
            return back()->with('error', 'Cannot delete a donation that is reserved or issued.');
        }

        $donation->delete();

        return redirect()->route('admin.blood-donations.index')->with('success', 'Donation removed.');
    }
}
