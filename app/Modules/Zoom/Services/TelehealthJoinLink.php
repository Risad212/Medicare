<?php

namespace App\Modules\Zoom\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;

class TelehealthJoinLink
{
    public function for(Appointment $appointment): string
    {
        $start = $this->startAt($appointment);
        $duration = max(5, min(1440, (int) config('services.zoom.duration_minutes', 60)));
        $expiresAt = $start->copy()->addMinutes($duration + 15);

        return URL::temporarySignedRoute('telehealth.join', $expiresAt, [
            'appointment' => $appointment->id,
        ]);
    }

    public function isJoinable(Appointment $appointment): bool
    {
        $start = $this->startAt($appointment);
        $duration = max(5, min(1440, (int) config('services.zoom.duration_minutes', 60)));

        return now()->between(
            $start->copy()->subMinutes(30),
            $start->copy()->addMinutes($duration + 15),
        );
    }

    private function startAt(Appointment $appointment): Carbon
    {
        if (! $appointment->timeSlot || ! is_string($appointment->timeSlot->time)) {
            throw new ZoomException('appointment_time_unavailable');
        }

        return Carbon::createFromFormat(
            'Y-m-d h:i A',
            $appointment->appointment_date->format('Y-m-d').' '.$appointment->timeSlot->time,
            config('app.timezone'),
        );
    }
}
