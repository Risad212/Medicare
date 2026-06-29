<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\GeneralSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share(
            'setting',
            GeneralSetting::first()
        );
    }
}