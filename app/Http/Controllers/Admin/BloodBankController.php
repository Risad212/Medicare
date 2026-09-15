<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodDonation;
use App\Models\BloodDonor;
use App\Models\BloodInventorySetting;
use App\Models\BloodRequest;
use App\Services\BloodBankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BloodBankController extends Controller
{
    public function __construct(
        private readonly BloodBankService $bankService,
    ) {}

    /**
     * Blood-bank dashboard with summary cards and stock table.
     */
    public function dashboard()
    {
        $settings = BloodInventorySetting::setting();
        $inventory = $this->bankService->inventory(['low_stock' => $settings->low_stock_threshold]);

        $availableQty = collect($inventory)->sum(fn ($row) => $row['quantity']['available']);
        $statusCounts = $this->donationStatusCounts();

        return view('backend.bloodbank.dashboard', [
            'totalDonors' => BloodDonor::count(),
            'totalDonations' => BloodDonation::count(),
            'availableQty' => $availableQty,
            'pendingRequests' => BloodRequest::where('status', BloodRequest::STATUS_PENDING)->count(),
            'approvedRequests' => BloodRequest::whereIn('status', [BloodRequest::STATUS_APPROVED, BloodRequest::STATUS_PARTIALLY_APPROVED])->count(),
            'emergencyRequests' => BloodRequest::where('urgency', 'emergency')->whereNotIn('status', [BloodRequest::STATUS_FULFILLED, BloodRequest::STATUS_REJECTED, BloodRequest::STATUS_CANCELLED])->count(),
            'issuedUnits' => $statusCounts[BloodDonation::STATUS_ISSUED],
            'expiredUnits' => $statusCounts[BloodDonation::STATUS_EXPIRED],
            'inventory' => $inventory,
            'lowStock' => collect($inventory)->filter(fn ($row) => $row['status'] !== 'available')->values(),
            'recentDonations' => BloodDonation::with('donor', 'bloodGroup')->latest()->limit(5)->get(),
            'recentRequests' => BloodRequest::with('patient', 'bloodGroup', 'doctor')->latest()->limit(5)->get(),
            'emergencies' => BloodRequest::with('patient', 'bloodGroup')
                ->where('urgency', 'emergency')
                ->whereNotIn('status', [BloodRequest::STATUS_FULFILLED, BloodRequest::STATUS_REJECTED, BloodRequest::STATUS_CANCELLED])
                ->orderBy('required_date')
                ->limit(5)
                ->get(),
            'settings' => $settings,
        ]);
    }

    /**
     * Stock overview per group plus the available bag pool.
     */
    public function inventory(Request $request)
    {
        $settings = BloodInventorySetting::setting();
        $inventory = $this->bankService->inventory(['low_stock' => $settings->low_stock_threshold]);

        $bags = BloodDonation::with(['donor', 'bloodGroup'])
            ->whereIn('status', [BloodDonation::STATUS_AVAILABLE, BloodDonation::STATUS_RESERVED])
            ->orderBy('expiry_date')
            ->paginate(15)
            ->withQueryString();

        return view('backend.bloodbank.inventory', [
            'inventory' => $inventory,
            'settings' => $settings,
            'bags' => $bags,
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
