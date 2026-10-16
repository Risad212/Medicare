<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_module_hides_prescription_routes_but_keeps_core(): void
    {
        config(['modules.prescriptions' => false]);
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);

        $this->actingAs($admin)->get('/admin/prescriptions')->assertNotFound();
        $this->actingAs($doctorUser)->get('/doctor/prescriptions')->assertNotFound();
        $this->actingAs($doctorUser)->get('/doctor/appointments/picker')->assertNotFound();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->get('/')->assertOk();
    }

    public function test_enabled_module_serves_prescription_lists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Rx Doctor',
            'slug' => 'rx-doctor-'.uniqid(),
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        Prescription::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Rx Patient',
            'diagnosis' => 'Flu',
        ]);

        $this->actingAs($admin)->get('/admin/prescriptions')->assertOk()->assertSee('Rx Patient');
    }
}
