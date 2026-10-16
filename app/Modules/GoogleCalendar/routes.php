<?php

use App\Modules\GoogleCalendar\Http\Controllers\CalendarConnectionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'doctor', 'module:google_calendar'])
    ->prefix('/doctor/calendar')
    ->name('doctor.calendar.')
    ->group(function () {
        Route::get('/', [CalendarConnectionController::class, 'index'])->name('index');
        Route::get('/connect', [CalendarConnectionController::class, 'connect'])->name('connect');
        Route::get('/callback', [CalendarConnectionController::class, 'callback'])->name('callback');
        Route::delete('/', [CalendarConnectionController::class, 'disconnect'])->name('disconnect');
        Route::post('/retry/{appointment}', [CalendarConnectionController::class, 'retry'])->name('retry');
    });
