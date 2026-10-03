<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodDonation;
use App\Models\BloodGroup;
use App\Models\BloodIssue;
use App\Models\BloodRequest;
use App\Services\BloodBankService;
use App\Services\CsvExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BloodReportController extends Controller
{
    public function __construct(
        protected BloodBankService $bloodBankService,
        protected CsvExport $csvExport,
    ) {}

    /**
     * Reports overview with period filters.
     */
    public function index(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();

        $donationStats = BloodDonation::whereBetween('created_at', [$from, $to])
            ->selectRaw('blood_group_id, status, SUM(quantity) as total_quantity')
            ->groupBy('blood_group_id', 'status')
            ->get();

        // Quantity per group (issued) in the period
        $issuedByGroup = DB::table('blood_issues')
            ->join('blood_groups', 'blood_groups.id', '=', 'blood_issues.blood_group_id')
            ->whereBetween('blood_issues.issue_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('blood_groups.name, SUM(blood_issues.quantity) as total_quantity')
            ->groupBy('blood_groups.name')
            ->pluck('total_quantity', 'name');

        // Donation counts + issued counts per month (last 6 months)
        $monthly = collect(range(5, 0))->map(function (int $i) {
            $monthStart = now()->startOfMonth()->subMonths($i);
            $monthEnd = (clone $monthStart)->endOfMonth();

            $donations = BloodDonation::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('status', '!=', BloodDonation::STATUS_REJECTED)
                ->sum('quantity');

            $issued = BloodIssue::whereBetween('issue_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('quantity');

            return [
                'label' => $monthStart->format('M Y'),
                'donations' => (int) $donations,
                'issued' => (int) $issued,
            ];
        });

        // Group-wise inventory (sums history; snapshot shows current available)
        $groupStats = BloodGroup::withCount(['donations'])->get()->map(function (BloodGroup $group) {
            $available = BloodDonation::where('blood_group_id', $group->id)
                ->whereIn('status', [BloodDonation::STATUS_AVAILABLE, BloodDonation::STATUS_RESERVED])
                ->sum('quantity');

            $group->available_quantity = (int) $available;
            $group->donation_count = (int) $group->donations_count;
            $group->issued_count = BloodIssue::where('blood_group_id', $group->id)->count();

            return $group;
        });

        $stats = [
            'period_donations' => (int) BloodDonation::whereBetween('created_at', [$from, $to])
                ->where('status', '!=', BloodDonation::STATUS_REJECTED)
                ->sum('quantity'),
            'period_issued' => (int) $issuedByGroup->sum(),
            'period_requests_created' => (int) BloodRequest::whereBetween('created_at', [$from, $to])->count(),
            'period_requests_fulfilled' => (int) BloodRequest::whereBetween('created_at', [$from, $to])
                ->whereIn('status', [BloodRequest::STATUS_FULFILLED, BloodRequest::STATUS_PARTIALLY_APPROVED])
                ->count(),
        ];

        return view('backend.reports.index', compact('donationStats', 'issuedByGroup', 'monthly', 'groupStats', 'stats', 'from', 'to'));
    }

    /**
     * Export donation records to CSV for the given period.
     */
    public function exportDonations(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $request->filled('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();

        $donations = BloodDonation::with(['donor', 'bloodGroup'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $rows = $donations->map(fn (BloodDonation $d) => [
            'ID' => $d->id,
            'Bag Number' => $d->bag_number,
            'Donor ID' => $d->donor_id,
            'Donor Name' => $d->donor->name ?? 'Walk-in / Anonymous',
            'Blood Group' => $d->bloodGroup->name ?? '',
            'Quantity (ml)' => $d->quantity,
            'Phone' => $d->donor->phone ?? '',
            'Blood Pressure' => $d->blood_pressure,
            'Hemoglobin' => $d->hemoglobin,
            'Status' => ucfirst($d->status),
            'Collected At' => $d->created_at->format('Y-m-d H:i'),
            'Expiry' => $d->expiry_date?->format('Y-m-d') ?? '',
        ])->all();

        return $this->csvExport->stream(
            sprintf('blood-donations-%s.csv', now()->format('Ymd-His')),
            ['ID', 'Bag Number', 'Donor ID', 'Donor Name', 'Blood Group', 'Quantity (ml)', 'Phone', 'Blood Pressure', 'Hemoglobin', 'Status', 'Collected At', 'Expiry'],
            $rows,
        );
    }

    /**
     * Export requests to CSV for the given period.
     */
    public function exportRequests(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $request->filled('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();

        $requests = BloodRequest::with(['patient', 'bloodGroup', 'doctor'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        $rows = $requests->map(fn (BloodRequest $r) => [
            'ID' => $r->id,
            'Patient' => $r->patient->name ?? '',
            'Phone' => $r->patient->phone ?? '',
            'Blood Group' => $r->bloodGroup->name ?? '',
            'Quantity (ml)' => $r->quantity,
            'Issued (ml)' => $r->issuedQuantity(),
            'Urgency' => ucfirst($r->urgency),
            'Required Date' => $r->required_date->format('Y-m-d'),
            'Doctor' => $r->doctor->name ?? '',
            'Department' => $r->department ?? '',
            'Status' => ucfirst(str_replace('_', ' ', $r->status)),
            'Requested At' => $r->created_at->format('Y-m-d H:i'),
        ])->all();

        return $this->csvExport->stream(
            sprintf('blood-requests-%s.csv', now()->format('Ymd-His')),
            ['ID', 'Patient', 'Phone', 'Blood Group', 'Quantity (ml)', 'Issued (ml)', 'Urgency', 'Required Date', 'Doctor', 'Department', 'Status', 'Requested At'],
            $rows,
        );
    }

    /**
     * Export issued (transfusion) records to CSV for the given period.
     */
    public function exportIssues(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $from = $request->filled('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();

        $issues = BloodIssue::with(['patient', 'bloodGroup', 'donation'])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('issue_date')
            ->get();

        $rows = $issues->map(fn (BloodIssue $i) => [
            'ID' => $i->id,
            'Request ID' => $i->request_id,
            'Patient' => $i->patient->name ?? '',
            'Blood Group' => $i->bloodGroup->name ?? '',
            'Quantity (ml)' => $i->quantity,
            'Bag Number' => $i->donation->bag_number ?? '',
            'Issue Date' => $i->issue_date->format('Y-m-d'),
            'Received By' => $i->receiver_name ?? '',
            'Receiver Phone' => $i->receiver_phone ?? '',
            'Notes' => $i->notes ?? '',
        ])->all();

        return $this->csvExport->stream(
            sprintf('blood-issues-%s.csv', now()->format('Ymd-His')),
            ['ID', 'Request ID', 'Patient', 'Blood Group', 'Quantity (ml)', 'Bag Number', 'Issue Date', 'Received By', 'Receiver Phone', 'Notes'],
            $rows,
        );
    }
}
