<?php

namespace App\Modules\Backups;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class BackupsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('backups')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
