<?php

namespace App\Modules\Language;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class LanguageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! Module::enabled('language')) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
