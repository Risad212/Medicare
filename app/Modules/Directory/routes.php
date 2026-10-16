<?php

use App\Modules\Directory\Http\Controllers\Admin\DepartmentController as DirectoryDepartmentController;
use App\Modules\Directory\Http\Controllers\Admin\ServiceController as DirectoryAdminServiceController;
use App\Modules\Directory\Http\Controllers\Admin\SliderController as DirectorySliderController;
use App\Modules\Directory\Http\Controllers\Frontend\ServiceController as DirectoryServiceController;
use App\Modules\Directory\Http\Controllers\Settings\AppointmentSettingController as DirectoryAppointmentSettingController;
use App\Modules\Directory\Http\Controllers\Settings\BlogSettingController as DirectoryBlogSettingController;
use App\Modules\Directory\Http\Controllers\Settings\ContactSettingController as DirectoryContactSettingController;
use App\Modules\Directory\Http\Controllers\Settings\DoctorSettingController as DirectoryDoctorSettingController;
use App\Modules\Directory\Http\Controllers\Settings\ServiceSettingController as DirectoryServiceSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin', 'module:directory', 'staff.modules'])->group(function () {
    Route::get('/admin/settings/service', [DirectoryServiceSettingController::class, 'service'])->name('settings.service');

    Route::post('/admin/settings/service', [DirectoryServiceSettingController::class, 'update'])->name('settings.service.update');

    Route::get('/admin/settings/doctor', [DirectoryDoctorSettingController::class, 'doctor'])->name('settings.doctor');

    Route::get('/admin/settings/blog', [DirectoryBlogSettingController::class, 'blog'])->name('settings.blog');

    Route::get('/admin/settings/contact', [DirectoryContactSettingController::class, 'contact'])->name('settings.contact');

    Route::get('/admin/settings/appointment', [DirectoryAppointmentSettingController::class, 'appointment'])->name('settings.appointment');

    Route::resource('/admin/sliders', DirectorySliderController::class)->names('admin.sliders')->except(['show']);

    Route::resource('/admin/departments', DirectoryDepartmentController::class)->names('admin.departments')->except(['show']);

    Route::resource('/admin/services', DirectoryAdminServiceController::class)->names('admin.services')->except(['show']);
});

Route::middleware(['web', 'module:directory'])->group(function () {
    Route::get('/service', [DirectoryServiceController::class, 'index'])->name('service');

    Route::get('/service/{service:slug}', [DirectoryServiceController::class, 'show'])->name('service.show');
});
