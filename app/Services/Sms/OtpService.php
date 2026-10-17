<?php

namespace App\Services\Sms;

use App\Jobs\SendSms;
use App\Models\User;
use App\Support\Module;
use Illuminate\Support\Facades\Cache;

class OtpService
{
    public const TTL_MINUTES = 5;

    public const MAX_ATTEMPTS = 5;

    /**
     * Issue a fresh one-time code and deliver it over SMS.
     *
     * Returns false when the account cannot receive SMS (module off,
     * no opted-in phone); true once the code is queued for delivery.
     */
    public function issue(User $user, string $purpose = 'default'): bool
    {
        if (! Module::enabled('sms') || ! $user->sms_enabled || ! $user->phone) {
            return false;
        }

        $code = (string) random_int(100000, 999999);

        Cache::put($this->key($user, $purpose), [
            'hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES)->getTimestamp(),
            'attempts' => 0,
        ], now()->addMinutes(self::TTL_MINUTES));

        SendSms::dispatch(
            $user->phone,
            __('messages.sms.otp', ['code' => $code, 'minutes' => (string) self::TTL_MINUTES], $user->locale ?? app()->getLocale()),
            'otp',
            ['purpose' => $purpose]
        );

        return true;
    }

    /**
     * Check a code against the outstanding one for this user/purpose.
     * A successful or expired/locked verification burns the code.
     */
    public function verify(User $user, string $purpose, string $code): bool
    {
        $key = $this->key($user, $purpose);
        $state = Cache::get($key);

        if (! is_array($state) || $code === '') {
            return false;
        }

        if ((int) $state['expires_at'] < now()->getTimestamp()) {
            Cache::forget($key);

            return false;
        }

        if ((int) $state['attempts'] >= self::MAX_ATTEMPTS) {
            Cache::forget($key);

            return false;
        }

        if (! hash_equals((string) $state['hash'], hash('sha256', $code))) {
            $state['attempts'] = (int) $state['attempts'] + 1;
            Cache::put($key, $state, max((int) $state['expires_at'] - now()->getTimestamp(), 1));

            return false;
        }

        Cache::forget($key);

        return true;
    }

    private function key(User $user, string $purpose): string
    {
        return 'sms.otp.'.$purpose.'.'.$user->id;
    }
}
