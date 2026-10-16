<?php

use App\Modules\Prescriptions\Http\Controllers\Admin\PrescriptionController as PrescriptionsAdminController;
use App\Modules\Prescriptions\Http\Controllers\Doctor\PrescriptionController as PrescriptionsDoctorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:prescriptions', 'staff.modules'])->group(function () {
    Route::get('/admin/prescriptions', [PrescriptionsAdminController::class, 'index'])->name('admin.prescriptions.index');

    Route::get('/admin/prescriptions/{prescription}', [PrescriptionsAdminController::class, 'show'])->name('admin.prescriptions.show');

    Route::get('/admin/prescriptions/{prescription}/pdf', [PrescriptionsAdminController::class, 'pdf'])->name('admin.prescriptions.pdf');

    Route::delete('/admin/prescriptions/{prescription}', [PrescriptionsAdminController::class, 'destroy'])->name('admin.prescriptions.destroy');
});

Route::middleware(['web', 'auth', 'doctor', 'module:prescriptions'])->group(function () {
    Route::get('/doctor/appointments/picker', [PrescriptionsDoctorController::class, 'appointmentPicker'])
        ->middleware('throttle:60,1')
        ->name('doctor.appointments.picker');

    Route::get('/doctor/prescriptions', [PrescriptionsDoctorController::class, 'index'])->name('doctor.prescriptions.index');

    Route::get('/doctor/prescriptions/create', [PrescriptionsDoctorController::class, 'create'])->name('doctor.prescriptions.create');

    Route::post('/doctor/prescriptions', [PrescriptionsDoctorController::class, 'store'])->name('doctor.prescriptions.store');

    Route::get('/doctor/prescriptions/{prescription}', [PrescriptionsDoctorController::class, 'show'])->name('doctor.prescriptions.show');

    Route::get('/doctor/prescriptions/{prescription}/edit', [PrescriptionsDoctorController::class, 'edit'])->name('doctor.prescriptions.edit');

    Route::get('/doctor/prescriptions/{prescription}/pdf', [PrescriptionsDoctorController::class, 'pdf'])->name('doctor.prescriptions.pdf');

    Route::put('/doctor/prescriptions/{prescription}', [PrescriptionsDoctorController::class, 'update'])->name('doctor.prescriptions.update');

    Route::delete('/doctor/prescriptions/{prescription}', [PrescriptionsDoctorController::class, 'destroy'])->name('doctor.prescriptions.destroy');
});
