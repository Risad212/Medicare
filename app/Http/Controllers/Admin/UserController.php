<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStaffUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private RoleService $roles) {}

    /**
     * Staff & users register: every login account with its roles.
     */
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->withCount('roles as roles_count')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('backend.users.index', compact('users'));
    }

    /**
     * Edit staff role + RBAC role assignments for a user.
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::withCount('permissions')->orderBy('name')->get();
        $assigned = $user->roles->pluck('id')->all();
        $staffRoles = ['patient', 'doctor', 'admin', 'receptionist', 'lab-technician', 'pharmacist'];

        return view('backend.users.edit', compact('user', 'roles', 'assigned', 'staffRoles'));
    }

    /**
     * Save staff role + RBAC role assignments.
     */
    public function update(UpdateStaffUserRequest $request, User $user)
    {
        if ($user->is($request->user()) && $request->validated()['staff_role'] !== 'admin') {
            return back()->with('error', 'You cannot remove your own admin access.');
        }

        $this->roles->assignToUser($user, $request->validated());

        return redirect()->route('admin.users.index')->with('success', "Access updated for '{$user->name}'.");
    }
}
