<?php

namespace App\Modules\BloodBank\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\User;
use App\Modules\BloodBank\Models\BloodDonation;
use App\Modules\BloodBank\Models\BloodGroup;
use App\Modules\BloodBank\Models\BloodRequest;
use App\Modules\BloodBank\Services\BloodBankService;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BloodRequestController extends Controller
{
    public function __construct(
        private readonly BloodBankService $bankService,
    ) {}

    /**
     * Filterable, paginated list of blood requests.
     */
    public function index(Request $request): Response
    {
        $query = BloodRequest::with(['patient', 'bloodGroup', 'doctor']);

        if ($search = $request->query('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], trim($search));
            $query->whereHas('patient', fn ($q) => $q->where('name', 'like', "%{$escaped}%"));
        }

        if ($request->filled('blood_group_id')) {
            $query->where('blood_group_id', $request->query('blood_group_id'));
        }

        if ($request->filled('urgency')) {
            $query->where('urgency', $request->query('urgency'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('required_date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('required_date', '<=', $request->query('to'));
        }

        $requests = $query->latest()->paginate(10)->withQueryString();

        $requests->through(fn (BloodRequest $bloodRequest) => [
            'id' => $bloodRequest->id,
            'patientName' => $bloodRequest->patient?->name ?? 'Unknown',
            'department' => $bloodRequest->department,
            'bloodGroup' => $bloodRequest->bloodGroup?->name ?? '—',
            'quantity' => $bloodRequest->quantity,
            'unit' => $bloodRequest->unit,
            'urgency' => $bloodRequest->urgency,
            'requiredDate' => $bloodRequest->required_date->format('Y-m-d'),
            'status' => $bloodRequest->status,
        ]);

        return Inertia::render('Admin/BloodBank/Requests/Index', [
            'requests' => [
                'data' => $requests->items(),
                'currentPage' => $requests->currentPage(),
                'lastPage' => $requests->lastPage(),
                'firstItem' => $requests->firstItem(),
                'lastItem' => $requests->lastItem(),
                'total' => $requests->total(),
                'previousPageUrl' => $requests->previousPageUrl(),
                'nextPageUrl' => $requests->nextPageUrl(),
            ],
            'bloodGroups' => BloodGroup::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'bloodGroupId' => (string) $request->query('blood_group_id', ''),
                'urgency' => (string) $request->query('urgency', ''),
                'status' => (string) $request->query('status', ''),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-requests.index'),
                'create' => route('admin.blood-requests.create'),
                'showBase' => url('/admin/blood-requests'),
            ],
        ]);
    }

    /**
     * Show the form for creating a blood request.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/BloodBank/Requests/Create', [
            'patients' => User::where('role', 'patient')->orderBy('name')->get(['id', 'name', 'email']),
            'doctors' => Doctor::orderBy('name')->get(['id', 'name']),
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(['id', 'name']),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-requests.index'),
                'store' => route('admin.blood-requests.store'),
            ],
        ]);
    }

    /**
     * Store a newly created blood request (starts as pending).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'blood_group_id' => 'required|exists:blood_groups,id',
            'quantity' => 'required|integer|min:1|max:10000',
            'required_date' => 'required|date|after_or_equal:today',
            'urgency' => 'required|in:normal,urgent,emergency',
            'department' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:2000',
            'doctor_id' => 'nullable|exists:doctors,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        BloodRequest::create([
            ...$validated,
            'unit' => 'ml',
            'status' => BloodRequest::STATUS_PENDING,
            'requested_by' => auth()->id(),
        ]);

        return redirect()->route('admin.blood-requests.index')->with('success', 'Blood request created and queued for approval.');
    }

    /**
     * Display request details: patient, requested vs available, issues.
     */
    public function show(BloodRequest $bloodRequest): Response
    {
        $bloodRequest->load(['patient', 'doctor', 'bloodGroup', 'requester', 'issues.donation']);

        $availableForGroup = BloodDonation::where('blood_group_id', $bloodRequest->blood_group_id)
            ->where('status', BloodDonation::STATUS_AVAILABLE)
            ->whereDate('expiry_date', '>=', now()->toDateString())
            ->get();

        $availableUnits = $availableForGroup->count();
        $availableQty = (int) $availableForGroup->sum('quantity');

        $reservedForRequest = BloodDonation::where('reserved_for_request_id', $bloodRequest->id)
            ->orderBy('expiry_date')
            ->get();

        return Inertia::render('Admin/BloodBank/Requests/Show', [
            'bloodRequest' => [
                'id' => $bloodRequest->id,
                'patientName' => $bloodRequest->patient?->name ?? 'Unknown',
                'doctorName' => $bloodRequest->doctor?->name,
                'requesterName' => $bloodRequest->requester?->name,
                'group' => $bloodRequest->bloodGroup?->name ?? '—',
                'quantity' => $bloodRequest->quantity,
                'unit' => $bloodRequest->unit,
                'urgency' => $bloodRequest->urgency,
                'requiredDate' => $bloodRequest->required_date->format('Y-m-d'),
                'createdAt' => $bloodRequest->created_at->format('Y-m-d H:i'),
                'status' => $bloodRequest->status,
                'department' => $bloodRequest->department,
                'reason' => $bloodRequest->reason,
                'notes' => $bloodRequest->notes,
                'issuedQuantity' => $bloodRequest->issuedQuantity(),
                'issues' => $bloodRequest->issues->map(fn ($issue) => [
                    'id' => $issue->id,
                    'date' => $issue->issue_date->format('Y-m-d'),
                    'bagNumber' => $issue->donation?->bag_number ?: '#'.$issue->donation_id,
                    'quantity' => $issue->quantity,
                    'unit' => $issue->unit,
                    'receiverName' => $issue->receiver_name ?: ($bloodRequest->patient?->name ?? 'Unknown'),
                ]),
            ],
            'availableUnits' => $availableUnits,
            'availableQty' => $availableQty,
            'reservedUnits' => $reservedForRequest->map(fn (BloodDonation $bag) => [
                'id' => $bag->id,
                'bagNumber' => $bag->bag_number ?: '#'.$bag->id,
                'quantity' => $bag->quantity,
                'unit' => $bag->unit,
                'expiryDate' => $bag->expiry_date->format('Y-m-d'),
                'status' => $bag->status,
            ]),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.blood-requests.index'),
                'approve' => route('admin.blood-requests.approve', $bloodRequest),
                'reject' => route('admin.blood-requests.reject', $bloodRequest),
                'cancel' => route('admin.blood-requests.cancel', $bloodRequest),
                'delete' => route('admin.blood-requests.destroy', $bloodRequest),
                'issue' => route('admin.blood-issues.create', $bloodRequest),
                'issueShowBase' => url('/admin/blood-issues'),
            ],
        ]);
    }

    /**
     * Approve a request: reserve available bags for it (STEP 8).
     */
    public function approve(BloodRequest $bloodRequest)
    {
        if ($bloodRequest->status !== BloodRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        try {
            $result = $this->bankService->reserveForRequest($bloodRequest);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            "Request approved. Reserved {$result['reserved_units']} unit(s) ({$result['reserved_quantity']} ml)."
        );
    }

    /**
     * Reject a request and release any held reservations.
     */
    public function reject(BloodRequest $bloodRequest)
    {
        if (in_array($bloodRequest->status, [BloodRequest::STATUS_FULFILLED], true)) {
            return back()->with('error', 'A fulfilled request cannot be rejected.');
        }

        $this->bankService->releaseReservations($bloodRequest);

        $bloodRequest->update(['status' => BloodRequest::STATUS_REJECTED]);

        return back()->with('success', 'Request rejected and reservations released.');
    }

    /**
     * Cancel a request and release any held reservations.
     */
    public function cancel(BloodRequest $bloodRequest)
    {
        if ($bloodRequest->status === BloodRequest::STATUS_FULFILLED) {
            return back()->with('error', 'A fulfilled request cannot be cancelled.');
        }

        $this->bankService->releaseReservations($bloodRequest);

        $bloodRequest->update(['status' => BloodRequest::STATUS_CANCELLED]);

        return back()->with('success', 'Request cancelled and reservations released.');
    }

    /**
     * Remove a pending request only; anything with history stays intact.
     */
    public function destroy(BloodRequest $bloodRequest)
    {
        if ($bloodRequest->status !== BloodRequest::STATUS_PENDING) {
            return back()->with('error', 'Only pending requests can be deleted.');
        }

        $bloodRequest->delete();

        return redirect()->route('admin.blood-requests.index')->with('success', 'Request deleted.');
    }
}
