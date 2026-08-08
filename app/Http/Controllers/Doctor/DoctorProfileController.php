<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoctorProfileController extends Controller
{
    public function edit()
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();
        return view('backend.doctor-dashboard.profile', compact('doctor'));
    }

    public function update(Request $request)
    {
        $doctor = Doctor::where('user_id', auth()->id())->first();

        $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string',
            'degree'    => 'nullable|string',
            'specialist'=> 'nullable|string',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        auth()->user()->update(['name' => $request->name]);

        $data = $request->except('image');
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('doctors', 'public');
        }
        $doctor->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }
}