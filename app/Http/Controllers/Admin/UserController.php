<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStaffUserRequest;
use App\Models\User;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Hospital staff register: every login account except patients.
     */
    public function index(Request $request): Response
    {
        $users = User::where('role', '!=', 'patient')
            ->when($request->search, fn ($q, $s) => $q->where(fn ($search) => $search
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $users->through(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'joinedAt' => $user->created_at?->format('M j, Y'),
        ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => [
                'data' => $users->items(),
                'currentPage' => $users->currentPage(),
                'lastPage' => $users->lastPage(),
                'firstItem' => $users->firstItem(),
                'lastItem' => $users->lastItem(),
                'total' => $users->total(),
                'previousPageUrl' => $users->previousPageUrl(),
                'nextPageUrl' => $users->nextPageUrl(),
            ],
            'filters' => ['search' => (string) $request->query('search', '')],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.users.index'),
                'editBase' => url('/admin/users'),
            ],
        ]);
    }

    /**
     * Edit the role for a user.
     */
    public function edit(User $user): Response
    {
        $staffRoles = ['patient', 'doctor', 'admin', 'receptionist', 'lab-technician', 'pharmacist'];

        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'joinedAt' => $user->created_at?->format('M j, Y'),
            ],
            'staffRoles' => $staffRoles,
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.users.index'),
                'update' => route('admin.users.update', $user),
            ],
        ]);
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
