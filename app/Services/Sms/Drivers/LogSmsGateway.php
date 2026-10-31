<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsGateway;
use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGateway
{
    public function send(string $to, string $message): void
    {
        // The log driver is for local development only. Refuse to run in
        // production so OTP codes and phone numbers are never written to logs.
        if (app()->environment('production')) {
            throw new \RuntimeException('SMS_DRIVER=log is not allowed in production.');
        }

        Log::info('sms.log_driver', ['to' => $to, 'message' => $message]);
    }
}
