<?php

namespace App\Modules\GoogleCalendar\Observers;

use App\Models\Appointment;
use App\Modules\GoogleCalendar\Jobs\SyncAppointmentToGoogleCalendar;

class AppointmentCalendarObserver
{
    public function created(Appointment $appointment): void
    {
        if ((int) $appointment->status === 1) {
            SyncAppointmentToGoogleCalendar::dispatch($appointment->id)->afterCommit();
        }
    }

    public function updated(Appointment $appointment): void
    {
        if ($appointment->wasChanged([
            'doctor_id',
            'appointment_date',
            'time_slot_id',
            'status',
        ])) {
            SyncAppointmentToGoogleCalendar::dispatch($appointment->id)->afterCommit();
        }
    }

    public function deleted(Appointment $appointment): void
    {
        SyncAppointmentToGoogleCalendar::dispatch($appointment->id)->afterCommit();
    }
}
