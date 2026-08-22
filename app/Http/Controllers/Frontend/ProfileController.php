<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show user profile.
     */
    public function index()
    {
        $user = auth()->user();

        $appointments = Appointment::with(['doctor', 'timeSlot'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view(
            'frontend.profile.index',
            compact('user', 'appointments')
        );
    }

    /**
     * Update user profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender'        => 'nullable|in:male,female,other',
            'blood_group'   => 'nullable|string|max:10',
            'address'       => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('profile_image')) {

            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $validated['profile_image'] = $request
                ->file('profile_image')
                ->store('profile-images', 'public');
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}