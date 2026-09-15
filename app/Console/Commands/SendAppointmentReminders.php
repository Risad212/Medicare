<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Services\AppointmentNotifier;
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

            if ($email) {
                Mail::to($email)->queue(new AppointmentReminderMail($appointment));
                // Quiet: marking sent must not fire ActivityLogObserver::updated noise.
                $appointment->forceFill(['reminder_sent_at' => now()])->saveQuietly();
                $sent++;
            }
        }

        $this->info("Sent {$sent} appointment reminder(s).");

        return self::SUCCESS;
    }
}
