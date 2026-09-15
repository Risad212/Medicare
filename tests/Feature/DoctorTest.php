<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class DoctorTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_dashboard_returns_404_without_doctor_profile(): void
    {
        $this->actingAs($this->makeUser(['role' => 'doctor']))
            ->get(route('doctor.dashboard'))
            ->assertNotFound();
    }

    public function test_dashboard_loads_for_doctor_with_profile(): void
    {
        $doctor = $this->makeDoctor(['name' => 'Dr. Sample']);
        $doctor->user->update(['name' => 'Dr. Sample']);

        $this->actingAs($doctor->user)
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee('Dr. Sample');
    }

    public function test_appointments_page_lists_only_own_appointments(): void
    {
        $doctor = $this->makeDoctor();
        $other = $this->makeDoctor();
        $slot = $this->makeTimeSlot();
        $ownApt = $this->makeAppointment(['doctor_id' => $doctor->id, 'time_slot_id' => $slot->id, 'patient_name' => 'My Patient']);
        $this->makeAppointment(['doctor_id' => $other->id, 'patient_name' => 'Their Patient']);

        $this->actingAs($doctor->user)
            ->from(route('doctor.dashboard'))
            ->get(route('doctor.appointments'))
            ->assertOk()
            ->assertSee('My Patient')
            ->assertDontSee('Their Patient');
    }

    public function test_doctor_updates_appointment_status(): void
    {
        $doctor = $this->makeDoctor();
        $appointment = $this->makeAppointment(['doctor_id' => $doctor->id]);

        $this->actingAs($doctor->user)
            ->from(route('doctor.appointments'))
            ->put(route('doctor.appointments.update', $appointment->id), ['status' => 2])
            ->assertRedirect()
            ->assertSessionHas('success', 'Appointment status updated!');

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 2]);
    }

    public function test_doctor_cancel_status_nullifies_token(): void
    {
        $doctor = $this->makeDoctor();
        $appointment = $this->makeAppointment(['doctor_id' => $doctor->id]);

        $this->actingAs($doctor->user)
            ->put(route('doctor.appointments.update', $appointment->id), ['status' => 3])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 3,
            'cancellation_token' => null,
        ]);
    }

    public function test_doctor_cannot_update_another_doctors_appointment(): void
    {
        $ownerDoctor = $this->makeDoctor();
        $intruder = $this->makeDoctor();
        $appointment = $this->makeAppointment(['doctor_id' => $ownerDoctor->id]);

        $this->actingAs($intruder->user)
            ->put(route('doctor.appointments.update', $appointment->id), ['status' => 2])
            ->assertForbidden();

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 0]);
    }

    public function test_doctor_cannot_set_pending_status(): void
    {
        $doctor = $this->makeDoctor();
        $appointment = $this->makeAppointment(['doctor_id' => $doctor->id]);

        $this->actingAs($doctor->user)
            ->put(route('doctor.appointments.update', $appointment->id), ['status' => 0])
            ->assertSessionHasErrors('status');
    }

    public function test_doctor_cannot_update_missing_appointment(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($doctor->user)
            ->put(route('doctor.appointments.update', 999999), ['status' => 2])
            ->assertNotFound();
    }

    public function test_doctor_reactivation_rejects_conflicting_booking(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();
        $date = now()->addDay()->toDateString();

        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
            'email' => 'other@example.com',
        ]);
        $target = $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
            'email' => 'target@example.com',
            'status' => 3,
        ]);

        // Must return a validation error, never a 500 from the unique index.
        $this->actingAs($doctor->user)
            ->put(route('doctor.appointments.update', $target->id), ['status' => 1])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('appointments', ['id' => $target->id, 'status' => 3]);
    }

    public function test_doctor_profile_page_loads_and_updates(): void
    {
        Storage::fake('public');
        $doctor = $this->makeDoctor();

        $this->actingAs($doctor->user)
            ->get(route('doctor.profile.edit'))
            ->assertOk();

        $this->actingAs($doctor->user)
            ->from(route('doctor.profile.edit'))
            ->put(route('doctor.profile.update'), [
                'name' => 'Dr. Updated',
                'phone' => '01711-888888',
                'degree' => 'MBBS',
                'specialist' => 'Radiologist',
                'image' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Profile updated successfully!');

        $this->assertDatabaseHas('users', ['id' => $doctor->user_id, 'name' => 'Dr. Updated']);
        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
            'phone' => '01711-888888',
            'specialist' => 'Radiologist',
        ]);
    }

    public function test_doctor_profile_update_validates_name(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($doctor->user)
            ->put(route('doctor.profile.update'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }
}
