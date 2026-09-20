<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStaffUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Hospital staff register: every login account except patients.
     */
    public function index(Request $request)
    {
        $users = User::where('role', '!=', 'patient')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('backend.users.index', compact('users'));
    }

    /**
     * Edit the role for a user.
     */
    public function edit(User $user)
    {
        $staffRoles = ['patient', 'doctor', 'admin', 'receptionist', 'lab-technician', 'pharmacist'];

        return view('backend.users.edit', compact('user', 'staffRoles'));
    }

    /**
     * Save the role for a user.
     */
    public function update(UpdateStaffUserRequest $request, User $user)
    {
        if ($user->is($request->user()) && $request->validated()['staff_role'] !== 'admin') {
            return back()->with('error', 'You cannot remove your own admin access.');
        }

        $user->update(['role' => $request->validated()['staff_role']]);

        return redirect()->route('admin.users.index')->with('success', "Access updated for '{$user->name}'.");
    }
}
