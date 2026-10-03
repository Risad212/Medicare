<?php

namespace App\Modules\Beds;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class BedsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('beds')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
