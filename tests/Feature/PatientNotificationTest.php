<?php

namespace Tests\Feature;

use App\Models\LabOrder;
use App\Notifications\AppointmentBookedPatient;
use App\Notifications\AppointmentReminderPatient;
use App\Notifications\AppointmentStatusChangedPatient;
use App\Notifications\LabResultReadyPatient;
use App\Notifications\PrescriptionReadyPatient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class PatientNotificationTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    private function makeLabOrder(array $overrides = []): LabOrder
    {
        $test = $this->makeLabTest();

        return LabOrder::create(array_merge([
            'doctor_id' => $this->makeDoctor()->id,
            'email' => 'patient@example.com',
            'patient_name' => 'Patient Name',
            'priority' => 'normal',
            'status' => 'in-progress',
            'total' => $test->price,
        ], $overrides));
    }

    private function prescriptionPayload(?int $appointmentId = null, array $overrides = []): array
    {
        return array_merge([
            'appointment_id' => $appointmentId,
            'patient_name' => 'Alice Patient',
            'age' => 28,
            'gender' => 2,
            'phone' => '01712-222222',
            'email' => 'alice@example.com',
            'diagnosis' => 'Lower respiratory infection',
            'items' => [
                ['medicine_name' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => '1-0-1', 'duration' => '5 days'],
            ],
        ], $overrides);
    }

    public function test_logged_in_booking_notifies_the_patient_account(): void
    {
        Notification::fake();

        $patient = $this->makeUser();
        $doctor = $this->makeDoctor();

        $this->actingAs($patient)
            ->post(route('appointment.store'), $this->appointmentPayload([
                'doctor_id' => $doctor->id,
                'email' => $patient->email,
            ]))
            ->assertRedirect();

        Notification::assertSentTo($patient, AppointmentBookedPatient::class);
    }

    public function test_guest_booking_does_not_notify_any_patient(): void
    {
        Notification::fake();

        $doctor = $this->makeDoctor();

        $this->post(route('appointment.store'), $this->appointmentPayload([
            'doctor_id' => $doctor->id,
            'email' => 'guest@example.com',
        ]))->assertRedirect();

        Notification::assertNotSentTo($this->makeUser(), AppointmentBookedPatient::class);
    }

    public function test_admin_status_change_notifies_the_patient(): void
    {
        Notification::fake();

        $admin = $this->makeAdmin();
        $patient = $this->makeUser();
        $appointment = $this->makeAppointment([
            'user_id' => $patient->id,
            'email' => $patient->email,
        ]);

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

        Notification::assertSentTo($patient, AppointmentStatusChangedPatient::class, function ($notification) {
            return $notification->status === 1;
        });
    }

    public function test_doctor_status_change_notifies_the_patient(): void
    {
        Notification::fake();

        $patient = $this->makeUser();
        $doctor = $this->makeDoctor();
        $appointment = $this->makeAppointment([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'email' => $patient->email,
        ]);

        $this->actingAs($doctor->user)
            ->put(route('doctor.appointments.update', $appointment->id), ['status' => 2])
            ->assertRedirect();

        Notification::assertSentTo($patient, AppointmentStatusChangedPatient::class, function ($notification) {
            return $notification->status === 2;
        });
    }

    public function test_patient_cancel_notifies_the_patient(): void
    {
        Notification::fake();

        $patient = $this->makeUser();
        $appointment = $this->makeAppointment([
            'user_id' => $patient->id,
            'email' => $patient->email,
            'status' => 0,
        ]);

        $this->actingAs($patient)
            ->patch(route('appointment.cancel', $appointment->id))
            ->assertRedirect();

        Notification::assertSentTo($patient, AppointmentStatusChangedPatient::class, function ($notification) {
            return $notification->status === 3;
        });
    }

    public function test_completing_a_lab_order_notifies_the_patient(): void
    {
        Notification::fake();

        $patient = $this->makeUser();
        $order = $this->makeLabOrder([
            'user_id' => $patient->id,
            'email' => $patient->email,
        ]);

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.lab-orders.status', $order->id), ['status' => 'completed'])
            ->assertRedirect();

        Notification::assertSentTo($patient, LabResultReadyPatient::class);
    }

    public function test_reminder_command_notifies_the_patient_and_stamps_sent(): void
    {
        Notification::fake();
        Mail::fake();

        $patient = $this->makeUser();
        $doctor = $this->makeDoctor();
        $appointment = $this->makeAppointment([
            'user_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'email' => null,
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 1,
        ]);

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Notification::assertSentTo($patient, AppointmentReminderPatient::class);
        $this->assertNotNull($appointment->fresh()->reminder_sent_at);
    }

    public function test_prescription_creation_notifies_the_patient(): void
    {
        Notification::fake();
        Mail::fake();

        $doctor = $this->makeDoctor();
        $patient = $this->makeUser(['email' => 'alice@example.com']);
        $appointment = $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'user_id' => $patient->id,
            'email' => 'alice@example.com',
            'status' => 1,
        ]);

        $this->actingAs($doctor->user)
            ->post(route('doctor.prescriptions.store'), $this->prescriptionPayload($appointment->id))
            ->assertRedirect();

        Notification::assertSentTo($patient, PrescriptionReadyPatient::class);
    }

    public function test_walkin_prescription_does_not_notify_a_patient_account(): void
    {
        Notification::fake();
        Mail::fake();

        $doctor = $this->makeDoctor();

        $this->actingAs($doctor->user)
            ->post(route('doctor.prescriptions.store'), $this->prescriptionPayload(null, [
                'email' => null,
                'patient_name' => 'Walk-in Patient',
            ]))
            ->assertRedirect();

        Notification::assertNothingSent();
    }

    public function test_patient_inbox_renders_and_mark_as_read_works(): void
    {
        $patient = $this->makeUser();
        $appointment = $this->makeAppointment(['user_id' => $patient->id]);
        $patient->notify(new AppointmentBookedPatient($appointment));

        $this->actingAs($patient)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Booking received');

        $notification = $patient->notifications()->first();

        $this->actingAs($patient)
            ->patch(route('notifications.read', $notification->id))
            ->assertRedirect();

        $this->assertSame(0, $patient->fresh()->unreadNotifications()->count());
    }

    public function test_patient_cannot_mark_another_patients_notification(): void
    {
        $owner = $this->makeUser();
        $other = $this->makeUser();
        $appointment = $this->makeAppointment(['user_id' => $owner->id]);
        $owner->notify(new AppointmentBookedPatient($appointment));

        $notification = $owner->notifications()->first();

        $this->actingAs($other)
            ->patch(route('notifications.read', $notification->id))
            ->assertForbidden();
    }

    public function test_patient_unread_count_reflects_their_own_inbox(): void
    {
        $patient = $this->makeUser();
        $appointment = $this->makeAppointment(['user_id' => $patient->id]);
        $patient->notify(new AppointmentBookedPatient($appointment));
        $patient->notify(new AppointmentStatusChangedPatient($appointment, 1));

        $this->actingAs($patient)
            ->getJson(route('notifications.unread-count'))
            ->assertOk()
            ->assertJson(['count' => 2]);
    }
}
