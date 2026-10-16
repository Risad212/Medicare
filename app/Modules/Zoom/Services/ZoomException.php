<?php

namespace App\Modules\Zoom\Services;

use RuntimeException;
use Throwable;

class ZoomException extends RuntimeException
{
    public function __construct(public readonly string $reason, ?Throwable $previous = null)
    {
        parent::__construct('Zoom meeting synchronization failed.', 0, $previous);
    }
}
