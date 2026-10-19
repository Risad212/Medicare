<?php

use App\Modules\Reviews\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Modules\Reviews\Http\Controllers\Frontend\ReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:reviews', 'staff.modules'])->group(function () {
    Route::resource('/admin/doctor-reviews', AdminReviewController::class)
        ->names('admin.doctor-reviews')
        ->only(['index', 'update', 'destroy']);
});

Route::middleware(['web', 'auth', 'patient', 'module:reviews'])->group(function () {
    Route::post('/profile/appointments/{appointment}/review', [ReviewController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('profile.appointments.review');
});
