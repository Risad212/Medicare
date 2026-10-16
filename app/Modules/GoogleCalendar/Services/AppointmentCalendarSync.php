<?php

namespace App\Modules\GoogleCalendar\Services;

use App\Models\Appointment;
use App\Modules\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Modules\GoogleCalendar\Models\GoogleCalendarEvent;
use Illuminate\Support\Facades\Cache;

class AppointmentCalendarSync
{
    public function __construct(private GoogleCalendarClient $client) {}

    public function sync(int $appointmentId): void
    {
        Cache::lock('google-calendar:appointment:'.$appointmentId, 30)
            ->block(5, fn () => $this->syncLocked($appointmentId));
    }

    private function syncLocked(int $appointmentId): void
    {
        $event = GoogleCalendarEvent::where('appointment_id', $appointmentId)->first();
        $appointment = Appointment::with(['doctor', 'timeSlot'])->find($appointmentId);

        if (! $appointment || (int) $appointment->status !== 1) {
            if ($event) {
                $this->client->deleteRemoteEvent($event);
            }

            return;
        }

        $connection = GoogleCalendarConnection::where('doctor_id', $appointment->doctor_id)->first();

        if (! $connection) {
            if ($event) {
                $this->client->deleteRemoteEvent($event);
            }

            return;
        }

        $this->client->sync($appointment, $connection);
    }
}
