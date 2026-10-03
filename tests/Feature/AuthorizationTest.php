<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_doctor_dashboard(): void
    {
        $response = $this->get('/doctor/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_patient_cannot_access_admin_dashboard(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($patient)->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_patient_cannot_access_doctor_dashboard(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($patient)->get('/doctor/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_doctor_cannot_access_admin_dashboard(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);

        $response = $this->actingAs($doctorUser)->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_doctor_can_access_doctor_dashboard(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        Doctor::create([
            'name' => 'Dr Test',
            'slug' => 'dr-test',
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($doctorUser)->get('/doctor/dashboard');

        $response->assertOk();
    }
}
