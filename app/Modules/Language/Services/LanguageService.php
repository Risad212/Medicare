<?php

namespace App\Modules\Language\Services;

use App\Modules\Language\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LanguageService
{
    /**
     * Active languages, default first. Falls back to English when the
     * languages table is unavailable (fresh install / tests booting).
     */
    public function available(): array
    {
        try {
            if (! Schema::hasTable('languages')) {
                return [['name' => 'English', 'code' => 'en']];
            }

            $rows = Language::active()->ordered()->get(['name', 'code'])->toArray();

            return $rows !== [] ? $rows : [['name' => 'English', 'code' => 'en']];
        } catch (\Throwable) {
            return [['name' => 'English', 'code' => 'en']];
        }
    }

    public function activeCodes(): array
    {
        return array_column($this->available(), 'code');
    }

    public function defaultCode(): string
    {
        try {
            if (Schema::hasTable('languages')) {
                $code = Language::active()->where('is_default', true)->value('code');
                if (is_string($code) && $code !== '') {
                    return $code;
                }
            }
        } catch (\Throwable) {
        }

        return config('app.locale', 'en');
    }

    /**
     * Resolution order: session -> user profile -> browser Accept-Language -> default.
     */
    public function resolve(Request $request): string
    {
        $active = $this->activeCodes();

        $session = $request->session()->get('locale');
        if (is_string($session) && in_array($session, $active, true)) {
            return $session;
        }

        $user = $request->user();
        if ($user && is_string($user->locale ?? null) && in_array($user->locale, $active, true)) {
            return $user->locale;
        }

        $browser = $request->getPreferredLanguage($active);
        if (is_string($browser) && $browser !== '') {
            return $browser;
        }

        return $this->defaultCode();
    }

    public function isActive(string $code): bool
    {
        return in_array($code, $this->activeCodes(), true);
    }
}
