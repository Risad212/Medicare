<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTimelineTest extends TestCase
{
    use RefreshDatabase;

    private function patient(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => 'patient']);
    }

    private function doctor(): Doctor
    {
        $user = User::factory()->create(['role' => 'doctor']);

        return Doctor::create([
            'name' => 'Dr. Timeline',
            'slug' => 'dr-timeline-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);
    }

    private function appointment(User $patient, int $status = 2): Appointment
    {
        return Appointment::create([
            'doctor_id' => $this->doctor()->id,
            'time_slot_id' => TimeSlot::create(['time' => '10:00 AM', 'status' => 1])->id,
            'user_id' => $patient->id,
            'patient_name' => $patient->name,
            'email' => $patient->email,
            'phone' => '+8801711111111',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->subDays(3)->toDateString(),
            'status' => $status,
        ]);
    }

    public function test_patient_can_save_chronic_conditions(): void
    {
        $patient = $this->patient();

        $this->actingAs($patient)
            ->put(route('profile.update'), [
                'name' => $patient->name,
                'email' => $patient->email,
                'chronic_conditions' => "Type 2 diabetes\nHypertension",
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $patient->id,
            'chronic_conditions' => "Type 2 diabetes\nHypertension",
        ]);
    }

    public function test_chronic_conditions_over_max_length_is_rejected(): void
    {
        $patient = $this->patient();

        $this->actingAs($patient)
            ->from(route('profile'))
            ->put(route('profile.update'), [
                'name' => $patient->name,
                'email' => $patient->email,
                'chronic_conditions' => str_repeat('a', 2001),
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHasErrors('chronic_conditions');

        $this->assertDatabaseMissing('users', [
            'id' => $patient->id,
            'chronic_conditions' => str_repeat('a', 2001),
        ]);
    }

    public function test_profile_renders_medical_timeline_with_conditions_and_visits(): void
    {
        $patient = $this->patient([
            'allergies' => 'Penicillin, Peanuts',
            'chronic_conditions' => 'Asthma',
        ]);
        $visit = $this->appointment($patient, 2);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee(__('messages.public.timeline_title'), false)
            ->assertSee(__('messages.public.allergies_title'), false)
            ->assertSee('Penicillin', false)
            ->assertSee(__('messages.public.chronic_title'), false)
            ->assertSee('Asthma', false)
            ->assertSee(__('messages.public.visit_history'), false)
            ->assertSee('Dr. Timeline', false)
            ->assertSee(Carbon::parse($visit->appointment_date)->format('d M Y'), false);
    }

    public function test_timeline_shows_localized_empty_state_without_visits(): void
    {
        $patient = $this->patient();

        $this->actingAs($patient)
            ->withSession(['locale' => 'es'])
            ->get(route('profile'))
            ->assertOk()
            ->assertSee(__('messages.public.chronic_none'), false)
            ->assertSee(__('messages.public.no_visits'), false);
    }

    public function test_guest_cannot_view_the_medical_timeline(): void
    {
        $this->get(route('profile'))->assertRedirect(route('login'));
    }
}
