<?php

namespace Tests\Feature;

use App\Models\Appointment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminAppointmentSlotTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function updatePayload(Appointment $appointment, array $overrides = []): array
    {
        return array_merge([
            'doctor_id' => $appointment->doctor_id,
            'name' => $appointment->patient_name,
            'age' => $appointment->age,
            'gender' => $appointment->gender,
            'phone' => $appointment->phone,
            'email' => $appointment->email,
            'visit_type' => $appointment->visit_type,
            'date' => $appointment->appointment_date->toDateString(),
            'status' => $appointment->status,
        ], $overrides);
    }

    public function test_admin_can_move_appointment_to_free_slot(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment(['status' => 0]);
        $newSlot = $this->makeTimeSlot(['time' => '11:00 AM']);

        $this->actingAs($this->makeAdmin())
            ->put(
                route('admin.appointments.update', $appointment->id),
                $this->updatePayload($appointment, ['time_slot_id' => $newSlot->id, 'status' => 1])
            )
            ->assertRedirect(route('admin.appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'time_slot_id' => $newSlot->id,
            'status' => 1,
        ]);
    }

    public function test_admin_cannot_move_appointment_to_booked_slot(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment(['status' => 0]);
        $newSlot = $this->makeTimeSlot(['time' => '11:00 AM']);
        $this->makeAppointment([
            'doctor_id' => $appointment->doctor_id,
            'appointment_date' => $appointment->appointment_date->toDateString(),
            'time_slot_id' => $newSlot->id,
            'status' => 1,
        ]);

        $this->actingAs($this->makeAdmin())
            ->put(
                route('admin.appointments.update', $appointment->id),
                $this->updatePayload($appointment, ['time_slot_id' => $newSlot->id])
            )
            ->assertSessionHasErrors('doctor_id');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'time_slot_id' => $appointment->time_slot_id,
        ]);
    }
}
