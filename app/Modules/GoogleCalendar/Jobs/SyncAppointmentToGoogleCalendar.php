<?php

namespace App\Modules\GoogleCalendar\Jobs;

use App\Modules\GoogleCalendar\Models\GoogleCalendarEvent;
use App\Modules\GoogleCalendar\Services\AppointmentCalendarSync;
use App\Modules\GoogleCalendar\Services\GoogleCalendarException;
use App\Support\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncAppointmentToGoogleCalendar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public readonly int $appointmentId) {}

    public function handle(AppointmentCalendarSync $sync): void
    {
        if (Module::enabled('google_calendar')) {
            $sync->sync($this->appointmentId);
        }
    }

    public function failed(Throwable $exception): void
    {
        $event = GoogleCalendarEvent::where('appointment_id', $this->appointmentId)->first();
        $reason = $exception instanceof GoogleCalendarException ? $exception->reason : 'unexpected_error';

        $event?->update([
            'sync_status' => 'failed',
            'last_error_code' => $reason,
        ]);

        Log::error('Google Calendar appointment synchronization failed.', [
            'appointment_id' => $this->appointmentId,
            'error_code' => $reason,
        ]);
    }
}
