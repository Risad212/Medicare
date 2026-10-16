<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_exclude_cancelled_appointments(): void
    {
        [$user, $doctor] = $this->doctor();

        foreach ([0, 1, 2, 3] as $status) {
            $slot = TimeSlot::create(['time' => '10:0'.$status.' AM', 'status' => 1]);
            $this->appointment($doctor, $slot, $status, today()->toDateString());
        }

        $this->actingAs($user)
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertViewHas('todayAppointments', 3)
            ->assertViewHas('totalAppointments', 3);
    }

    public function test_dashboard_shows_a_cancelled_pill_for_cancelled_rows(): void
    {
        [$user, $doctor] = $this->doctor();
        $slot = TimeSlot::create(['time' => '11:00 AM', 'status' => 1]);
        $this->appointment($doctor, $slot, 3, today()->toDateString());

        $this->actingAs($user)
            ->get(route('doctor.dashboard'))
            ->assertOk()
            ->assertSee('p-cancelled', false)
            ->assertSee('Cancelled');
    }

    public function test_doctor_profile_returns_not_found_without_a_doctor_record(): void
    {
        $user = User::factory()->create(['role' => 'doctor']);

        $this->actingAs($user)
            ->get(route('doctor.profile.edit'))
            ->assertNotFound();
    }

    public function test_doctor_can_save_services_and_availability(): void
    {
        [$user, $doctor] = $this->doctor();

        $this->actingAs($user)
            ->put(route('doctor.profile.update'), [
                'name' => 'Dr Updated',
                'services' => 'General consultation, ECG',
                'availability' => 'Mon-Fri 9-17',
            ])
            ->assertRedirect();

        $this->assertSame('General consultation, ECG', $doctor->fresh()->services);
        $this->assertSame('Mon-Fri 9-17', $doctor->fresh()->availability);
    }

    public function test_doctor_appointments_are_paginated(): void
    {
        [$user, $doctor] = $this->doctor();
        $slot = TimeSlot::create(['time' => '12:00 PM', 'status' => 1]);

        for ($i = 0; $i < 11; $i++) {
            $this->appointment($doctor, $slot, 0, now()->addDays($i + 1)->toDateString());
        }

        $this->actingAs($user)
            ->get(route('doctor.appointments'))
            ->assertOk()
            ->assertSee('page=2');
    }

    /**
     * @return array{0: User, 1: Doctor}
     */
    private function doctor(): array
    {
        $user = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr Dashboard',
            'slug' => 'dr-dashboard-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);

        return [$user, $doctor];
    }

    private function appointment(Doctor $doctor, TimeSlot $slot, int $status, string $date): Appointment
    {
        return Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Dashboard Patient',
            'email' => 'dash-'.uniqid().'@example.test',
            'phone' => '555-0100',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => $date,
            'status' => $status,
        ]);
    }
}
