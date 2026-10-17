<?php

namespace App\Services\Sms;

use App\Services\ActivityLogger;
use App\Support\Module;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsSender
{
    /**
     * Deliver one SMS through the configured gateway.
     *
     * @param  array<string, mixed>  $payload  extra context recorded in the activity feed
     */
    public function send(string $to, string $message, string $type, array $payload = []): bool
    {
        if (! Module::enabled('sms')) {
            return false;
        }

        $normalized = $this->normalize($to);

        if ($normalized === null) {
            return false;
        }

        try {
            app(SmsGateway::class)->send($normalized, $message);
        } catch (Throwable $e) {
            Log::warning('sms.failed', ['type' => $type, 'error' => $e->getMessage()]);

            $this->record('sms.failed', $normalized, $type, $payload + ['error' => $e->getMessage()]);

            return false;
        }

        $this->record('sms.sent', $normalized, $type, $payload);

        return true;
    }

    /**
     * Strip formatting; keep a leading + and 7-15 digits, otherwise reject.
     */
    public function normalize(string $to): ?string
    {
        $clean = preg_replace('/[\s().-]/', '', trim($to)) ?? '';

        if (! preg_match('/^\+?\d{7,15}$/', $clean)) {
            return null;
        }

        return $clean;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function record(string $action, string $to, string $type, array $payload): void
    {
        app(ActivityLogger::class)->log($action, null, [
            'type' => $type,
            'to' => $this->mask($to),
            'payload' => $payload ?: null,
        ]);
    }

    /**
     * Keep the recipient out of the feed in full: show country-ish prefix only.
     */
    private function mask(string $to): string
    {
        $digits = preg_replace('/\D+/', '', $to) ?? '';
        $visible = substr($digits, 0, 4);

        return (str_starts_with($to, '+') ? '+' : '').$visible.str_repeat('*', max(strlen($digits) - 4, 3));
    }
}
