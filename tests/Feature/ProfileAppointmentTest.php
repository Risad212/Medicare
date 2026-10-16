<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileAppointmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_appointment_owned_by_patient_shows_cancel_button(): void
    {
        $patient = $this->patient(verified: true);
        $appointment = $this->appointment(['user_id' => $patient->id, 'status' => 1]);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee(route('appointment.cancel', $appointment->id), false);
    }

    public function test_patient_can_cancel_an_approved_appointment(): void
    {
        $patient = $this->patient(verified: true);
        $appointment = $this->appointment(['user_id' => $patient->id, 'status' => 1]);

        $this->actingAs($patient)
            ->patch(route('appointment.cancel', $appointment->id))
            ->assertRedirect();

        $this->assertSame(3, (int) $appointment->fresh()->status);
    }

    public function test_related_but_unowned_appointment_hides_cancel_and_rejects_cancellation(): void
    {
        $patient = $this->patient(verified: true, email: 'shared@example.test');
        $appointment = $this->appointment(['user_id' => null, 'email' => 'shared@example.test', 'status' => 0]);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertDontSee(route('appointment.cancel', $appointment->id), false);

        $this->actingAs($patient)
            ->patch(route('appointment.cancel', $appointment->id))
            ->assertForbidden();
    }

    public function test_verified_patient_sees_guest_history_matched_by_email(): void
    {
        $patient = $this->patient(verified: true, email: 'guest@example.test');
        $this->appointment(['user_id' => null, 'email' => 'guest@example.test'], 'Matched Doctor');

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Matched Doctor');
    }

    public function test_unverified_patient_does_not_see_email_matched_guest_history(): void
    {
        $patient = User::factory()->create([
            'role' => 'patient',
            'email' => 'unverified@example.test',
            'email_verified_at' => null,
        ]);
        $this->appointment(['user_id' => null, 'email' => 'unverified@example.test'], 'Hidden Doctor');

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertDontSee('Hidden Doctor');
    }

    private function patient(bool $verified, string $email = 'patient@example.test'): User
    {
        return User::factory()->create([
            'role' => 'patient',
            'email' => $email,
            'email_verified_at' => $verified ? now() : null,
        ]);
    }

    private function appointment(array $overrides, string $doctorName = 'Profile Doctor'): Appointment
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => $doctorName,
            'slug' => 'profile-doctor-'.uniqid(),
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        $slot = TimeSlot::create(['time' => '10:00 AM', 'status' => 1]);

        return Appointment::create(array_merge([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Profile Patient',
            'email' => 'patient@example.test',
            'phone' => '555-0100',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'status' => 0,
        ], $overrides));
    }
}
