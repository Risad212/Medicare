<?php

namespace App\Modules\Pharmacy;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class PharmacyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('pharmacy')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
