<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Services\AppointmentNotifier;
use App\Services\PatientNotifier;
use App\Services\StaffNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';

    protected $description = 'Email reminders for appointments scheduled tomorrow';

    public function handle(): int
    {
        $appointments = Appointment::with('doctor', 'timeSlot', 'user')
            ->whereDate('appointment_date', now()->addDay()->toDateString())
            ->where('status', 1)
            ->whereNull('reminder_sent_at')
            ->get();

        $sent = 0;

        foreach ($appointments as $appointment) {
            $email = AppointmentNotifier::recipient($appointment);

            // Doctor gets an in-app heads-up for tomorrow's visit too.
            StaffNotifier::appointmentReminder($appointment);

            // Patient's own account (if linked) gets the in-app reminder.
            PatientNotifier::appointmentReminder($appointment);

            if ($email) {
                Mail::to($email)->queue(new AppointmentReminderMail($appointment));
                // Quiet: marking sent must not fire ActivityLogObserver::updated noise.
                $appointment->forceFill(['reminder_sent_at' => now()])->saveQuietly();
                $sent++;
            } elseif ($appointment->user_id || $appointment->doctor?->user_id) {
                // In-app reminder was delivered to at least one account;
                // stamp so tomorrow's run does not repeat it.
                $appointment->forceFill(['reminder_sent_at' => now()])->saveQuietly();
            }
        }

        $this->info("Sent {$sent} appointment reminder(s).");

        return self::SUCCESS;
    }
}
