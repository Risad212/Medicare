<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RoleService
{
    /**
     * Create a role with its permission grants.
     *
     * @param  array{name: string, slug: string, description?: ?string, permissions?: int[]}  $data
     */
    public function create(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
            ]);

            $role->permissions()->sync($data['permissions'] ?? []);

            return $role;
        });
    }

    /**
     * Update a role and replace its permission grants.
     *
     * @param  array{name: string, slug: string, description?: ?string, permissions?: int[]}  $data
     */
    public function update(Role $role, array $data): Role
    {
        return DB::transaction(function () use ($role, $data) {
            $role->update([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
            ]);

            $role->permissions()->sync($data['permissions'] ?? []);

            return $role->refresh();
        });
    }

    /**
     * Assign RBAC roles + staff `users.role` to a user account.
     *
     * @param  array{staff_role: string, roles?: int[]}  $data
     */
    public function assignToUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update(['role' => $data['staff_role']]);
            $user->roles()->sync($data['roles'] ?? []);

            return $user->refresh();
        });
    }
}
