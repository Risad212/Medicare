<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\LabTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_model_created_is_logged_with_actor(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post(route('admin.lab-tests.store'), [
            'name' => 'Blood Sugar',
            'category' => 'Chemistry',
            'price' => 12.00,
            'status' => 1,
        ])->assertRedirect();

        $test = LabTest::where('name', 'Blood Sugar')->firstOrFail();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'labtest.created',
            'model_type' => LabTest::class,
            'model_id' => $test->id,
        ]);
    }

    public function test_model_update_is_logged_with_only_changed_columns(): void
    {
        $admin = $this->makeAdmin();
        $test = $this->makeLabTest();

        $this->actingAs($admin)->put(route('admin.lab-tests.update', $test->id), [
            'name' => 'Updated CBC Name',
            'category' => $test->category,
            'price' => $test->price,
            'status' => (int) $test->status,
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'labtest.updated',
            'model_id' => $test->id,
        ]);

        $log = ActivityLog::where('action', 'labtest.updated')
            ->where('model_id', $test->id)
            ->latest()
            ->firstOrFail();

        $this->assertEquals(['name' => 'Updated CBC Name'], $log->payload);
    }

    public function test_passwords_never_land_in_the_audit_log(): void
    {
        $this->post(route('register'), [
            'name' => 'New Patient',
            'email' => 'audit@example.com',
            'phone' => '01711-000000',
            'password' => 'secret-password-123',
            'password_confirmation' => 'secret-password-123',
        ]);

        $user = User::where('email', 'audit@example.com')->firstOrFail();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'user.created',
            'model_id' => $user->id,
        ]);

        $logs = ActivityLog::where('action', 'user.created')->where('model_id', $user->id)->get();

        foreach ($logs as $log) {
            $this->assertArrayNotHasKey('password', $log->payload ?? []);
        }
    }

    public function test_admin_can_view_activity_logs(): void
    {
        $admin = $this->makeAdmin();
        $this->makeLabTest();

        $this->actingAs($admin)
            ->get(route('admin.activity-logs.index'))
            ->assertOk()
            ->assertSee('labtest.created');
    }

    public function test_patients_cannot_view_activity_logs(): void
    {
        $this->actingAs($this->makeUser())
            ->get(route('admin.activity-logs.index'))
            ->assertRedirect('/login');
    }
}
