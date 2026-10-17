<?php

namespace App\Jobs;

use App\Services\Sms\SmsSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSms implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public function __construct(
        public readonly string $to,
        public readonly string $message,
        public readonly string $type,
        public readonly array $payload = [],
    ) {}

    public function handle(SmsSender $sender): void
    {
        $sender->send($this->to, $this->message, $this->type, $this->payload);
    }
}
