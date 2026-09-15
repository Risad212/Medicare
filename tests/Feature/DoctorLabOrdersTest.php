<?php

namespace Tests\Feature;

use App\Models\LabOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class DoctorLabOrdersTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_doctor_can_view_their_lab_orders(): void
    {
        $doctor = $this->makeDoctor();
        $test = $this->makeLabTest();

        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Bob Patient',
            'priority' => 'normal',
            'status' => 'pending',
            'total' => $test->price,
        ]);

        $this->actingAs(User::find($doctor->user_id))
            ->get(route('doctor.lab-orders.index'))
            ->assertOk()
            ->assertSee('Bob Patient');
    }

    public function test_doctor_can_create_lab_order_with_multiple_tests(): void
    {
        $doctor = $this->makeDoctor();
        $testA = $this->makeLabTest(['name' => 'CBC', 'price' => 20.50]);
        $testB = $this->makeLabTest(['name' => 'Lipid Profile', 'price' => 15.00]);

        $response = $this->actingAs(User::find($doctor->user_id))
            ->post(route('doctor.lab-orders.store'), [
                'patient_name' => 'Carla Patient',
                'phone' => '01711-999999',
                'priority' => 'urgent',
                'note' => 'Fasting sample required.',
                'lab_test_ids' => [$testA->id, $testB->id],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('lab_orders', [
            'doctor_id' => $doctor->id,
            'patient_name' => 'Carla Patient',
            'priority' => 'urgent',
            'status' => 'pending',
            'total' => 35.50,
        ]);

        $order = LabOrder::where('patient_name', 'Carla Patient')->firstOrFail();
        $this->assertCount(2, $order->items);
        $this->assertDatabaseHas('lab_order_items', [
            'lab_order_id' => $order->id,
            'lab_test_id' => $testA->id,
            'price' => 20.50,
        ]);
    }

    public function test_doctor_cannot_create_order_without_tests(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs(User::find($doctor->user_id))
            ->from(route('doctor.lab-orders.create'))
            ->post(route('doctor.lab-orders.store'), [
                'patient_name' => 'Carla Patient',
            ])
            ->assertSessionHasErrors('lab_test_ids');
    }

    public function test_doctor_cannot_link_another_doctors_appointment(): void
    {
        $doctor = $this->makeDoctor();
        $otherDoctor = $this->makeDoctor(['name' => 'Dr. Other', 'slug' => 'dr-other-1']);
        $otherAppointment = $this->makeAppointment(['doctor_id' => $otherDoctor->id]);
        $test = $this->makeLabTest();

        $this->actingAs(User::find($doctor->user_id))
            ->post(route('doctor.lab-orders.store'), [
                'patient_name' => 'Carla Patient',
                'appointment_id' => $otherAppointment->id,
                'lab_test_ids' => [$test->id],
            ])
            ->assertForbidden();
    }

    public function test_doctor_cannot_view_another_doctors_order(): void
    {
        $doctor = $this->makeDoctor();
        $otherDoctor = $this->makeDoctor(['name' => 'Dr. Other', 'slug' => 'dr-other-2']);
        $test = $this->makeLabTest();

        $order = LabOrder::create([
            'doctor_id' => $otherDoctor->id,
            'patient_name' => 'Private Patient',
            'priority' => 'normal',
            'status' => 'pending',
            'total' => $test->price,
        ]);

        $this->actingAs(User::find($doctor->user_id))
            ->get(route('doctor.lab-orders.show', $order->id))
            ->assertForbidden();
    }

    public function test_guest_cannot_access_lab_orders(): void
    {
        $this->get(route('doctor.lab-orders.index'))->assertRedirect('/login');
    }

    public function test_selected_user_identity_overrides_typed_patient_fields(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makeUser([
            'name' => 'Linked Patient',
            'email' => 'linked@example.com',
            'phone' => '01999-000000',
        ]);
        $test = $this->makeLabTest();

        $this->actingAs(User::find($doctor->user_id))
            ->post(route('doctor.lab-orders.store'), [
                'user_id' => $patient->id,
                'patient_name' => 'Typed Different Name',
                'phone' => '01711-123456',
                'email' => 'typed@example.com',
                'lab_test_ids' => [$test->id],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('lab_orders', [
            'user_id' => $patient->id,
            'patient_name' => 'Linked Patient',
            'phone' => '01999-000000',
            'email' => 'linked@example.com',
        ]);
    }
}
