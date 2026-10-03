<?php

namespace App\Modules\BloodBank;

use App\Modules\BloodBank\Console\Commands\BloodBankExpire;
use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class BloodBankServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Module::enabled('bloodbank')) {
            $this->loadRoutesFrom(__DIR__.'/routes.php');
            $this->commands([BloodBankExpire::class]);
        }
    }
}
