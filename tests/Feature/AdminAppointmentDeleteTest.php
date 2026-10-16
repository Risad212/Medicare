<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAppointmentDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_appointment_is_cancelled_not_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $appointment = $this->appointment(status: 1);

        $this->actingAs($admin)
            ->delete(route('admin.appointments.destroy', $appointment->id))
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 3,
        ]);
    }

    public function test_pending_appointment_is_cancelled_not_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $appointment = $this->appointment(status: 0);

        $this->actingAs($admin)
            ->delete(route('admin.appointments.destroy', $appointment->id))
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 3,
        ]);
    }

    public function test_completed_appointment_can_be_hard_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $appointment = $this->appointment(status: 2);

        $this->actingAs($admin)
            ->delete(route('admin.appointments.destroy', $appointment->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }

    private function appointment(int $status): Appointment
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Admin Delete Doctor',
            'slug' => 'admin-delete-doctor-'.uniqid(),
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        $slot = TimeSlot::create(['time' => '01:00 PM', 'status' => 1]);

        return Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Delete Patient',
            'email' => 'delete@example.test',
            'phone' => '555-0100',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'status' => $status,
        ]);
    }
}
