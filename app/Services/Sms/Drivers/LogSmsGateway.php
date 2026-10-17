<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsGateway;
use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGateway
{
    public function send(string $to, string $message): void
    {
        Log::info('sms.log_driver', ['to' => $to, 'message' => $message]);
    }
}
