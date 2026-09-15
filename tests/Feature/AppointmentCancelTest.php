<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AppointmentCancelTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_guest_can_view_cancel_page_with_valid_token(): void
    {
        $appointment = $this->makeAppointment();

        $this->get(route('appointment.cancel-page', $appointment->cancellation_token))
            ->assertOk()
            ->assertSee($appointment->patient_name);
    }

    public function test_guest_cancel_page_404s_for_unknown_token(): void
    {
        $this->get(route('appointment.cancel-page', 'unknown-token'))->assertNotFound();
    }

    public function test_guest_cancel_page_410s_when_link_expired(): void
    {
        $appointment = $this->makeAppointment([
            'appointment_date' => now()->subDays(3)->toDateString(),
        ]);

        $this->get(route('appointment.cancel-page', $appointment->cancellation_token))
            ->assertStatus(410);
    }

    public function test_guest_cancel_page_410s_when_appointment_no_longer_cancellable(): void
    {
        $appointment = $this->makeAppointment(['status' => 2]);

        $this->get(route('appointment.cancel-page', $appointment->cancellation_token))
            ->assertStatus(410);
    }

    public function test_guest_cancels_appointment_via_token(): void
    {
        $appointment = $this->makeAppointment();

        $this->put(route('appointment.cancel-by-token', $appointment->cancellation_token))
            ->assertRedirect(route('appointment'))
            ->assertSessionHas('success', 'Your appointment has been cancelled.');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 3,
            'cancellation_token' => null,
        ]);
    }

    public function test_guest_cancel_by_token_fails_when_expired(): void
    {
        $appointment = $this->makeAppointment([
            'appointment_date' => now()->subDays(3)->toDateString(),
        ]);

        $this->from('/')->put(route('appointment.cancel-by-token', $appointment->cancellation_token))
            ->assertRedirect(route('appointment'))
            ->assertSessionHas('error', 'Cancellation link has expired.');
    }

    public function test_guest_cancel_by_token_fails_when_not_cancellable(): void
    {
        $appointment = $this->makeAppointment(['status' => 2]);

        $this->put(route('appointment.cancel-by-token', $appointment->cancellation_token))
            ->assertRedirect(route('appointment'))
            ->assertSessionHas('error', 'This appointment can no longer be cancelled.');
    }

    public function test_logged_in_patient_cancels_own_appointment(): void
    {
        $patient = $this->makeUser();
        $appointment = $this->makeAppointment(['user_id' => $patient->id]);

        $this->actingAs($patient)
            ->from('/profile')
            ->patch(route('appointment.cancel', $appointment->id))
            ->assertRedirect()
            ->assertSessionHas('success', 'Appointment cancelled successfully.');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 3,
            'cancellation_token' => null,
        ]);
    }

    public function test_cannot_cancel_someone_elses_appointment(): void
    {
        $other = $this->makeUser();
        $appointment = $this->makeAppointment(['user_id' => $other->id]);

        $this->actingAs($this->makeUser())
            ->patch(route('appointment.cancel', $appointment->id))
            ->assertForbidden();
    }

    public function test_cannot_cancel_completed_or_cancelled_appointment_via_patch(): void
    {
        $patient = $this->makeUser();
        $appointment = $this->makeAppointment(['user_id' => $patient->id, 'status' => 2]);

        $this->actingAs($patient)
            ->from('/profile')
            ->patch(route('appointment.cancel', $appointment->id))
            ->assertSessionHas('error', 'This appointment can no longer be cancelled.');

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 2]);
    }
}
