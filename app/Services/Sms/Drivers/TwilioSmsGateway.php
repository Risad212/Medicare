<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDeliveryException;
use App\Services\Sms\SmsGateway;
use Illuminate\Support\Facades\Http;

class TwilioSmsGateway implements SmsGateway
{
    public function __construct(
        private readonly string $accountSid,
        private readonly string $authToken,
        private readonly string $from,
    ) {
        if ($this->accountSid === '' || $this->authToken === '') {
            throw new SmsDeliveryException('Twilio credentials are not configured.');
        }

        if ($this->from === '') {
            throw new SmsDeliveryException('Twilio "from" number is not configured.');
        }
    }

    public function send(string $to, string $message): void
    {
        $response = Http::withBasicAuth($this->accountSid, $this->authToken)
            ->timeout(10)
            ->post(
                "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json",
                [
                    'To' => $to,
                    'From' => $this->from,
                    'Body' => $message,
                ]
            );

        if ($response->failed()) {
            throw new SmsDeliveryException("Twilio rejected the message: HTTP {$response->status()}.");
        }
    }
}
