<?php

namespace App\Modules\Directory;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class DirectoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('directory')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
