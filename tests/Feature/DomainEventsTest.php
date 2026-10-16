<?php

namespace Tests\Feature;

use App\Events\AppointmentBooked;
use App\Events\AppointmentCancelled;
use App\Events\LabOrderCompleted;
use App\Events\PrescriptionCreated;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use App\Modules\Lab\Models\LabOrder;
use App\Modules\Lab\Models\LabTest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_dispatches_appointment_booked(): void
    {
        config()->set('modules.zoom', false);
        Mail::fake();
        Event::fake([AppointmentBooked::class]);
        [$doctor, $slot] = $this->doctorWithSlot();

        $this->post(route('appointment.store'), [
            'doctor_id' => $doctor->id,
            'patient_name' => 'Event Patient',
            'phone' => '+1 555 0100',
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'time_slot_id' => $slot->id,
            'gender' => 1,
        ])->assertSessionHas('success');

        Event::assertDispatched(AppointmentBooked::class);
    }

    public function test_patient_cancel_dispatches_appointment_cancelled(): void
    {
        Event::fake([AppointmentCancelled::class]);
        $patient = User::factory()->create(['role' => 'patient']);
        [, $slot] = $this->doctorWithSlot();
        $appointment = $this->appointment($slot, $patient->id);

        $this->actingAs($patient)
            ->patch(route('appointment.cancel', $appointment->id))
            ->assertRedirect();

        Event::assertDispatched(AppointmentCancelled::class);
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 3]);
    }

    public function test_doctor_destroy_blocked_by_lab_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Lab Doctor', 'slug' => 'lab-doctor-'.uniqid(),
            'user_id' => $doctorUser->id, 'status' => 1,
        ]);
        LabOrder::create([
            'doctor_id' => $doctor->id, 'patient_name' => 'Lab Patient',
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.doctors.destroy', $doctor->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('doctors', ['id' => $doctor->id]);
    }

    public function test_doctor_destroy_allowed_without_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Clean Doctor', 'slug' => 'clean-doctor-'.uniqid(),
            'user_id' => $doctorUser->id, 'status' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.doctors.destroy', $doctor->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('doctors', ['id' => $doctor->id]);
    }

    public function test_lab_completion_dispatches_lab_order_completed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Event::fake([LabOrderCompleted::class]);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Order Doctor', 'slug' => 'order-doctor-'.uniqid(),
            'user_id' => $doctorUser->id, 'status' => 1,
        ]);
        $order = LabOrder::create([
            'doctor_id' => $doctor->id, 'patient_name' => 'Order Patient',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.lab-orders.status', $order->id), ['status' => 'completed'])
            ->assertRedirect();

        Event::assertDispatched(LabOrderCompleted::class, fn ($e) => $e->labOrderId === $order->id);
    }

    public function test_prescription_store_dispatches_prescription_created(): void
    {
        Event::fake([PrescriptionCreated::class]);
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        Doctor::create([
            'name' => 'Rx Doctor', 'slug' => 'rx-doctor-'.uniqid(),
            'user_id' => $doctorUser->id, 'status' => 1,
        ]);

        $this->actingAs($doctorUser)
            ->post(route('doctor.prescriptions.store'), [
                'patient_name' => 'Rx Patient',
                'phone' => '+1 555 0100',
                'diagnosis' => 'Flu',
                'items' => [['medicine_name' => 'Paracetamol']],
            ])
            ->assertRedirect();

        Event::assertDispatched(PrescriptionCreated::class);
        $this->assertDatabaseHas('prescriptions', ['patient_name' => 'Rx Patient']);
    }

    public function test_module_provider_registers_activity_observer(): void
    {
        LabTest::create(['name' => 'CBC', 'price' => 100]);

        $this->assertDatabaseHas('activity_logs', ['action' => 'labtest.created']);
    }

    private function doctorWithSlot(): array
    {
        $user = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Event Doctor', 'slug' => 'event-doctor-'.uniqid(),
            'user_id' => $user->id, 'status' => 1,
        ]);

        return [$doctor, TimeSlot::create(['time' => '09:00 AM', 'status' => 1])];
    }

    private function appointment(TimeSlot $slot, int $userId): Appointment
    {
        $user = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Cancel Doctor', 'slug' => 'cancel-doctor-'.uniqid(),
            'user_id' => $user->id, 'status' => 1,
        ]);

        return Appointment::create([
            'user_id' => $userId,
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Cancel Patient',
            'phone' => '+1 555 0100',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'cancellation_token' => Str::random(40),
            'status' => 0,
        ]);
    }
}
