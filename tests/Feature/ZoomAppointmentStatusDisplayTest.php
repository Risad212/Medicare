<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use App\Modules\Zoom\Models\ZoomMeeting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Rendering contract for the Zoom column on the doctor appointments list.
 * (Model/service level sync_status lifecycle is covered by ZoomTelehealthTest.)
 */
class ZoomAppointmentStatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_telehealth_appointment_without_a_meeting_row_renders_pending_state(): void
    {
        Queue::fake();

        [$user, $doctor] = $this->doctor();
        $slot = TimeSlot::create(['time' => '01:00 PM', 'status' => 1]);
        $this->appointment($doctor, $slot, 1, now()->addDay()->toDateString(), 'telehealth');

        $this->actingAs($user)
            ->get(route('doctor.appointments'))
            ->assertOk()
            ->assertSee('Meeting pending');
    }

    public function test_ready_meeting_renders_a_host_link(): void
    {
        Queue::fake();

        [$user, $doctor] = $this->doctor();
        $slot = TimeSlot::create(['time' => '02:00 PM', 'status' => 1]);
        $appointment = $this->appointment($doctor, $slot, 1, now()->addDays(2)->toDateString(), 'telehealth');

        ZoomMeeting::create([
            'appointment_id' => $appointment->id,
            'sync_status' => 'ready',
        ]);

        $this->actingAs($user)
            ->get(route('doctor.appointments'))
            ->assertOk()
            ->assertSee('Start Zoom');
    }

    public function test_failed_meeting_renders_an_error_and_a_retry_button(): void
    {
        Queue::fake();

        [$user, $doctor] = $this->doctor();
        $slot = TimeSlot::create(['time' => '03:00 PM', 'status' => 1]);
        $appointment = $this->appointment($doctor, $slot, 1, now()->addDays(3)->toDateString(), 'telehealth');

        ZoomMeeting::create([
            'appointment_id' => $appointment->id,
            'sync_status' => 'failed',
            'last_error_code' => 'zoom_auth_failed',
        ]);

        $this->actingAs($user)
            ->get(route('doctor.appointments'))
            ->assertOk()
            ->assertSee('Zoom failed')
            ->assertSee(route('doctor.telehealth.retry', $appointment), false);
    }

    public function test_in_person_appointment_skips_the_zoom_column_content(): void
    {
        Queue::fake();

        [$user, $doctor] = $this->doctor();
        $slot = TimeSlot::create(['time' => '04:00 PM', 'status' => 1]);
        $this->appointment($doctor, $slot, 1, now()->addDays(4)->toDateString(), 'in_person');

        $this->actingAs($user)
            ->get(route('doctor.appointments'))
            ->assertOk()
            ->assertSee('In-person');
    }

    /**
     * @return array{0: User, 1: Doctor}
     */
    private function doctor(): array
    {
        $user = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Dr Zoom',
            'slug' => 'dr-zoom-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);

        return [$user, $doctor];
    }

    private function appointment(Doctor $doctor, TimeSlot $slot, int $status, string $date, string $mode): Appointment
    {
        return Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Zoom Patient',
            'email' => 'zoom-'.uniqid().'@example.test',
            'phone' => '555-0100',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => $mode,
            'appointment_date' => $date,
            'status' => $status,
        ]);
    }
}
