<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private RoleService $roles) {}

    /**
     * List all roles with user + permission counts.
     */
    public function index(Request $request)
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('backend.roles.index', compact('roles'));
    }

    /**
     * Show the create form with the grouped permission matrix.
     */
    public function create()
    {
        $grouped = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');

        return view('backend.roles.create', compact('grouped'));
    }

    /**
     * Store a new role with its grants.
     */
    public function store(StoreRoleRequest $request)
    {
        $role = $this->roles->create($request->validated());

        return redirect()->route('admin.roles.edit', $role)->with('success', "Role '{$role->name}' created.");
    }

    /**
     * Edit a role and its permission matrix.
     */
    public function edit(Role $role)
    {
        $role->load('permissions');
        $grouped = Permission::orderBy('group')->orderBy('name')->get()->groupBy('group');
        $granted = $role->permissions->pluck('id')->all();

        return view('backend.roles.edit', compact('role', 'grouped', 'granted'));
    }

    /**
     * Update a role and replace its grants.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->roles->update($role, $request->validated());

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' updated.");
    }

    /**
     * Delete a role (grants + assignments cascade).
     */
    public function destroy(Role $role)
    {
        if (in_array($role->slug, ['admin', 'doctor', 'patient'], true)) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' deleted.");
    }
}
