<?php

namespace App\Modules\GoogleCalendar\Services;

use RuntimeException;
use Throwable;

class GoogleCalendarException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        ?Throwable $previous = null,
    ) {
        parent::__construct('Google Calendar synchronization failed.', 0, $previous);
    }
}
