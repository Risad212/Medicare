<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentPickerTest extends TestCase
{
    use RefreshDatabase;

    public function test_picker_returns_only_this_doctors_appointments(): void
    {
        [$user, $doctor] = $this->doctor();

        $mine = $this->appointment($doctor, TimeSlot::create(['time' => '9:00 AM', 'status' => 1]), 'Alice Picker');
        $this->appointment($doctor, TimeSlot::create(['time' => '9:15 AM', 'status' => 1]), 'Bob Other');

        $this->actingAs($user)
            ->getJson(route('doctor.appointments.picker', ['q' => 'Alice']))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonPath('results.0.id', $mine->id);
    }

    public function test_picker_excludes_another_doctors_appointments(): void
    {
        [, $doctor] = $this->doctor();
        [$otherUser, $otherDoctor] = $this->doctor();
        $slot = TimeSlot::create(['time' => '9:30 AM', 'status' => 1]);

        $this->appointment($otherDoctor, $slot, 'Foreign Patient');

        $this->actingAs($doctor->user)
            ->getJson(route('doctor.appointments.picker', ['q' => 'Foreign']))
            ->assertOk()
            ->assertJsonCount(0, 'results');
    }

    public function test_picker_requires_a_doctor_account(): void
    {
        $patient = User::factory()->create(['role' => 'patient']);

        $this->actingAs($patient)
            ->getJson(route('doctor.appointments.picker'))
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Doctor}
     */
    private function doctor(): array
    {
        $user = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr Picker',
            'slug' => 'dr-picker-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);

        return [$user, $doctor];
    }

    private function appointment(Doctor $doctor, TimeSlot $slot, string $name): Appointment
    {
        return Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => $name,
            'email' => 'picker-'.uniqid().'@example.test',
            'phone' => '555-0100',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'status' => 1,
        ]);
    }
}
