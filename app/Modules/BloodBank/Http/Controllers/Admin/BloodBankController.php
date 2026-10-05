<?php

namespace App\Modules\BloodBank\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Models\BloodDonation;
use App\Modules\BloodBank\Models\BloodDonor;
use App\Modules\BloodBank\Models\BloodInventorySetting;
use App\Modules\BloodBank\Models\BloodRequest;
use App\Modules\BloodBank\Services\BloodBankService;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BloodBankController extends Controller
{
    public function __construct(
        private readonly BloodBankService $bankService,
    ) {}

    /**
     * Blood-bank dashboard with summary cards and stock table.
     */
    public function dashboard(): Response
    {
        $settings = BloodInventorySetting::setting();
        $inventory = $this->bankService->inventory(['low_stock' => $settings->low_stock_threshold]);

        $availableQty = collect($inventory)->sum(fn ($row) => $row['quantity']['available']);
        $statusCounts = $this->donationStatusCounts();

        $emergencies = BloodRequest::with(['patient', 'bloodGroup'])
            ->where('urgency', 'emergency')
            ->whereNotIn('status', [BloodRequest::STATUS_FULFILLED, BloodRequest::STATUS_REJECTED, BloodRequest::STATUS_CANCELLED])
            ->orderBy('required_date')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/BloodBank/Dashboard', [
            'totalDonors' => BloodDonor::count(),
            'totalDonations' => BloodDonation::count(),
            'availableQty' => $availableQty,
            'pendingRequests' => BloodRequest::where('status', BloodRequest::STATUS_PENDING)->count(),
            'approvedRequests' => BloodRequest::whereIn('status', [BloodRequest::STATUS_APPROVED, BloodRequest::STATUS_PARTIALLY_APPROVED])->count(),
            'emergencyRequests' => BloodRequest::where('urgency', 'emergency')->whereNotIn('status', [BloodRequest::STATUS_FULFILLED, BloodRequest::STATUS_REJECTED, BloodRequest::STATUS_CANCELLED])->count(),
            'issuedUnits' => $statusCounts[BloodDonation::STATUS_ISSUED],
            'expiredUnits' => $statusCounts[BloodDonation::STATUS_EXPIRED],
            'inventory' => collect($inventory)->map(fn ($row) => [
                'group' => $row['blood_group']->name,
                'units' => $row['units'],
                'quantity' => $row['quantity'],
                'status' => $row['status'],
            ])->values(),
            'lowStock' => collect($inventory)->filter(fn ($row) => $row['status'] !== 'available')
                ->map(fn ($row) => ['group' => $row['blood_group']->name, 'units' => $row['units']['available'], 'status' => $row['status']])->values(),
            'recentDonations' => BloodDonation::with('donor', 'bloodGroup')->latest()->limit(5)->get()
                ->map(fn (BloodDonation $donation) => [
                    'id' => $donation->id,
                    'group' => $donation->bloodGroup->name,
                    'donor' => $donation->donor?->name ?? 'Removed',
                    'date' => $donation->donation_date->format('Y-m-d'),
                    'quantity' => $donation->quantity,
                    'status' => $donation->status,
                ]),
            'recentRequests' => BloodRequest::with(['patient', 'bloodGroup'])->latest()->limit(5)->get()
                ->map(fn (BloodRequest $request) => [
                    'id' => $request->id,
                    'patient' => $request->patient?->name ?? 'Unknown',
                    'group' => $request->bloodGroup->name,
                    'quantity' => $request->quantity,
                    'urgency' => $request->urgency,
                    'requiredDate' => $request->required_date->format('Y-m-d'),
                    'status' => $request->status,
                ]),
            'emergencies' => $emergencies->map(fn (BloodRequest $request) => [
                'id' => $request->id,
                'patient' => $request->patient?->name ?? 'Unknown',
                'group' => $request->bloodGroup->name,
                'quantity' => $request->quantity,
                'unit' => $request->unit,
                'requiredDate' => $request->required_date->format('Y-m-d'),
                'status' => $request->status,
            ]),
            'settings' => ['lowStockThreshold' => $settings->low_stock_threshold],
            'routes' => [
                ...AdminNavigation::routes(),
                'dashboard' => route('admin.bloodbank.dashboard'),
                'inventory' => route('admin.bloodbank.inventory'),
                'donors' => route('admin.blood-donors.index'),
                'donationCreate' => route('admin.blood-donations.create'),
                'requests' => route('admin.blood-requests.index'),
                'requestShowBase' => url('/admin/blood-requests'),
            ],
        ]);
    }

    /**
     * Stock overview per group plus the available bag pool.
     */
    public function inventory(Request $request): Response
    {
        $settings = BloodInventorySetting::setting();
        $inventory = $this->bankService->inventory(['low_stock' => $settings->low_stock_threshold]);

        $bags = BloodDonation::with(['donor', 'bloodGroup'])
            ->whereIn('status', [BloodDonation::STATUS_AVAILABLE, BloodDonation::STATUS_RESERVED])
            ->orderBy('expiry_date')
            ->paginate(15)
            ->withQueryString();

        $bags->through(fn (BloodDonation $bag) => [
            'id' => $bag->id,
            'bagNumber' => $bag->bag_number ?: '#'.$bag->id,
            'donor' => $bag->donor?->name ?? 'Removed',
            'group' => $bag->bloodGroup->name,
            'quantity' => $bag->quantity,
            'unit' => $bag->unit,
            'expiryDate' => $bag->expiry_date->format('Y-m-d'),
            'status' => $bag->status,
        ]);

        return Inertia::render('Admin/BloodBank/Inventory', [
            'inventory' => collect($inventory)->map(fn ($row) => [
                'group' => $row['blood_group']->name,
                'units' => $row['units'],
                'quantity' => $row['quantity'],
                'status' => $row['status'],
            ])->values(),
            'settings' => [
                'lowStockThreshold' => $settings->low_stock_threshold,
                'minDonationDays' => $settings->min_donation_days,
            ],
            'bags' => [
                'data' => $bags->items(),
                'currentPage' => $bags->currentPage(),
                'lastPage' => $bags->lastPage(),
                'firstItem' => $bags->firstItem(),
                'lastItem' => $bags->lastItem(),
                'total' => $bags->total(),
                'previousPageUrl' => $bags->previousPageUrl(),
                'nextPageUrl' => $bags->nextPageUrl(),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'dashboard' => route('admin.bloodbank.dashboard'),
                'inventory' => route('admin.bloodbank.inventory'),
                'settingsUpdate' => route('admin.bloodbank.settings.update'),
            ],
        ]);
    }

    /**
     * Update configurable inventory settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'low_stock_threshold' => 'required|integer|min:0|max:1000',
            'min_donation_days' => 'required|integer|min:1|max:3650',
        ]);

        $settings = BloodInventorySetting::setting();
        $settings->update($validated);

        return back()->with('success', 'Blood bank settings updated.');
    }

    /**
     * Sum donation quantities by status for the summary cards.
     *
     * @return array<string, int>
     */
    protected function donationStatusCounts(): array
    {
        $rows = BloodDonation::select('status', DB::raw('COALESCE(SUM(quantity),0) as qty'))
            ->groupBy('status')
            ->pluck('qty', 'status');

        return [
            BloodDonation::STATUS_ISSUED => (int) ($rows[BloodDonation::STATUS_ISSUED] ?? 0),
            BloodDonation::STATUS_EXPIRED => (int) ($rows[BloodDonation::STATUS_EXPIRED] ?? 0),
        ];
    }
}
