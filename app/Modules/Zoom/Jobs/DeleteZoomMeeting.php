<?php

namespace App\Modules\Zoom\Jobs;

use App\Modules\Zoom\Services\ZoomClient;
use App\Modules\Zoom\Services\ZoomException;
use App\Support\Module;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteZoomMeeting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public readonly string $zoomMeetingId) {}

    public function handle(ZoomClient $client): void
    {
        if (Module::enabled('zoom')) {
            $client->deleteMeeting($this->zoomMeetingId);
        }
    }

    public function failed(Throwable $exception): void
    {
        $reason = $exception instanceof ZoomException ? $exception->reason : 'unexpected_error';

        Log::error('Zoom meeting cleanup failed.', [
            'error_code' => $reason,
        ]);
    }
}
