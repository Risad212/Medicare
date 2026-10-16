<?php

namespace App\Modules\GoogleCalendar;

use App\Models\Appointment;
use App\Modules\GoogleCalendar\Observers\AppointmentCalendarObserver;
use App\Support\Module;
use Illuminate\Support\ServiceProvider;

class GoogleCalendarServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! Module::enabled('google_calendar')) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/routes.php');
        Appointment::observe(AppointmentCalendarObserver::class);
    }
}
