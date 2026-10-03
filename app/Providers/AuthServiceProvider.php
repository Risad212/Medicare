<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * No gates registered: access is driven by the legacy `users.role`
     * column (`admin` / `doctor` / `patient`) via route middleware.
     */
    public function boot(): void
    {
        //
    }
}
