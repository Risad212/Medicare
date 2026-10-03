<?php

use App\Modules\Ambulance\Http\Controllers\Admin\AmbulanceRequestController as AdminAmbulanceRequestController;
use App\Modules\Ambulance\Http\Controllers\Frontend\AmbulanceController as FrontAmbulanceController;
use Illuminate\Support\Facades\Route;

Route::get('/ambulance', [FrontAmbulanceController::class, 'index'])
    ->middleware(['web', 'module:ambulance'])
    ->name('ambulance');

Route::post('/ambulance', [FrontAmbulanceController::class, 'store'])
    ->middleware(['web', 'module:ambulance', 'throttle:10,1'])
    ->name('ambulance.store');

Route::middleware(['web', 'auth', 'admin', 'module:ambulance', 'staff.modules'])->group(function () {
    Route::resource('/admin/ambulance-requests', AdminAmbulanceRequestController::class)
        ->names('admin.ambulance-requests')
        ->only(['index', 'update']);
});
