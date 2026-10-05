<?php

namespace App\Modules\BloodBank\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Models\BloodDonation;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\BloodGroup;
use App\Modules\BloodBank\Services\BloodBankService;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BloodDonationController extends Controller
{
    public function __construct(
        private readonly BloodBankService $bankService,
    ) {}

    /**
     * Filterable, paginated list of blood donations.
     */
    public function index(Request $request): Response
    {
        $query = BloodDonation::with(['donor', 'bloodGroup']);

        if ($search = $request->query('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], trim($search));
            $query->where(function ($q) use ($escaped) {
                $q->where('bag_number', 'like', "%{$escaped}%")
                    ->orWhereHas('donor', fn ($d) => $d->where('name', 'like', "%{$escaped}%"));
            });
        }

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

        $donations->through(fn (BloodDonation $donation) => [
            'id' => $donation->id,
            'donorName' => $donation->donor?->name ?? 'Removed',
            'bloodGroup' => $donation->bloodGroup?->name ?? '—',
            'quantity' => $donation->quantity,
            'unit' => $donation->unit,
            'donationDate' => $donation->donation_date->format('Y-m-d'),
            'expiryDate' => $donation->expiry_date->format('Y-m-d'),
            'status' => $donation->status,
            'canSetAvailable' => in_array($donation->status, [BloodDonation::STATUS_COLLECTED, BloodDonation::STATUS_TESTING], true),
            'canDelete' => in_array($donation->status, [BloodDonation::STATUS_COLLECTED, BloodDonation::STATUS_TESTING, BloodDonation::STATUS_EXPIRED], true),
        ]);

        return Inertia::render('Admin/BloodBank/Donations/Index', [
            'donations' => [
                'data' => $donations->items(),
                'currentPage' => $donations->currentPage(),
                'lastPage' => $donations->lastPage(),
                'firstItem' => $donations->firstItem(),
                'lastItem' => $donations->lastItem(),
                'total' => $donations->total(),
                'previousPageUrl' => $donations->previousPageUrl(),
                'nextPageUrl' => $donations->nextPageUrl(),
            ],
            'bloodGroups' => BloodGroup::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'bloodGroupId' => (string) $request->query('blood_group_id', ''),
                'status' => (string) $request->query('status', ''),
                'from' => (string) $request->query('from', ''),
                'to' => (string) $request->query('to', ''),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-donations.index'),
                'create' => route('admin.blood-donations.create'),
                'showBase' => url('/admin/blood-donations'),
                'editBase' => url('/admin/blood-donations'),
                'statusBase' => url('/admin/blood-donations'),
                'deleteBase' => url('/admin/blood-donations'),
            ],
        ]);
    }

    /**
     * Show the form for recording a new donation.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/BloodBank/Donations/Form', [
            'mode' => 'create',
            'defaults' => [
                'donationDate' => now()->format('Y-m-d'),
                'expiryDate' => now()->addMonths(3)->format('Y-m-d'),
                'quantity' => 450,
            ],
            'donors' => BloodDonor::with('bloodGroup')->where('status', true)->orderBy('name')->get()
                ->map(fn (BloodDonor $donor) => ['id' => $donor->id, 'name' => $donor->name, 'bloodGroup' => $donor->bloodGroup?->name]),
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(['id', 'name']),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-donations.index'),
                'store' => route('admin.blood-donations.store'),
            ],
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
    public function show(BloodDonation $donation): Response
    {
        $donation->load(['donor', 'bloodGroup', 'creator', 'issues.request', 'issues.patient']);

        return Inertia::render('Admin/BloodBank/Donations/Show', [
            'donation' => [
                'id' => $donation->id,
                'donorName' => $donation->donor?->name ?? 'Removed donor',
                'bloodGroup' => $donation->bloodGroup?->name ?? '—',
                'quantity' => $donation->quantity,
                'unit' => $donation->unit,
                'bagNumber' => $donation->bag_number,
                'collectionLocation' => $donation->collection_location,
                'donationDate' => $donation->donation_date->format('Y-m-d'),
                'expiryDate' => $donation->expiry_date->format('Y-m-d'),
                'status' => $donation->status,
                'creatorName' => $donation->creator?->name,
                'issues' => $donation->issues->map(fn ($issue) => [
                    'id' => $issue->id,
                    'date' => $issue->issue_date->format('Y-m-d'),
                    'requestId' => $issue->request?->id,
                    'patientName' => $issue->patient?->name,
                    'quantity' => $issue->quantity,
                    'unit' => $issue->unit,
                ]),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-donations.index'),
                'edit' => route('admin.blood-donations.edit', $donation),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified donation.
     */
    public function edit(BloodDonation $donation): Response
    {
        return Inertia::render('Admin/BloodBank/Donations/Form', [
            'mode' => 'edit',
            'donation' => [
                'id' => $donation->id,
                'donorId' => $donation->donor_id,
                'bloodGroupId' => $donation->blood_group_id,
                'donationDate' => $donation->donation_date->format('Y-m-d'),
                'quantity' => $donation->quantity,
                'bagNumber' => $donation->bag_number,
                'expiryDate' => $donation->expiry_date->format('Y-m-d'),
                'collectionLocation' => $donation->collection_location,
                'notes' => $donation->notes,
                'bloodGroup' => $donation->bloodGroup?->name,
                'status' => $donation->status,
            ],
            'donors' => BloodDonor::with('bloodGroup')->where('status', true)->orderBy('name')->get()
                ->map(fn (BloodDonor $donor) => ['id' => $donor->id, 'name' => $donor->name, 'bloodGroup' => $donor->bloodGroup?->name]),
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(['id', 'name']),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-donations.index'),
                'update' => route('admin.blood-donations.update', $donation),
            ],
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
