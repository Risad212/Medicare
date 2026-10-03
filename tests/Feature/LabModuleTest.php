<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_module_hides_lab_routes_but_keeps_dashboard(): void
    {
        config(['modules.lab' => false]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/lab-tests')->assertNotFound();
        $this->actingAs($admin)->get('/admin/lab-orders')->assertNotFound();
        $this->actingAs($admin)->get('/admin/exports/lab-orders')->assertNotFound();

        $doctor = User::factory()->create(['role' => 'doctor']);
        $this->actingAs($doctor)->get('/doctor/lab-orders')->assertNotFound();

        // Core pages degrade gracefully without the lab module.
        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/invoices')->assertOk();

        $patient = User::factory()->create(['role' => 'patient']);
        $this->actingAs($patient)->get('/profile')->assertOk();
    }

    public function test_enabled_module_serves_lab_tests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/lab-tests')->assertOk();
    }
}
