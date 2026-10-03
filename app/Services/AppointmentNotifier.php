<?php

namespace App\Services;

use App\Mail\AppointmentConfirmedMail;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;

class AppointmentNotifier
{
    /**
     * Send a confirmation email when an appointment is approved.
     */
    public function notifyApproved(Appointment $appointment): void
    {
        $email = $this->recipient($appointment);

        if ($email) {
            Mail::to($email)->queue(new AppointmentConfirmedMail($appointment->loadMissing('doctor', 'timeSlot')));
        }
    }

    /**
     * Resolve the best email address for an appointment.
     */
    public static function recipient(Appointment $appointment): ?string
    {
        return $appointment->email ?: $appointment->user?->email ?: null;
    }
}
