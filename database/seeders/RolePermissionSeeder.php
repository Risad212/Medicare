<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Module permissions: slug => [name, group].
     *
     * @var array<string, array{string, string}>
     */
    public const PERMISSIONS = [
        'dashboard.view' => ['View dashboard', 'Clinic'],
        'appointments.manage' => ['Manage appointments & time slots', 'Clinic'],
        'patients.manage' => ['Manage patients', 'Clinic'],
        'doctors.manage' => ['Manage doctors', 'Clinic'],
        'reviews.manage' => ['Moderate reviews', 'Clinic'],
        'lab.manage' => ['Manage lab tests & orders', 'Laboratory'],
        'prescriptions.manage' => ['Manage prescriptions', 'Laboratory'],
        'invoices.manage' => ['Manage invoices', 'Laboratory'],
        'bloodbank.manage' => ['Manage blood bank', 'Blood Bank'],
        'content.manage' => ['Manage departments, services, blogs & sliders', 'Content'],
        'settings.manage' => ['Manage site settings & SEO', 'Content'],
        'roles.manage' => ['Manage roles & permissions', 'System'],
        'users.manage' => ['Manage staff & users', 'System'],
        'activity-logs.view' => ['View activity logs', 'System'],
        'exports.manage' => ['Export CSV reports', 'System'],
    ];

    /**
     * Default roles: slug => [name, description, permission slugs ('*' = all)].
     *
     * @var array<string, array{string, string, array<int, string>}>
     */
    public const ROLES = [
        'admin' => ['Administrator', 'Full access to every module.', ['*']],
        'receptionist' => ['Receptionist', 'Front desk: bookings, patients, doctors.', ['dashboard.view', 'appointments.manage', 'patients.manage', 'doctors.manage']],
        'lab-technician' => ['Lab Technician', 'Laboratory tests, orders and invoices.', ['dashboard.view', 'lab.manage', 'invoices.manage']],
        'pharmacist' => ['Pharmacist', 'Prescriptions and dispensing.', ['dashboard.view', 'prescriptions.manage']],
        'doctor' => ['Doctor', 'Clinical staff (uses the doctor panel).', []],
        'patient' => ['Patient', 'Default account for site users.', []],
    ];

    /**
     * Seed roles + permissions idempotently (safe to re-run).
     */
    public function run(): void
    {
        DB::transaction(function () {
            $permissions = collect(self::PERMISSIONS)->mapWithKeys(
                fn ($meta, $slug) => [$slug => Permission::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $meta[0], 'group' => $meta[1]]
                )]
            );

            foreach (self::ROLES as $slug => [$name, $description, $grants]) {
                $role = Role::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'description' => $description]
                );

                $ids = in_array('*', $grants, true)
                    ? $permissions->pluck('id')->all()
                    : $permissions->only($grants)->pluck('id')->all();

                $role->permissions()->sync($ids);
            }
        });
    }
}
