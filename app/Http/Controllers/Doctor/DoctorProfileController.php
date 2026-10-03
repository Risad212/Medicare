<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\UpdateDoctorProfileRequest;
use App\Models\Doctor;

class DoctorProfileController extends Controller
{
    public function edit()
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        return view('backend.doctor-dashboard.profile', compact('doctor'));
    }

    public function update(UpdateDoctorProfileRequest $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        $validated = $request->validated();

        auth()->user()->update(['name' => $validated['name']]);

        $data = array_intersect_key($validated, array_flip(['name', 'phone', 'degree', 'specialist', 'services', 'availability']));
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('doctors', 'public');
        }
        $doctor->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }
}
