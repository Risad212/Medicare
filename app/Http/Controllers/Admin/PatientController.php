<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequest;
use App\Models\Appointment;
use App\Models\Patient;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    /**
     * Display a listing of patient records (clinic register, not accounts).
     */
    public function index(Request $request): Response
    {
        $search = $request->search ? str_replace(['%', '_'], ['\%', '\_'], $request->search) : null;
        $patients = Patient::when($search, function ($query) use ($search) {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%');
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $patients->through(fn (Patient $patient) => [
            'id' => $patient->id,
            'name' => $patient->name,
            'email' => $patient->email,
            'phone' => $patient->phone,
            'gender' => $patient->gender,
            'dateOfBirth' => $patient->date_of_birth?->format('Y-m-d'),
            'registered' => $patient->created_at?->format('Y-m-d'),
        ]);

        return Inertia::render('Admin/Patients/Index', [
            'patients' => [
                'data' => $patients->items(),
                'currentPage' => $patients->currentPage(),
                'lastPage' => $patients->lastPage(),
                'firstItem' => $patients->firstItem(),
                'lastItem' => $patients->lastItem(),
                'total' => $patients->total(),
                'previousPageUrl' => $patients->previousPageUrl(),
                'nextPageUrl' => $patients->nextPageUrl(),
            ],
            'filters' => ['search' => (string) $request->query('search', '')],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.patients.index'),
                'create' => route('admin.patients.create'),
                'export' => route('admin.exports.patients'),
                'showBase' => url('/admin/patients'),
                'editBase' => url('/admin/patients'),
                'deleteBase' => url('/admin/patients'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new patient record (no login account).
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Patients/Create', [
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.patients.index'),
                'store' => route('admin.patients.store'),
            ],
        ]);
    }

    /**
     * Store a newly created patient record.
     */
    public function store(PatientRequest $request)
    {
        Patient::create($request->validated());

        return redirect()
            ->route('admin.patients.index')
            ->with('success', 'Patient created successfully.');
    }

    /**
     * Display a specific patient with visits matched by email/phone.
     */
    public function show(Patient $patient): Response
    {
        $visits = collect();

        if ($patient->email || $patient->phone) {
            $visits = Appointment::with(['doctor', 'timeSlot'])
                ->where(function ($query) use ($patient) {
                    if ($patient->email) {
                        $query->orWhere('email', $patient->email);
                    }
                    if ($patient->phone) {
                        $query->orWhere('phone', $patient->phone);
                    }
                })
                ->latest()
                ->get();
        }

        return Inertia::render('Admin/Patients/Show', [
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'email' => $patient->email,
                'phone' => $patient->phone,
                'gender' => $patient->gender,
                'dateOfBirth' => $patient->date_of_birth?->format('d M Y'),
                'bloodGroup' => $patient->blood_group,
                'address' => $patient->address,
            ],
            'visits' => $visits->map(fn (Appointment $visit) => [
                'id' => $visit->id,
                'doctor' => $visit->doctor->name ?? 'N/A',
                'date' => $visit->appointment_date?->format('d M Y') ?? 'N/A',
                'time' => $visit->timeSlot->time ?? 'N/A',
                'visitType' => $visit->visit_type_label,
                'status' => (int) $visit->status,
            ])->values(),
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.patients.index'),
                'edit' => route('admin.patients.edit', $patient),
            ],
        ]);
    }

    /**
     * Show the form for editing a patient record.
     */
    public function edit(Patient $patient): Response
    {
        return Inertia::render('Admin/Patients/Edit', [
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'email' => $patient->email,
                'phone' => $patient->phone,
                'gender' => $patient->gender,
                'dateOfBirth' => $patient->date_of_birth?->format('Y-m-d'),
                'bloodGroup' => $patient->blood_group,
                'address' => $patient->address,
                'registeredYear' => $patient->created_at?->format('Y'),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.patients.index'),
                'update' => route('admin.patients.update', $patient),
            ],
        ]);
    }

    /**
     * Update patient information.
     */
    public function update(PatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

        return redirect()
            ->route('admin.patients.index')
            ->with('success', 'Patient updated successfully.');
    }

    /**
     * Delete a patient record (login accounts are never touched).
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()
            ->route('admin.patients.index')
            ->with('success', 'Patient deleted successfully.');
    }
}
