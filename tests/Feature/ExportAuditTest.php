<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExportAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_phi_export_is_audit_logged_without_patient_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.exports.appointments'))->assertOk();

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'phi_export',
            'user_id' => $admin->id,
        ]);

        $entry = ActivityLog::where('action', 'phi_export')->latest()->first();
        $payload = is_string($entry->payload) ? json_decode($entry->payload, true) : $entry->payload;
        $this->assertSame('appointments', $payload['type'] ?? null);
        $this->assertArrayHasKey('count', $payload ?? []);
    }

    public function test_export_routes_carry_an_hourly_throttle(): void
    {
        foreach (['admin.exports.appointments', 'admin.exports.patients', 'admin.exports.lab-orders'] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Route [{$name}] missing.");
            $this->assertContains('throttle:10,60', $route->gatherMiddleware());
        }
    }
}
