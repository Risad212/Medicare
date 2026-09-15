<?php

namespace Tests\Feature;

use App\Models\DoctorOffDay;
use App\Models\DoctorSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class DoctorAvailabilityTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    private function futureDate(): string
    {
        return now()->addDays(10)->toDateString();
    }

    public function test_unconfigured_doctor_keeps_all_slots_open(): void
    {
        $doctor = $this->makeDoctor();
        $this->makeTimeSlot();
        $this->makeTimeSlot(['time' => '11:00 AM']);

        $response = $this->getJson(route('get.slots', [
            'doctor_id' => $doctor->id,
            'date' => $this->futureDate(),
        ]))->assertOk();

        $this->assertCount(2, $response->json('slots'));
        $this->assertSame([], $response->json('unavailableSlotIds'));
    }

    public function test_get_slots_marks_slots_closed_outside_schedule(): void
    {
        $doctor = $this->makeDoctor();
        $openSlot = $this->makeTimeSlot();
        $closedSlot = $this->makeTimeSlot(['time' => '11:00 AM']);
        $date = $this->futureDate();

        DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'weekday' => Carbon::parse($date)->dayOfWeek,
            'time_slot_id' => $openSlot->id,
        ]);

        $response = $this->getJson(route('get.slots', [
            'doctor_id' => $doctor->id,
            'date' => $date,
        ]))->assertOk();

        $this->assertSame([], $response->json('bookedSlotIds'));
        $this->assertSame([$closedSlot->id], $response->json('unavailableSlotIds'));
    }

    public function test_get_slots_returns_all_closed_on_off_day(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();
        $date = $this->futureDate();

        DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'weekday' => Carbon::parse($date)->dayOfWeek,
            'time_slot_id' => $slot->id,
        ]);
        DoctorOffDay::create(['doctor_id' => $doctor->id, 'date' => $date]);

        $response = $this->getJson(route('get.slots', [
            'doctor_id' => $doctor->id,
            'date' => $date,
        ]))->assertOk();

        $this->assertSame([$slot->id], $response->json('unavailableSlotIds'));
    }

    public function test_store_rejects_slot_outside_schedule(): void
    {
        $doctor = $this->makeDoctor();
        $openSlot = $this->makeTimeSlot();
        $closedSlot = $this->makeTimeSlot(['time' => '11:00 AM']);
        $date = $this->futureDate();

        DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'weekday' => Carbon::parse($date)->dayOfWeek,
            'time_slot_id' => $openSlot->id,
        ]);

        $this->post(route('appointment.store'), $this->appointmentPayload([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $closedSlot->id,
            'appointment_date' => $date,
        ]))->assertSessionHasErrors('time_slot_id');
    }

    public function test_store_accepts_slot_open_in_schedule(): void
    {
        $doctor = $this->makeDoctor();
        $openSlot = $this->makeTimeSlot();
        $date = $this->futureDate();

        DoctorSchedule::create([
            'doctor_id' => $doctor->id,
            'weekday' => Carbon::parse($date)->dayOfWeek,
            'time_slot_id' => $openSlot->id,
        ]);

        $this->post(route('appointment.store'), $this->appointmentPayload([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $openSlot->id,
            'appointment_date' => $date,
        ]))->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor->id,
            'time_slot_id' => $openSlot->id,
            'appointment_date' => $date,
        ]);
    }

    public function test_admin_can_save_weekly_schedule_matrix(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();
        $weekday = 5;

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.doctors.availability.update', $doctor->id), [
                'schedules' => [$weekday => [$slot->id]],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('doctor_schedules', [
            'doctor_id' => $doctor->id,
            'weekday' => $weekday,
            'time_slot_id' => $slot->id,
        ]);
    }

    public function test_admin_can_add_and_remove_off_day(): void
    {
        $doctor = $this->makeDoctor();
        $date = $this->futureDate();

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.doctors.off-days.store', $doctor->id), [
                'date' => $date,
                'reason' => 'Public holiday',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $offDay = DoctorOffDay::where('doctor_id', $doctor->id)->firstOrFail();
        $this->assertSame('Public holiday', $offDay->reason);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.doctor-off-days.destroy', $offDay->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('doctor_off_days', ['id' => $offDay->id]);
    }
}
