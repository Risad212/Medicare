<?php

use App\Modules\Vaccination\Http\Controllers\Admin\VaccinationController as AdminVaccinationController;
use App\Modules\Vaccination\Http\Controllers\Doctor\VaccinationController as DoctorVaccinationController;
use App\Modules\Vaccination\Http\Controllers\Frontend\VaccinationController as FrontVaccinationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:vaccination', 'staff.modules'])->group(function () {
    Route::resource('/admin/vaccinations', AdminVaccinationController::class)
        ->names('admin.vaccinations');
});

Route::middleware(['web', 'auth', 'doctor', 'module:vaccination'])->group(function () {
    Route::get('/doctor/vaccinations', [DoctorVaccinationController::class, 'index'])->name('doctor.vaccinations.index');

    Route::get('/doctor/vaccinations/create', [DoctorVaccinationController::class, 'create'])->name('doctor.vaccinations.create');

    Route::post('/doctor/vaccinations', [DoctorVaccinationController::class, 'store'])->name('doctor.vaccinations.store');

    Route::get('/doctor/vaccinations/{vaccination}', [DoctorVaccinationController::class, 'show'])->name('doctor.vaccinations.show');

    Route::get('/doctor/vaccinations/{vaccination}/edit', [DoctorVaccinationController::class, 'edit'])->name('doctor.vaccinations.edit');

    Route::put('/doctor/vaccinations/{vaccination}', [DoctorVaccinationController::class, 'update'])->name('doctor.vaccinations.update');

    Route::delete('/doctor/vaccinations/{vaccination}', [DoctorVaccinationController::class, 'destroy'])->name('doctor.vaccinations.destroy');
});

Route::middleware(['web', 'auth', 'patient', 'module:vaccination'])->group(function () {
    Route::get('/my-vaccinations', [FrontVaccinationController::class, 'mine'])->name('my-vaccinations');
});
