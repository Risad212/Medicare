<?php

namespace App\Modules\Reviews;

use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class ReviewsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('reviews')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
        }
    }
}
