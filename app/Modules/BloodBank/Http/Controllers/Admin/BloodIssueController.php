<?php

namespace App\Modules\BloodBank\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\BloodBank\Models\BloodDonation;
use App\Modules\BloodBank\Models\BloodGroup;
use App\Modules\BloodBank\Models\BloodIssue;
use App\Modules\BloodBank\Models\BloodRequest;
use App\Modules\BloodBank\Services\BloodBankService;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BloodIssueController extends Controller
{
    public function __construct(
        private readonly BloodBankService $bankService,
    ) {}

    /**
     * Filterable, paginated list of issued blood.
     */
    public function index(Request $request): Response
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

        $issues->through(fn (BloodIssue $issue) => [
            'id' => $issue->id,
            'patientName' => $issue->patient?->name ?? 'Unknown',
            'requestId' => $issue->request_id,
            'bloodGroup' => $issue->bloodGroup?->name ?? '—',
            'quantity' => $issue->quantity,
            'unit' => $issue->unit,
            'issueDate' => $issue->issue_date->format('Y-m-d'),
            'receiverName' => $issue->receiver_name ?: ($issue->patient?->name ?? 'Unknown'),
        ]);

        return Inertia::render('Admin/BloodBank/Issues/Index', [
            'issues' => [
                'data' => $issues->items(),
                'currentPage' => $issues->currentPage(),
                'lastPage' => $issues->lastPage(),
                'firstItem' => $issues->firstItem(),
                'lastItem' => $issues->lastItem(),
                'total' => $issues->total(),
                'previousPageUrl' => $issues->previousPageUrl(),
                'nextPageUrl' => $issues->nextPageUrl(),
            ],
            'bloodGroups' => BloodGroup::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'bloodGroupId' => (string) $request->query('blood_group_id', ''),
                'from' => (string) $request->query('from', ''),
                'to' => (string) $request->query('to', ''),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-issues.index'),
                'showBase' => url('/admin/blood-issues'),
            ],
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

        $bloodRequest->load(['patient', 'bloodGroup']);

        return Inertia::render('Admin/BloodBank/Issues/Create', [
            'bloodRequest' => [
                'id' => $bloodRequest->id,
                'patientName' => $bloodRequest->patient?->name ?? 'Unknown',
                'group' => $bloodRequest->bloodGroup?->name ?? '—',
                'quantity' => $bloodRequest->quantity,
                'unit' => $bloodRequest->unit,
                'issuedQuantity' => $bloodRequest->issuedQuantity(),
            ],
            'reservedBags' => $reservedBags->map(fn (BloodDonation $bag) => [
                'id' => $bag->id,
                'bagNumber' => $bag->bag_number ?: '#'.$bag->id,
                'quantity' => $bag->quantity,
                'expiryDate' => $bag->expiry_date->format('Y-m-d'),
            ]),
            'defaults' => ['issueDate' => now()->format('Y-m-d')],
            'routes' => [
                ...AdminNavigation::routes(),
                'store' => route('admin.blood-issues.store', $bloodRequest),
                'cancel' => route('admin.blood-requests.show', $bloodRequest),
            ],
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
    public function show(BloodIssue $bloodIssue): Response
    {
        $bloodIssue->load([
            'request.patient',
            'request.bloodGroup',
            'patient',
            'bloodGroup',
            'donation.donor',
            'issuer',
        ]);

        return Inertia::render('Admin/BloodBank/Issues/Show', [
            'issue' => [
                'id' => $bloodIssue->id,
                'requestId' => $bloodIssue->request_id,
                'patientName' => $bloodIssue->patient?->name ?? 'Unknown',
                'requestGroup' => $bloodIssue->request?->bloodGroup?->name ?? '—',
                'bloodGroup' => $bloodIssue->bloodGroup?->name ?? '—',
                'quantity' => $bloodIssue->quantity,
                'unit' => $bloodIssue->unit,
                'issueDate' => $bloodIssue->issue_date->format('Y-m-d'),
                'bagNumber' => $bloodIssue->donation?->bag_number,
                'donorName' => $bloodIssue->donation?->donor?->name,
                'issuerName' => $bloodIssue->issuer?->name ?? 'Unknown',
                'receiverName' => $bloodIssue->receiver_name,
                'receiverPhone' => $bloodIssue->receiver_phone,
                'notes' => $bloodIssue->notes,
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'request' => route('admin.blood-requests.show', $bloodIssue->request_id),
            ],
        ]);
    }
}
