<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BloodBankModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_module_hides_bloodbank_routes(): void
    {
        config(['modules.bloodbank' => false]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/bloodbank')->assertNotFound();
        $this->actingAs($admin)->get('/admin/bloodbank/inventory')->assertNotFound();
        $this->actingAs($admin)->get('/admin/blood-donors')->assertNotFound();
        $this->actingAs($admin)->get('/admin/blood-requests')->assertNotFound();
        $this->actingAs($admin)->get('/admin/blood-reports')->assertNotFound();

        $doctor = User::factory()->create(['role' => 'doctor']);
        $this->actingAs($doctor)->get('/doctor/blood-requests')->assertNotFound();

        $patient = User::factory()->create(['role' => 'patient']);
        $this->actingAs($patient)->get('/profile/blood-requests')->assertNotFound();
    }

    public function test_enabled_module_serves_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/bloodbank')->assertOk();
    }
}
