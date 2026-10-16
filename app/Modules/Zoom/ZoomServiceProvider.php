<?php

namespace App\Modules\Zoom;

use App\Models\Appointment;
use App\Modules\Zoom\Observers\AppointmentZoomObserver;
use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class ZoomServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! Module::enabled('zoom')) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/routes.php');
        Appointment::observe(AppointmentZoomObserver::class);
    }
}
