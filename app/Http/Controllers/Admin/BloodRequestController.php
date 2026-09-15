<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BloodDonation;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Doctor;
use App\Models\User;
use App\Services\BloodBankService;
use Illuminate\Http\Request;

class BloodRequestController extends Controller
{
    public function __construct(
        private readonly BloodBankService $bankService,
    ) {}

    /**
     * Filterable, paginated list of blood requests.
     */
    public function index(Request $request)
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

        return view('backend.blood-requests.index', [
            'requests' => $requests,
            'bloodGroups' => BloodGroup::orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a blood request.
     */
    public function create()
    {
        return view('backend.blood-requests.create', [
            'patients' => User::where('role', 'patient')->orderBy('name')->get(),
            'doctors' => Doctor::orderBy('name')->get(),
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(),
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
    public function show(BloodRequest $bloodRequest)
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

        return view('backend.blood-requests.show', [
            'request' => $bloodRequest,
            'availableUnits' => $availableUnits,
            'availableQty' => $availableQty,
            'reservedUnits' => $reservedForRequest,
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
