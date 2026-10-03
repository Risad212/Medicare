<?php

namespace App\Modules\Lab;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class LabServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('lab')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
