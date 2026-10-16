<?php

use App\Modules\Zoom\Http\Controllers\TelehealthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'module:zoom'])
    ->get('/telehealth/{appointment}/join', [TelehealthController::class, 'join'])
    ->middleware('signed')
    ->name('telehealth.join');

Route::middleware(['web', 'auth', 'doctor', 'module:zoom'])
    ->prefix('/doctor/telehealth')
    ->name('doctor.telehealth.')
    ->group(function () {
        Route::get('/{appointment}/host', [TelehealthController::class, 'host'])->name('host');
        Route::post('/{appointment}/retry', [TelehealthController::class, 'retry'])->name('retry');
    });
