<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodDonation;
use App\Models\BloodGroup;
use App\Models\BloodIssue;
use App\Models\BloodRequest;
use App\Services\BloodBankService;
use Illuminate\Http\Request;

class BloodIssueController extends Controller
{
    public function __construct(
        private readonly BloodBankService $bankService,
    ) {}

    /**
     * Filterable, paginated list of issued blood.
     */
    public function index(Request $request)
    {
        $query = BloodIssue::with(['patient', 'bloodGroup']);

        if ($search = $request->query('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], trim($search));
            $query->whereHas('patient', fn ($q) => $q->where('name', 'like', "%{$escaped}%"));
        }

        if ($request->filled('blood_group_id')) {
            $query->where('blood_group_id', $request->query('blood_group_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('issue_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('issue_date', '<=', $request->query('to'));
        }

        $issues = $query->latest('issue_date')->paginate(10)->withQueryString();

        return view('backend.blood-issues.index', [
            'issues' => $issues,
            'bloodGroups' => BloodGroup::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form to issue blood against a specific approved request.
     */
    public function create(BloodRequest $bloodRequest)
    {
        if ($bloodRequest->status === BloodRequest::STATUS_PENDING) {
            return back()->with('error', 'Approve the request before issuing blood.');
        }

        if (in_array($bloodRequest->status, [BloodRequest::STATUS_REJECTED, BloodRequest::STATUS_CANCELLED, BloodRequest::STATUS_FULFILLED], true)) {
            return back()->with('error', 'Blood cannot be issued against this request.');
        }

        $reservedBags = BloodDonation::where('reserved_for_request_id', $bloodRequest->id)
            ->where('status', BloodDonation::STATUS_RESERVED)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date')
            ->get();

        if ($reservedBags->isEmpty()) {
            return back()->with('error', 'No valid reserved bags available for this request.');
        }

        return view('backend.blood-issues.create', [
            'request' => $bloodRequest,
            'reservedBags' => $reservedBags,
        ]);
    }

    /**
     * Store a newly issued record. Guards live in BloodBankService.
     */
    public function store(Request $request, BloodRequest $bloodRequest)
    {
        $validated = $request->validate([
            'donation_id' => 'required|exists:blood_donations,id',
            'issue_date' => 'required|date',
            'receiver_name' => 'nullable|string|max:255',
            'receiver_phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:2000',
        ]);

        try {
            $issue = $this->bankService->issueForRequest(
                $bloodRequest,
                $validated + ['issued_by' => auth()->id()]
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()
            ->route('admin.blood-issues.show', $issue->id)
            ->with('success', 'Blood issued successfully and stock updated.');
    }

    /**
     * Display a single issue record.
     */
    public function show(BloodIssue $bloodIssue)
    {
        $bloodIssue->load([
            'request.patient',
            'request.bloodGroup',
            'patient',
            'bloodGroup',
            'donation.donor',
            'issuer',
        ]);

        return view('backend.blood-issues.show', ['issue' => $bloodIssue]);
    }
}
