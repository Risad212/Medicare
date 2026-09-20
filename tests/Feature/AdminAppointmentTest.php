<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AdminAppointmentTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    private function actingAsAdmin()
    {
        return $this->actingAs($this->makeAdmin());
    }

    private function adminAppointmentPayload(array $overrides = []): array
    {
        $doctor = $overrides['doctor_id'] ?? $this->makeDoctor()->id;
        $slot = $overrides['time_slot_id'] ?? $this->makeTimeSlot()->id;

        return array_merge([
            'doctor_id' => $doctor,
            'time_slot_id' => $slot,
            'name' => 'John Doe',
            'age' => 30,
            'gender' => 1,
            'phone' => '01711-223344',
            'email' => 'john@example.com',
            'visit_type' => 2,
            'date' => now()->addDay()->toDateString(),
        ], $overrides);
    }

    public function test_index_and_create_pages_load(): void
    {
        $this->actingAsAdmin()->get(route('admin.appointments.index'))->assertOk();
        $this->actingAsAdmin()->get(route('admin.appointments.create'))->assertOk();
    }

    public function test_admin_creates_appointment(): void
    {
        $payload = $this->adminAppointmentPayload();

        $this->actingAsAdmin()
            ->post(route('admin.appointments.store'), $payload)
            ->assertRedirect(route('admin.appointments.index'))
            ->assertSessionHas('success', 'Appointment created successfully!');

        $this->assertDatabaseHas('appointments', [
            'patient_name' => 'John Doe',
            'status' => 0,
            'appointment_date' => $payload['date'],
        ]);
    }

    public function test_admin_appointment_store_validates_fields(): void
    {
        $this->actingAsAdmin()
            ->from(route('admin.appointments.create'))
            ->post(route('admin.appointments.store'), [])
            ->assertSessionHasErrors([
                'doctor_id', 'time_slot_id', 'name', 'gender', 'phone', 'visit_type', 'date',
            ]);
    }

    public function test_admin_appointment_store_rejects_double_booking(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();
        $date = now()->addDay()->toDateString();

        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
        ]);

        $this->actingAsAdmin()
            ->from(route('admin.appointments.create'))
            ->post(route('admin.appointments.store'), $this->adminAppointmentPayload([
                'doctor_id' => $doctor->id,
                'time_slot_id' => $slot->id,
                'date' => $date,
            ]))
            ->assertSessionHasErrors('doctor_id');
    }

    public function test_admin_updates_appointment(): void
    {
        $appointment = $this->makeAppointment();
        $payload = $this->adminAppointmentPayload(['name' => 'Updated Name', 'status' => 1]);

        $this->actingAsAdmin()
            ->put(route('admin.appointments.update', $appointment->id), $payload)
            ->assertRedirect(route('admin.appointments.index'))
            ->assertSessionHas('success', 'Appointment updated successfully!');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'patient_name' => 'Updated Name',
            'status' => 1,
        ]);
    }

    public function test_admin_update_rejects_invalid_status(): void
    {
        $appointment = $this->makeAppointment();

        $this->actingAsAdmin()
            ->put(route('admin.appointments.update', $appointment->id), $this->adminAppointmentPayload(['status' => 5]))
            ->assertSessionHasErrors('status');
    }

    public function test_admin_update_rejects_conflicting_booking(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();
        $date = now()->addDay()->toDateString();
        // Active booking on the slot (DB-legal on its own)...
        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
            'email' => 'other@example.com',
        ]);
        // ...plus a cancelled booking on the same slot that is being
        // re-activated through the update form.
        $target = $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
            'email' => 'target@example.com',
            'status' => 3,
        ]);

        $this->actingAsAdmin()
            ->from(route('admin.appointments.edit', $target->id))
            ->put(route('admin.appointments.update', $target->id), $this->adminAppointmentPayload([
                'doctor_id' => $doctor->id,
                'time_slot_id' => $slot->id,
                'date' => $date,
                'status' => 1,
            ]))
            ->assertSessionHasErrors('doctor_id');
    }

    public function test_admin_update_to_cancelled_skips_conflict_and_nullifies_token(): void
    {
        $doctor = $this->makeDoctor();
        $slotA = $this->makeTimeSlot();
        $slotB = $this->makeTimeSlot();
        $date = now()->addDay()->toDateString();
        $target = $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slotA->id,
            'appointment_date' => $date,
        ]);
        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slotB->id,
            'appointment_date' => $date,
            'email' => 'other@example.com',
        ]);

        $this->actingAsAdmin()
            ->put(route('admin.appointments.update', $target->id), $this->adminAppointmentPayload([
                'doctor_id' => $doctor->id,
                'date' => $date,
                'status' => 3,
            ]))
            ->assertRedirect(route('admin.appointments.index'));

        $this->assertDatabaseHas('appointments', [
            'id' => $target->id,
            'status' => 3,
            'cancellation_token' => null,
        ]);
    }

    public function test_admin_quick_status_update(): void
    {
        $appointment = $this->makeAppointment();

        $this->actingAsAdmin()
            ->from(route('admin.home'))
            ->patch(route('admin.appointments.status', $appointment->id), ['status' => 2])
            ->assertRedirect()
            ->assertSessionHas('success', 'Appointment status updated.');

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 2]);
    }

    public function test_admin_quick_status_update_to_cancelled_nullifies_token(): void
    {
        $appointment = $this->makeAppointment();

        $this->actingAsAdmin()
            ->patch(route('admin.appointments.status', $appointment->id), ['status' => 3])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 3,
            'cancellation_token' => null,
        ]);
    }

    public function test_admin_quick_status_reactivation_rejects_conflicting_booking(): void
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
        $this->actingAsAdmin()
            ->from(route('admin.home'))
            ->patch(route('admin.appointments.status', $target->id), ['status' => 1])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('appointments', ['id' => $target->id, 'status' => 3]);
    }

    public function test_admin_quick_status_update_rejects_invalid_status(): void
    {
        $appointment = $this->makeAppointment();

        $this->actingAsAdmin()
            ->patch(route('admin.appointments.status', $appointment->id), ['status' => 9])
            ->assertSessionHasErrors('status');
    }

    public function test_admin_deletes_appointment(): void
    {
        $appointment = $this->makeAppointment();

        $this->actingAsAdmin()
            ->from(route('admin.appointments.index'))
            ->delete(route('admin.appointments.destroy', $appointment->id))
            ->assertRedirect()
            ->assertSessionHas('success', 'Appointment deleted successfully!');

        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }

    public function test_admin_index_searches_by_patient_name(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();

        $this->makeAppointment(['doctor_id' => $doctor->id, 'time_slot_id' => $slot->id, 'patient_name' => 'Zed Pineapple']);
        $this->makeAppointment(['patient_name' => 'Ann Apple']);

        $this->actingAsAdmin()
            ->get(route('admin.appointments.index', ['search' => 'Pineapple']))
            ->assertOk()
            ->assertSee('Zed Pineapple')
            ->assertDontSee('Ann Apple');
    }
}
