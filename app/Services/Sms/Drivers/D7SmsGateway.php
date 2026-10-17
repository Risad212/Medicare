<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDeliveryException;
use App\Services\Sms\SmsGateway;
use Illuminate\Support\Facades\Http;

class D7SmsGateway implements SmsGateway
{
    public function __construct(
        private readonly string $token,
        private readonly string $originator,
        private readonly string $baseUrl,
    ) {
        if ($this->token === '') {
            throw new SmsDeliveryException('D7 API token is not configured.');
        }
    }

    public function send(string $to, string $message): void
    {
        $response = Http::withToken($this->token)
            ->timeout(10)
            ->post(rtrim($this->baseUrl, '/').'/messages/v1/send', [
                'originator' => $this->originator,
                'recipients' => [$to],
                'content' => [
                    'message_type' => 'text',
                    'body' => $message,
                ],
                'message_type' => 'text',
            ]);

        if ($response->failed()) {
            throw new SmsDeliveryException("D7 Networks rejected the message: HTTP {$response->status()}.");
        }
    }
}
