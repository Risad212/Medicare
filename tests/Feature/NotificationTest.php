<?php

namespace Tests\Feature;

use App\Models\LabOrder;
use App\Notifications\AppointmentBooked;
use App\Notifications\AppointmentReminder;
use App\Notifications\AppointmentStatusChanged;
use App\Notifications\LabResultReady;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_booking_an_appointment_notifies_admin_and_doctor(): void
    {
        Notification::fake();

        $admin = $this->makeAdmin();
        $doctor = $this->makeDoctor();

        $this->post(route('appointment.store'), $this->appointmentPayload([
            'doctor_id' => $doctor->id,
            'email' => 'patient@example.com',
        ]))->assertRedirect();

        $this->assertDatabaseCount('appointments', 1);
        Notification::assertSentTo($admin, AppointmentBooked::class);
        Notification::assertSentTo($doctor->user, AppointmentBooked::class);
    }

    public function test_admin_approval_notifies_staff_excluding_the_acting_admin(): void
    {
        Notification::fake();

        $admin = $this->makeAdmin();
        $doctor = $this->makeDoctor();
        $appointment = $this->makeAppointment(['doctor_id' => $doctor->id, 'email' => 'patient@example.com']);

        $this->actingAs($admin)
            ->put(route('admin.appointments.update', $appointment->id), [
                'doctor_id' => $appointment->doctor_id,
                'name' => $appointment->patient_name,
                'age' => $appointment->age,
                'gender' => $appointment->gender,
                'phone' => $appointment->phone,
                'email' => $appointment->email,
                'visit_type' => $appointment->visit_type,
                'date' => $appointment->appointment_date,
                'status' => 1,
            ])
            ->assertRedirect();

        Notification::assertSentTo($doctor->user, AppointmentStatusChanged::class, function ($notification) {
            return (int) $notification->status === 1;
        });
        Notification::assertNotSentTo($admin, AppointmentStatusChanged::class);
    }

    public function test_doctor_status_change_notifies_admins_but_not_the_acting_doctor(): void
    {
        Notification::fake();

        $admin = $this->makeAdmin();
        $doctor = $this->makeDoctor();
        $appointment = $this->makeAppointment(['doctor_id' => $doctor->id]);

        $this->actingAs($doctor->user)
            ->put(route('doctor.appointments.update', $appointment->id), ['status' => 1])
            ->assertRedirect();

        Notification::assertSentTo($admin, AppointmentStatusChanged::class);
        Notification::assertNotSentTo($doctor->user, AppointmentStatusChanged::class);
    }

    public function test_logged_in_patient_cancel_notifies_staff(): void
    {
        Notification::fake();

        $admin = $this->makeAdmin();
        $doctor = $this->makeDoctor();
        $patient = $this->makeUser(['role' => 'patient']);
        $appointment = $this->makeAppointment([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'status' => 0,
        ]);

        $this->actingAs($patient)
            ->patch(route('appointment.cancel', $appointment->id))
            ->assertRedirect();

        Notification::assertSentTo($admin, AppointmentStatusChanged::class, function ($notification) {
            return (int) $notification->status === 3;
        });
        Notification::assertSentTo($doctor->user, AppointmentStatusChanged::class, function ($notification) {
            return (int) $notification->status === 3;
        });
    }

    public function test_guest_cancel_notifies_staff(): void
    {
        Notification::fake();

        $admin = $this->makeAdmin();
        $doctor = $this->makeDoctor();
        $appointment = $this->makeAppointment(['doctor_id' => $doctor->id, 'status' => 0]);

        $this->put(route('appointment.cancel-by-token', $appointment->cancellation_token))
            ->assertRedirect();

        Notification::assertSentTo($admin, AppointmentStatusChanged::class);
        Notification::assertSentTo($doctor->user, AppointmentStatusChanged::class);
    }

    public function test_completing_a_lab_order_notifies_staff(): void
    {
        Notification::fake();

        $admin = $this->makeAdmin();
        $doctor = $this->makeDoctor();
        $test = $this->makeLabTest();

        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'email' => 'patient@example.com',
            'patient_name' => 'Patient Name',
            'priority' => 'normal',
            'status' => 'in-progress',
            'total' => $test->price,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.lab-orders.status', $order->id), ['status' => 'completed'])
            ->assertRedirect();

        Notification::assertSentTo($doctor->user, LabResultReady::class);
        Notification::assertNotSentTo($admin, LabResultReady::class);
    }

    public function test_reminder_command_notifies_the_doctor_in_app(): void
    {
        Notification::fake();

        $doctor = $this->makeDoctor();
        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'email' => 'patient@example.com',
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 1,
        ]);

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Notification::assertSentTo($doctor->user, AppointmentReminder::class);
    }

    public function test_unread_count_endpoint_returns_pending_count(): void
    {
        $admin = $this->makeAdmin();
        $appointment = $this->makeAppointment();
        $admin->notify(new AppointmentBooked($appointment));

        $this->actingAs($admin)
            ->getJson(route('notifications.unread-count'))
            ->assertOk()
            ->assertJson(['count' => 1]);
    }

    public function test_inbox_index_renders_and_mark_as_read_works(): void
    {
        $admin = $this->makeAdmin();
        $appointment = $this->makeAppointment();
        $admin->notify(new AppointmentBooked($appointment));

        $this->actingAs($admin)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('New appointment booked');

        $notification = $admin->notifications()->first();

        $this->actingAs($admin)
            ->patch(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
        $this->assertNotNull($admin->fresh()->notifications()->first()->read_at);
    }

    public function test_mark_all_as_read_clears_the_inbox(): void
    {
        $admin = $this->makeAdmin();
        $appointment = $this->makeAppointment();
        $admin->notify(new AppointmentBooked($appointment));
        $admin->notify(new AppointmentStatusChanged($appointment, 1));

        $this->actingAs($admin)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $admin->fresh()->unreadNotifications()->count());
    }

    public function test_users_cannot_mark_other_users_notifications(): void
    {
        $admin = $this->makeAdmin();
        $other = $this->makeAdmin();
        $appointment = $this->makeAppointment();
        $admin->notify(new AppointmentBooked($appointment));

        $notification = $admin->notifications()->first();

        $this->actingAs($other)
            ->patch(route('notifications.read', $notification->id))
            ->assertForbidden();
    }

    public function test_patients_can_access_notifications_pages(): void
    {
        $patient = $this->makeUser(['role' => 'patient']);

        $this->actingAs($patient)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('No Notifications Yet');

        $this->actingAs($patient)
            ->getJson(route('notifications.unread-count'))
            ->assertOk()
            ->assertJson(['count' => 0]);
    }
}
