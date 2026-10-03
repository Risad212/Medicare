<?php

use App\Modules\Beds\Http\Controllers\BedController;
use App\Modules\Beds\Http\Controllers\RoomController;
use App\Modules\Beds\Http\Controllers\WardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:beds', 'staff.modules'])->group(function () {
    Route::resource('/admin/wards', WardController::class)->names('admin.wards');

    Route::resource('/admin/rooms', RoomController::class)->names('admin.rooms');

    Route::resource('/admin/beds', BedController::class)
        ->names('admin.beds')
        ->except(['show']);

    Route::post('/admin/beds/{bed}/assign', [BedController::class, 'assign'])->name('admin.beds.assign');

    Route::post('/admin/beds/{bed}/discharge', [BedController::class, 'discharge'])->name('admin.beds.discharge');
});
