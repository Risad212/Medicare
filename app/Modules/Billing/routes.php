<?php

use App\Modules\Billing\Http\Controllers\Admin\InvoiceController as BillingInvoiceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:billing', 'staff.modules'])->group(function () {
    Route::get('/admin/invoices', [BillingInvoiceController::class, 'index'])->name('admin.invoices.index');

    Route::get('/admin/invoices/{invoice}', [BillingInvoiceController::class, 'show'])->name('admin.invoices.show');

    Route::get('/admin/invoices/{invoice}/pdf', [BillingInvoiceController::class, 'download'])->name('admin.invoices.pdf');

    Route::patch('/admin/invoices/{invoice}/status', [BillingInvoiceController::class, 'updateStatus'])->name('admin.invoices.status');

    Route::delete('/admin/invoices/{invoice}', [BillingInvoiceController::class, 'destroy'])->name('admin.invoices.destroy');
});
