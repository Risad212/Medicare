<?php

namespace App\Modules\Ambulance;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class AmbulanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('ambulance')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
