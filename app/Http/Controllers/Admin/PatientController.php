<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index()
    {
        $patients = User::where('role', 'patient')
            ->latest()
            ->paginate(10);

        return view('backend.patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        return view('backend.patients.create');
    }

    /**
     * Store a newly created patient.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        $user = new User($validated);
        $user->role = 'patient';
        $user->save();

        return redirect()
            ->route('admin.patients.index')
            ->with('success', 'Patient created successfully.');
    }

    /**
     * Display a specific patient.
     */
    public function show(string $id)
    {
        $patient = User::where('role', 'patient')
            ->findOrFail($id);

        return view('backend.patients.show', compact('patient'));
    }

    /**
     * Show the form for editing a patient.
     */
    public function edit(string $id)
    {
        $patient = User::where('role', 'patient')
            ->findOrFail($id);

        return view('backend.patients.edit', compact('patient'));
    }

    /**
     * Update patient information.
     */
    public function update(Request $request, string $id)
    {
        $patient = User::where('role', 'patient')
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$patient->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $patient->update($validated);

        return redirect()
            ->route('admin.patients.index')
            ->with('success', 'Patient updated successfully.');
    }

    /**
     * Delete a patient.
     */
    public function destroy(string $id)
    {
        $patient = User::where('role', 'patient')
            ->findOrFail($id);

        $patient->delete();

        return redirect()
            ->route('admin.patients.index')
            ->with('success', 'Patient deleted successfully.');
    }
}
