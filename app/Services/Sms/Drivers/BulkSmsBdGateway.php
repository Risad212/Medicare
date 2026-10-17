<?php

namespace App\Services\Sms\Drivers;

use App\Services\Sms\SmsDeliveryException;
use App\Services\Sms\SmsGateway;
use Illuminate\Support\Facades\Http;

class BulkSmsBdGateway implements SmsGateway
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $senderId,
        private readonly string $baseUrl,
    ) {
        if ($this->apiKey === '') {
            throw new SmsDeliveryException('BulkSMSBD API key is not configured.');
        }
    }

    public function send(string $to, string $message): void
    {
        $response = Http::timeout(10)->get(rtrim($this->baseUrl, '/').'/smsapi', [
            'api_key' => $this->apiKey,
            'senderid' => $this->senderId,
            'number' => $to,
            'message' => $message,
        ]);

        if ($response->failed()) {
            throw new SmsDeliveryException("BulkSMSBD rejected the message: HTTP {$response->status()}.");
        }

        $payload = $response->json();

        if (! is_array($payload) || (int) ($payload['success'] ?? 0) !== 1) {
            $detail = is_array($payload)
                ? (string) ($payload['msg'] ?? $payload['error_code'] ?? 'unknown error')
                : 'non-JSON response';

            throw new SmsDeliveryException("BulkSMSBD rejected the message: {$detail}.");
        }
    }
}
