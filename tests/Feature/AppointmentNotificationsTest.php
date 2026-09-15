<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmedMail;
use App\Mail\AppointmentReminderMail;
use App\Mail\LabResultReadyMail;
use App\Models\LabOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AppointmentNotificationsTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_approving_appointment_sends_confirmation_email(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $appointment = $this->makeAppointment(['email' => 'patient@example.com']);

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

        Mail::assertQueued(AppointmentConfirmedMail::class, function ($mail) {
            return $mail->hasTo('patient@example.com');
        });
    }

    public function test_approving_an_already_approved_appointment_does_not_resend_email(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();
        $appointment = $this->makeAppointment(['email' => 'patient@example.com', 'status' => 1]);

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

        Mail::assertNothingSent();
    }

    public function test_doctor_approval_sends_confirmation_email(): void
    {
        Mail::fake();

        $doctor = $this->makeDoctor();
        $appointment = $this->makeAppointment(['doctor_id' => $doctor->id, 'email' => 'patient@example.com']);

        $this->actingAs($doctor->user)
            ->put(route('doctor.appointments.update', $appointment->id), ['status' => 1])
            ->assertRedirect();

        Mail::assertQueued(AppointmentConfirmedMail::class);
    }

    public function test_reminder_command_sends_one_email_per_appointment_and_marks_sent(): void
    {
        Mail::fake();

        $tomorrow = now()->addDay()->toDateString();
        $this->makeAppointment(['email' => 'patient@example.com', 'appointment_date' => $tomorrow, 'status' => 1]);
        $this->makeAppointment(['email' => 'second@example.com', 'appointment_date' => $tomorrow, 'status' => 1]);

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertQueued(AppointmentReminderMail::class, 2);

        $this->assertDatabaseHas('appointments', [
            'email' => 'patient@example.com',
            'reminder_sent_at' => now(),
        ]);
    }

    public function test_reminder_command_skips_pending_cancelled_and_non_tomorrow_appointments(): void
    {
        Mail::fake();

        $this->makeAppointment(['email' => 'pending@example.com', 'appointment_date' => now()->addDay()->toDateString(), 'status' => 0]);
        $this->makeAppointment(['email' => 'later@example.com', 'appointment_date' => now()->addDays(5)->toDateString(), 'status' => 1]);
        $this->makeAppointment(['email' => 'cancelled@example.com', 'appointment_date' => now()->addDay()->toDateString(), 'status' => 3]);

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_reminder_command_does_not_resend_to_already_reminded(): void
    {
        Mail::fake();

        $this->makeAppointment([
            'email' => 'patient@example.com',
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 1,
            'reminder_sent_at' => now(),
        ]);

        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_completing_lab_order_sends_result_email(): void
    {
        Mail::fake();

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

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.lab-orders.status', $order->id), ['status' => 'completed'])
            ->assertRedirect();

        Mail::assertQueued(LabResultReadyMail::class, function ($mail) {
            return $mail->hasTo('patient@example.com');
        });
    }
}
