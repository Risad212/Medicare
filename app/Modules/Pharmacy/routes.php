<?php

use App\Modules\Pharmacy\Http\Controllers\DispenseController;
use App\Modules\Pharmacy\Http\Controllers\MedicineController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:pharmacy', 'staff.modules'])->group(function () {
    Route::resource('/admin/medicines', MedicineController::class)
        ->names('admin.medicines')
        ->except(['show']);

    Route::post('/admin/prescriptions/{prescription}/items/{item}/dispense', [DispenseController::class, 'store'])
        ->name('admin.prescriptions.items.dispense');
});
