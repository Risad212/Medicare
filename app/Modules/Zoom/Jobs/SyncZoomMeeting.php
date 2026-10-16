<?php

namespace App\Modules\Zoom\Jobs;

use App\Modules\Zoom\Models\ZoomMeeting;
use App\Modules\Zoom\Services\ZoomException;
use App\Modules\Zoom\Services\ZoomMeetingSync;
use App\Support\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncZoomMeeting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public readonly int $appointmentId) {}

    public function handle(ZoomMeetingSync $sync): void
    {
        if (Module::enabled('zoom')) {
            $sync->sync($this->appointmentId);
        }
    }

    public function failed(Throwable $exception): void
    {
        $reason = $exception instanceof ZoomException ? $exception->reason : 'unexpected_error';

        ZoomMeeting::where('appointment_id', $this->appointmentId)->update([
            'sync_status' => 'failed',
            'last_error_code' => $reason,
        ]);

        Log::error('Zoom telehealth synchronization failed.', [
            'appointment_id' => $this->appointmentId,
            'error_code' => $reason,
        ]);
    }
}
