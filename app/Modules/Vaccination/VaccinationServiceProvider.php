<?php

namespace App\Modules\Vaccination;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class VaccinationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('vaccination')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
