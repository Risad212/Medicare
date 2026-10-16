<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DoctorDetailBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_form_shows_consultation_mode_when_zoom_on(): void
    {
        config()->set('modules.zoom', true);
        $doctor = $this->makeDoctor();

        $this->get(route('doctor.show', $doctor->id))
            ->assertOk()
            ->assertSee('name="consultation_mode"', false)
            ->assertSee('type="tel"', false);
    }

    public function test_detail_form_hides_consultation_mode_when_zoom_off(): void
    {
        config()->set('modules.zoom', false);
        $doctor = $this->makeDoctor();

        $this->get(route('doctor.show', $doctor->id))
            ->assertOk()
            ->assertDontSee('name="consultation_mode"', false);
    }

    public function test_in_person_books_without_email_when_zoom_on(): void
    {
        config()->set('modules.zoom', true);
        Mail::fake();
        [$doctor, $slot] = $this->makeDoctorWithSlot();

        $this->post(route('appointment.store'), $this->booking($doctor, $slot, [
            'consultation_mode' => 'in_person',
        ]))->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor->id,
            'consultation_mode' => 'in_person',
        ]);
    }

    public function test_telehealth_requires_email(): void
    {
        config()->set('modules.zoom', true);
        Mail::fake();
        [$doctor, $slot] = $this->makeDoctorWithSlot();

        $this->post(route('appointment.store'), $this->booking($doctor, $slot, [
            'consultation_mode' => 'telehealth',
        ]))->assertSessionHasErrors('email');

        $this->post(route('appointment.store'), $this->booking($doctor, $slot, [
            'consultation_mode' => 'telehealth',
            'email' => 'patient@example.test',
        ]))->assertSessionHas('success');
    }

    public function test_intl_phone_number_submits_intact(): void
    {
        config()->set('modules.zoom', false);
        Mail::fake();
        [$doctor, $slot] = $this->makeDoctorWithSlot();

        $this->post(route('appointment.store'), $this->booking($doctor, $slot, [
            'phone' => '+880 1712345678',
        ]))->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', ['phone' => '+880 1712345678']);
    }

    public function test_inactive_doctor_reports_all_slots_unavailable(): void
    {
        config()->set('modules.zoom', false);
        $doctor = $this->makeDoctor();
        $doctor->update(['status' => 0]);
        $slot = TimeSlot::create(['time' => '09:00 AM', 'status' => 1]);

        $this->getJson(route('get.slots', [
            'doctor_id' => $doctor->id,
            'date' => now()->addDays(2)->toDateString(),
        ]))
            ->assertOk()
            ->assertJsonPath('unavailableSlotIds', [$slot->id])
            ->assertJsonPath('bookedSlotIds', []);

        $this->post(route('appointment.store'), $this->booking($doctor, $slot, []))
            ->assertNotFound();
    }

    private function makeDoctor(): Doctor
    {
        $user = User::factory()->create(['role' => 'doctor']);

        return Doctor::create([
            'name' => 'Detail Doctor',
            'slug' => 'detail-doctor-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);
    }

    private function makeDoctorWithSlot(): array
    {
        return [$this->makeDoctor(), TimeSlot::create(['time' => '09:00 AM', 'status' => 1])];
    }

    private function booking(Doctor $doctor, TimeSlot $slot, array $overrides): array
    {
        return array_merge([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Detail Patient',
            'phone' => '+1 555 0100',
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'time_slot_id' => $slot->id,
            'gender' => 1,
        ], $overrides);
    }
}
