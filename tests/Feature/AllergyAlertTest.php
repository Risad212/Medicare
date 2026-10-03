<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllergyAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_save_allergies_from_profile(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($patient)->put('/profile', [
            'name' => $patient->name,
            'email' => $patient->email,
            'allergies' => 'Penicillin, peanuts',
        ]);

        $response->assertRedirect();
        $this->assertSame('Penicillin, peanuts', $patient->fresh()->allergies);
    }

    public function test_allergies_validation_rejects_oversized_input(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $response = $this->actingAs($patient)->put('/profile', [
            'name' => $patient->name,
            'email' => $patient->email,
            'allergies' => str_repeat('a', 2001),
        ]);

        $response->assertSessionHasErrors('allergies');
    }

    public function test_doctor_create_form_exposes_patient_allergies(): void
    {
        [$doctorUser] = $this->makeDoctorWithPatient('Penicillin');

        $response = $this->actingAs($doctorUser)->get('/doctor/prescriptions/create');

        $response->assertOk();
        $response->assertSee('allergy_alert', false);
        $response->assertSee('Penicillin');
    }

    public function test_doctor_edit_shows_banner_only_when_allergies_present(): void
    {
        [$doctorUser, $doctor, $patient] = $this->makeDoctorWithPatient('Latex, aspirin');

        $prescription = Prescription::create([
            'doctor_id' => $doctor->id,
            'patient_user_id' => $patient->id,
            'patient_name' => $patient->name,
            'diagnosis' => 'Flu',
        ]);

        $response = $this->actingAs($doctorUser)->get("/doctor/prescriptions/{$prescription->id}/edit");

        $response->assertOk();
        $response->assertSee('Latex, aspirin');

        $patient->update(['allergies' => null]);

        $response = $this->actingAs($doctorUser)->get("/doctor/prescriptions/{$prescription->id}/edit");

        $response->assertOk();
        $response->assertDontSee('Latex, aspirin');
    }

    public function test_guest_appointment_has_no_allergy_banner_data(): void
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr Guest',
            'slug' => 'dr-guest',
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);

        Appointment::create([
            'user_id' => null,
            'doctor_id' => $doctor->id,
            'patient_name' => 'Walk-in Joe',
            'gender' => 1,
            'phone' => '01700000001',
            'visit_type' => 1,
            'appointment_date' => now()->toDateString(),
            'status' => 1,
        ]);

        $response = $this->actingAs($doctorUser)->get('/doctor/prescriptions/create');

        $response->assertOk();
        $response->assertSee('data-allergies=""', false);
    }

    /**
     * @return array{0: User, 1: Doctor, 2: User}
     */
    private function makeDoctorWithPatient(?string $allergies): array
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr House',
            'slug' => 'dr-house-'.uniqid(),
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        $patient = User::factory()->create(['role' => 'patient', 'allergies' => $allergies]);

        Appointment::create([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'patient_name' => $patient->name,
            'gender' => 1,
            'phone' => '01700000000',
            'visit_type' => 1,
            'appointment_date' => now()->toDateString(),
            'status' => 1,
        ]);

        return [$doctorUser, $doctor, $patient];
    }

    public function test_disabled_module_hides_allergy_ui_and_ignores_input(): void
    {
        config(['modules.allergy' => false]);
        [$doctorUser] = $this->makeDoctorWithPatient('Penicillin');

        $this->actingAs($doctorUser)->get('/doctor/prescriptions/create')->assertOk()->assertDontSee('id="allergy_alert"', false);

        $patient = User::factory()->create(['role' => 'patient']);
        $this->actingAs($patient)->put('/profile', [
            'name' => $patient->name,
            'email' => $patient->email,
            'allergies' => 'Sneaky input',
        ])->assertRedirect();
        $this->assertNull($patient->fresh()->allergies);
    }
}
