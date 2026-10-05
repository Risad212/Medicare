<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuditReactPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_list_and_role_form_render_react_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['name' => 'Front Desk', 'role' => 'receptionist']);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Index')
                ->where('users.total', 2)
                ->where('users.data', fn ($users) => collect($users)->contains(fn ($user) => $user['name'] === 'Front Desk'))
                ->has('routes.activityLogs')
            );

        $this->get("/admin/users/{$staff->id}/edit")
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Edit')
                ->where('user.role', 'receptionist')
                ->where('staffRoles.0', 'patient')
            );
    }

    public function test_staff_search_does_not_include_patient_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['name' => 'Visible staff', 'email' => 'visible@example.test', 'role' => 'doctor']);
        User::factory()->create(['name' => 'Hidden patient', 'email' => 'hidden@example.test', 'role' => 'patient']);

        $this->actingAs($admin)
            ->get('/admin/users?search=hidden%40example.test')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users/Index')
                ->where('filters.search', 'hidden@example.test')
                ->where('users.total', 0)
            );
    }

    public function test_admin_can_update_staff_role_but_not_remove_own_admin_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'receptionist']);

        $this->actingAs($admin)
            ->from('/admin/users')
            ->put("/admin/users/{$staff->id}", ['staff_role' => 'lab-technician'])
            ->assertRedirect('/admin/users');
        $this->assertSame('lab-technician', $staff->fresh()->role);

        $this->from('/admin/users')
            ->put("/admin/users/{$admin->id}", ['staff_role' => 'receptionist'])
            ->assertRedirect('/admin/users')
            ->assertSessionHas('error');
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_activity_logs_render_filtered_audit_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Audit Administrator']);
        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'appointment.created',
            'model_type' => 'App\\Models\\Appointment',
            'model_id' => 42,
            'payload' => ['patient' => 'Example', 'doctor' => 'Care team'],
            'ip_address' => '127.0.0.1',
        ]);
        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'patient.updated',
            'payload' => ['name' => 'Example'],
        ]);

        $this->actingAs($admin)
            ->get('/admin/activity-logs?search=Audit%20Administrator&action=appointment.created')
            ->assertInertia(fn ($page) => $page
                ->component('Admin/ActivityLogs/Index')
                ->where('filters.action', 'appointment.created')
                ->where('logs.total', 1)
                ->where('logs.data.0.action', 'appointment.created')
                ->where('logs.data.0.actionLabel', 'Created')
                ->where('logs.data.0.recordType', 'Appointment')
                ->where('logs.data.0.detailKeys.0', 'patient')
                ->where('availableActions', fn ($actions) => collect($actions)->contains('appointment.created') && count($actions) >= 2)
            );
    }
}
