<?php

use App\Modules\BloodBank\Http\Controllers\Admin\BloodBankController;
use App\Modules\BloodBank\Http\Controllers\Admin\BloodDonationController as AdminBloodDonationController;
use App\Modules\BloodBank\Http\Controllers\Admin\BloodDonorController as AdminBloodDonorController;
use App\Modules\BloodBank\Http\Controllers\Admin\BloodGroupController as AdminBloodGroupController;
use App\Modules\BloodBank\Http\Controllers\Admin\BloodIssueController as AdminBloodIssueController;
use App\Modules\BloodBank\Http\Controllers\Admin\BloodReportController as AdminBloodReportController;
use App\Modules\BloodBank\Http\Controllers\Admin\BloodRequestController as AdminBloodRequestController;
use App\Modules\BloodBank\Http\Controllers\Doctor\BloodRequestController as DoctorBloodRequestController;
use App\Modules\BloodBank\Http\Controllers\Frontend\BloodRequestController as FrontendBloodRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:bloodbank', 'staff.modules'])->group(function () {
    Route::get('/admin/bloodbank', [BloodBankController::class, 'dashboard'])->name('admin.bloodbank.dashboard');
    Route::get('/admin/bloodbank/inventory', [BloodBankController::class, 'inventory'])->name('admin.bloodbank.inventory');
    Route::patch('/admin/bloodbank/settings', [BloodBankController::class, 'updateSettings'])->name('admin.bloodbank.settings.update');

    Route::resource('/admin/blood-groups', AdminBloodGroupController::class)
        ->names('admin.blood-groups')
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('/admin/blood-groups/{bloodGroup}/toggle', [AdminBloodGroupController::class, 'toggle'])->name('admin.blood-groups.toggle');

    Route::resource('/admin/blood-donors', AdminBloodDonorController::class)
        ->names('admin.blood-donors')
        ->parameters(['blood-donors' => 'donor']);

    Route::resource('/admin/blood-donations', AdminBloodDonationController::class)
        ->names('admin.blood-donations')
        ->parameters(['blood-donations' => 'donation']);
    Route::patch('/admin/blood-donations/{donation}/status', [AdminBloodDonationController::class, 'updateStatus'])->name('admin.blood-donations.status');

    Route::get('/admin/blood-requests', [AdminBloodRequestController::class, 'index'])->name('admin.blood-requests.index');
    Route::get('/admin/blood-requests/create', [AdminBloodRequestController::class, 'create'])->name('admin.blood-requests.create');
    Route::post('/admin/blood-requests', [AdminBloodRequestController::class, 'store'])->name('admin.blood-requests.store');
    Route::get('/admin/blood-requests/{bloodRequest}', [AdminBloodRequestController::class, 'show'])->name('admin.blood-requests.show');
    Route::post('/admin/blood-requests/{bloodRequest}/approve', [AdminBloodRequestController::class, 'approve'])->name('admin.blood-requests.approve');
    Route::post('/admin/blood-requests/{bloodRequest}/reject', [AdminBloodRequestController::class, 'reject'])->name('admin.blood-requests.reject');
    Route::post('/admin/blood-requests/{bloodRequest}/cancel', [AdminBloodRequestController::class, 'cancel'])->name('admin.blood-requests.cancel');
    Route::delete('/admin/blood-requests/{bloodRequest}', [AdminBloodRequestController::class, 'destroy'])->name('admin.blood-requests.destroy');

    Route::get('/admin/blood-issues', [AdminBloodIssueController::class, 'index'])->name('admin.blood-issues.index');
    Route::get('/admin/blood-issues/create/{bloodRequest}', [AdminBloodIssueController::class, 'create'])->name('admin.blood-issues.create');
    Route::post('/admin/blood-issues/{bloodRequest}', [AdminBloodIssueController::class, 'store'])->name('admin.blood-issues.store');
    Route::get('/admin/blood-issues/{bloodIssue}', [AdminBloodIssueController::class, 'show'])->name('admin.blood-issues.show');

    Route::get('/admin/blood-reports', [AdminBloodReportController::class, 'index'])->name('admin.bloodbank.reports');
    Route::get('/admin/blood-reports/export/donations', [AdminBloodReportController::class, 'exportDonations'])->name('admin.bloodbank.reports.donations');
    Route::get('/admin/blood-reports/export/requests', [AdminBloodReportController::class, 'exportRequests'])->name('admin.bloodbank.reports.requests');
    Route::get('/admin/blood-reports/export/issues', [AdminBloodReportController::class, 'exportIssues'])->name('admin.bloodbank.reports.issues');
});

Route::middleware(['web', 'auth', 'doctor', 'module:bloodbank'])->group(function () {
    Route::get('/doctor/blood-requests', [DoctorBloodRequestController::class, 'index'])->name('doctor.blood-requests.index');

    Route::get('/doctor/blood-requests/create', [DoctorBloodRequestController::class, 'create'])->name('doctor.blood-requests.create');

    Route::post('/doctor/blood-requests', [DoctorBloodRequestController::class, 'store'])->name('doctor.blood-requests.store');

    Route::get('/doctor/blood-requests/{bloodRequest}', [DoctorBloodRequestController::class, 'show'])->name('doctor.blood-requests.show');
});

Route::middleware(['web', 'auth', 'patient', 'module:bloodbank'])->group(function () {
    Route::get('/profile/blood-requests', [FrontendBloodRequestController::class, 'index'])->name('profile.blood-requests');
});
