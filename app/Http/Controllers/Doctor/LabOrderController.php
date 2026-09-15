<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabOrderController extends Controller
{
    /**
     * Display the list of lab orders created by the current doctor.
     */
    public function index(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        $orders = LabOrder::with(['items.test', 'appointment'])
            ->where('doctor_id', $doctor->id)
            ->when($request->filled('status'), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10);

        return view('backend.doctor-dashboard.lab-orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new lab order.
     */
    public function create()
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        $tests = LabTest::where('status', true)->orderBy('name')->get();
        $patients = User::where('role', 'patient')->orderBy('name')->get();
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 1)
            ->latest()
            ->get();

        return view('backend.doctor-dashboard.lab-orders.create', compact('tests', 'patients', 'appointments', 'doctor'));
    }

    /**
     * Store a newly created lab order.
     */
    public function store(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'patient_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'priority' => 'sometimes|in:normal,urgent',
            'note' => 'nullable|string|max:2000',
            'lab_test_ids' => 'required|array|min:1',
            'lab_test_ids.*' => 'exists:lab_tests,id',
        ]);

        $appointment = null;
        if ($validated['appointment_id'] ?? null) {
            $appointment = Appointment::findOrFail($validated['appointment_id']);
            abort_if($appointment->doctor_id !== $doctor->id, 403, 'Unauthorized appointment access.');
        }

        $user = ($validated['user_id'] ?? null) ? User::find($validated['user_id']) : null;

        // Derive patient identity from the selected appointment/user so the
        // order can never disagree with the account it appears under in the
        // patient's "My Lab Reports" tab. Falls back to the typed values for
        // walk-ins. user_id follows the same rule: appointment wins, then
        // the selected user, never a mismatched submitted id.
        $patientName = $appointment?->patient_name ?? $user?->name ?? $validated['patient_name'];
        $phone = $appointment?->phone ?? $user?->phone ?? ($validated['phone'] ?? null);
        $email = $appointment?->email ?? $user?->email ?? ($validated['email'] ?? null);
        $resolvedUserId = $appointment?->user_id ?? $user?->id ?? null;

        $tests = LabTest::where('status', true)->whereIn('id', $validated['lab_test_ids'])->get();
        abort_if($tests->isEmpty(), 422, 'No active tests selected.');

        $total = $tests->sum(fn ($test) => (float) $test->price);

        $order = DB::transaction(function () use ($doctor, $validated, $tests, $total, $patientName, $phone, $email, $resolvedUserId) {
            $order = LabOrder::create([
                'doctor_id' => $doctor->id,
                'user_id' => $resolvedUserId,
                'appointment_id' => $validated['appointment_id'] ?? null,
                'patient_name' => $patientName,
                'phone' => $phone,
                'email' => $email,
                'priority' => $validated['priority'] ?? 'normal',
                'note' => $validated['note'] ?? null,
                'status' => 'pending',
                'total' => $total,
            ]);

            $items = $tests->map(fn ($test) => new LabOrderItem([
                'lab_test_id' => $test->id,
                'price' => $test->price,
            ]));

            $order->items()->saveMany($items);

            return $order;
        });

        return redirect()
            ->route('doctor.lab-orders.show', $order)
            ->with('success', 'Lab order created successfully.');
    }

    /**
     * Display a single lab order.
     */
    public function show(LabOrder $order)
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        abort_if($order->doctor_id !== $doctor->id, 403, 'Unauthorized order access.');

        $order->load(['items.test', 'appointment.timeSlot', 'user']);

        return view('backend.doctor-dashboard.lab-orders.show', compact('order'));
    }
}
