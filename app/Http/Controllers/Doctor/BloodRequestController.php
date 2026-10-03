<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BloodDonation;
use App\Models\BloodGroup;
use App\Models\BloodRequest;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;

class BloodRequestController extends Controller
{
    /**
     * The authenticated doctor's own blood requests.
     */
    protected function doctor(): Doctor
    {
        return Doctor::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * List requests assigned to the authenticated doctor.
     */
    public function index(Request $request)
    {
        $doctor = $this->doctor();

        $query = BloodRequest::with(['patient', 'bloodGroup'])
            ->where('doctor_id', $doctor->id);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $requests = $query->latest()->paginate(10)->withQueryString();

        return view('doctor.blood-requests.index', compact('requests'));
    }

    /**
     * Form to raise a request for one of the doctor's patients.
     */
    public function create()
    {
        $doctor = $this->doctor();

        $patients = User::where('role', 'patient')
            ->whereHas('appointments', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->orderBy('name')
            ->get();

        return view('doctor.blood-requests.create', [
            'patients' => $patients,
            'bloodGroups' => BloodGroup::where('status', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new request for one of the doctor's patients.
     */
    public function store(Request $request)
    {
        $doctor = $this->doctor();

        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'blood_group_id' => 'required|exists:blood_groups,id',
            'quantity' => 'required|integer|min:1|max:10000',
            'required_date' => 'required|date|after_or_equal:today',
            'urgency' => 'required|in:normal,urgent,emergency',
            'department' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $isOwnPatient = Appointment::where('doctor_id', $doctor->id)
            ->where('user_id', $validated['patient_id'])
            ->exists();

        if (! $isOwnPatient) {
            return back()->with('error', 'You can only raise requests for your own patients.')->withInput();
        }

        BloodRequest::create([
            ...$validated,
            'doctor_id' => $doctor->id,
            'unit' => 'ml',
            'status' => BloodRequest::STATUS_PENDING,
            'requested_by' => auth()->id(),
        ]);

        return redirect()->route('doctor.blood-requests.index')->with('success', 'Blood request submitted for admin approval.');
    }

    /**
     * Show a single request and its issue history (read-only for doctors).
     */
    public function show(BloodRequest $bloodRequest)
    {
        if ($bloodRequest->doctor_id !== $this->doctor()->id) {
            abort(403, 'You cannot access this request.');
        }

        $bloodRequest->load(['patient', 'bloodGroup', 'issues.donation']);

        return view('doctor.blood-requests.show', [
            'request' => $bloodRequest,
            'reservedUnits' => BloodDonation::where('reserved_for_request_id', $bloodRequest->id)
                ->orderBy('expiry_date')
                ->get(),
        ]);
    }
}
