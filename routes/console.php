<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('appointments:send-reminders')->dailyAt('08:00');

Schedule::command('bloodbank:expire')->dailyAt('02:00');

// Prevent table bloat: jobs/sessions/activity grow forever otherwise.
Schedule::command('queue:prune-failed')->weekly();

Schedule::command('queue:prune-batches')->daily();

Schedule::command('model:prune')->daily();
