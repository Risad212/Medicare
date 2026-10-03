<?php

namespace App\Modules\Language\Http\Middleware;

use App\Modules\Language\Services\LanguageService;
use App\Support\Module;
use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function __construct(private LanguageService $languages) {}

    public function handle(Request $request, Closure $next)
    {
        // Runtime kill-switch: flag off mid-process (tests, env change
        // without reboot) means plain default locale, no DB lookups.
        if (! Module::enabled('language')) {
            app()->setLocale(config('app.locale', 'en'));

            return $next($request);
        }

        $locale = $this->languages->resolve($request);
        app()->setLocale($locale);

        try {
            view()->share('availableLanguages', $this->languages->available());
            view()->share('currentLocale', $locale);
        } catch (\Throwable) {
        }

        return $next($request);
    }
}
