<?php

namespace App\Services\Sms;

use App\Services\Sms\Drivers\BulkSmsBdGateway;
use App\Services\Sms\Drivers\D7SmsGateway;
use App\Services\Sms\Drivers\LogSmsGateway;
use App\Services\Sms\Drivers\TwilioSmsGateway;
use InvalidArgumentException;

class SmsGatewayFactory
{
    /**
     * @param  array<string, mixed>  $config  the full config/sms.php array
     */
    public static function make(string $driver, array $config = []): SmsGateway
    {
        $providers = $config['providers'] ?? [];

        return match ($driver) {
            'log' => new LogSmsGateway,
            'd7' => new D7SmsGateway(
                (string) ($providers['d7']['token'] ?? ''),
                (string) ($providers['d7']['originator'] ?? ''),
                (string) ($providers['d7']['base_url'] ?? 'https://api.d7networks.com'),
            ),
            'bulksmsbd' => new BulkSmsBdGateway(
                (string) ($providers['bulksmsbd']['api_key'] ?? ''),
                (string) ($providers['bulksmsbd']['senderid'] ?? ''),
                (string) ($providers['bulksmsbd']['base_url'] ?? 'https://bulksmsbd.net/api'),
            ),
            'twilio' => new TwilioSmsGateway(
                (string) ($providers['twilio']['account_sid'] ?? ''),
                (string) ($providers['twilio']['auth_token'] ?? ''),
                (string) ($providers['twilio']['from'] ?? $config['from'] ?? ''),
            ),
            default => throw new InvalidArgumentException("Unsupported SMS driver [{$driver}]."),
        };
    }
}
