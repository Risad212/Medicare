<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    private function seedRbac(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }

    private function makeStaff(string $roleSlug, array $grants): User
    {
        $user = $this->makeUser(['role' => $roleSlug]);
        $role = Role::firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucfirst($roleSlug)]
        );
        $role->permissions()->sync(Permission::whereIn('slug', $grants)->pluck('id'));

        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    public function test_admin_can_crud_roles_with_permissions(): void
    {
        $this->seedRbac();

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('Receptionist');

        $permissionIds = Permission::whereIn('slug', ['dashboard.view', 'appointments.manage'])->pluck('id')->all();

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.roles.create'))
            ->assertOk()
            ->assertSee('Permissions');

        $response = $this->actingAs($this->makeAdmin())->post(route('admin.roles.store'), [
            'name' => 'Front Desk',
            'slug' => 'front-desk',
            'permissions' => $permissionIds,
        ]);

        $response->assertRedirect();
        $role = Role::where('slug', 'front-desk')->firstOrFail();
        $this->assertEqualsCanonicalizing($permissionIds, $role->permissions()->pluck('permissions.id')->all());

        $this->actingAs($this->makeAdmin())->put(route('admin.roles.update', $role), [
            'name' => 'Front Desk',
            'slug' => 'front-desk',
            'permissions' => [$permissionIds[0]],
        ])->assertRedirect();

        $this->assertEquals([$permissionIds[0]], $role->refresh()->permissions()->pluck('permissions.id')->all());

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.roles.edit', $role))
            ->assertOk()
            ->assertSee('Permissions');

        $this->actingAs($this->makeAdmin())->delete(route('admin.roles.destroy', $role))->assertRedirect();
        $this->assertDatabaseMissing('roles', ['slug' => 'front-desk']);
    }

    public function test_system_roles_cannot_be_deleted(): void
    {
        $this->seedRbac();
        $admin = Role::where('slug', 'admin')->firstOrFail();

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.roles.destroy', $admin))
            ->assertRedirect();

        $this->assertDatabaseHas('roles', ['slug' => 'admin']);
    }

    public function test_receptionist_gets_only_granted_modules(): void
    {
        $this->seedRbac();
        $staff = $this->makeStaff('receptionist', ['dashboard.view', 'appointments.manage', 'patients.manage']);

        $this->actingAs($staff)->get(route('admin.home'))->assertOk();
        $this->actingAs($staff)->get(route('admin.appointments.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.patients.index'))->assertOk();

        $this->actingAs($staff)->get(route('admin.doctors.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.lab-orders.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.roles.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_staff_without_any_grant_cannot_open_admin_panel(): void
    {
        $this->seedRbac();
        $user = $this->makeUser(['role' => 'receptionist']);

        $this->actingAs($user)->get(route('admin.home'))->assertRedirect('/login');
    }

    public function test_admin_can_assign_roles_to_users(): void
    {
        $this->seedRbac();
        $user = $this->makeUser();
        $role = Role::where('slug', 'receptionist')->firstOrFail();

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Staff')
            ->assertSee($user->email);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.users.edit', $user))
            ->assertOk()
            ->assertSee('RBAC roles');

        $this->actingAs($this->makeAdmin())->put(route('admin.users.update', $user), [
            'staff_role' => 'receptionist',
            'roles' => [$role->id],
        ])->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertSame('receptionist', $user->role);
        $this->assertTrue($user->hasRole('receptionist'));
        $this->assertTrue($user->hasPermission('appointments.manage'));
        $this->assertFalse($user->hasPermission('lab.manage'));

        // Newly empowered staffer lands on the dashboard after login.
        $this->actingAs($user)->get(route('admin.home'))->assertOk();
    }

    public function test_admin_cannot_strip_own_admin_access(): void
    {
        $this->seedRbac();
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'staff_role' => 'patient',
            'roles' => [],
        ])->assertRedirect();

        $this->assertSame('admin', $admin->refresh()->role);
    }

    public function test_role_validation_rejects_bad_input(): void
    {
        $this->seedRbac();

        $this->actingAs($this->makeAdmin())->post(route('admin.roles.store'), [
            'name' => '',
            'slug' => 'BAD SLUG',
            'permissions' => [999999],
        ])->assertSessionHasErrors(['name', 'slug', 'permissions.0']);
    }
}
