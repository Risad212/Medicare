<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequest;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of patient records (clinic register, not accounts).
     */
    public function index(Request $request)
    {
        $search = $request->search ? str_replace(['%', '_'], ['\%', '\_'], $request->search) : null;
        $patients = Patient::when($search, function ($query) use ($search) {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%');
        })
            ->latest()
            ->paginate(10);

        return view('backend.patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new patient record (no login account).
     */
    public function create()
    {
        return view('backend.patients.create');
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
    public function show(Patient $patient)
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

        return view('backend.patients.show', compact('patient', 'visits'));
    }

    /**
     * Show the form for editing a patient record.
     */
    public function edit(Patient $patient)
    {
        return view('backend.patients.edit', compact('patient'));
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
