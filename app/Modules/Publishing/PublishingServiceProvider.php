<?php

namespace App\Modules\Publishing;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class PublishingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('publishing')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
