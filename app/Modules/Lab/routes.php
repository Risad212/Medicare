<?php

use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Modules\Lab\Http\Controllers\Admin\LabOrderController as AdminLabOrderController;
use App\Modules\Lab\Http\Controllers\Admin\LabTestController;
use App\Modules\Lab\Http\Controllers\Doctor\LabOrderController as DoctorLabOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:lab', 'staff.modules'])->group(function () {
    Route::resource('/admin/lab-tests', LabTestController::class)->names('admin.lab-tests')->except(['show']);

    Route::get('/admin/lab-orders', [AdminLabOrderController::class, 'index'])->name('admin.lab-orders.index');

    Route::get('/admin/lab-orders/{order}', [AdminLabOrderController::class, 'show'])->name('admin.lab-orders.show');

    Route::put('/admin/lab-orders/{order}/status', [AdminLabOrderController::class, 'updateStatus'])->name('admin.lab-orders.status');

    Route::post('/admin/lab-orders/{order}/reports', [AdminLabOrderController::class, 'storeReport'])->name('admin.lab-orders.reports.store');

    Route::get('/admin/lab-orders/{order}/pdf', [AdminLabOrderController::class, 'downloadPdf'])->name('admin.lab-orders.pdf');

    Route::put('/admin/lab-order-items/{item}/result', [AdminLabOrderController::class, 'updateItemResult'])->name('admin.lab-order-items.result');

    Route::delete('/admin/lab-reports/{report}', [AdminLabOrderController::class, 'destroyReport'])->name('admin.lab-reports.destroy');

    Route::get('/admin/lab-reports/{report}/download', [AdminLabOrderController::class, 'downloadReport'])->name('admin.lab-reports.download');

    Route::get('/admin/exports/lab-orders', [AdminExportController::class, 'labOrders'])->name('admin.exports.lab-orders');

    Route::post('/admin/lab-orders/{order}/invoice', [AdminInvoiceController::class, 'createFromOrder'])->name('admin.invoices.create-from-order');
});

Route::middleware(['web', 'auth', 'doctor', 'module:lab'])->group(function () {
    Route::get('/doctor/lab-orders', [DoctorLabOrderController::class, 'index'])->name('doctor.lab-orders.index');

    Route::get('/doctor/lab-orders/create', [DoctorLabOrderController::class, 'create'])->name('doctor.lab-orders.create');

    Route::post('/doctor/lab-orders', [DoctorLabOrderController::class, 'store'])->name('doctor.lab-orders.store');

    Route::get('/doctor/lab-orders/{order}', [DoctorLabOrderController::class, 'show'])->name('doctor.lab-orders.show');
});
