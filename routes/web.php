<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;

use App\Http\Controllers\Settings\GeneralSettingController;
use App\Http\Controllers\Settings\HomeSettingController;

use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;

use App\Http\Controllers\Frontend\DoctorController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\AppointmentController as FrontAppointmentController;



/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/service', function () {
    return view('frontend.service');
})->name('service');

Route::get('/doctor', function () {
    return view('frontend.doctor');
})->name('doctor');

Route::get('/doctor/{id}', [DoctorController::class, 'show'])->name('doctor.show');

Route::get('/blog', function () {
    return view('frontend.blog');
})->name('blog');

Route::get('/contact', function () {
    return view('frontend.contact');
})->name('contact');

Route::get('/appointment', function () {
    return view('frontend.appointment');
})->name('appointment');

Route::post('/appointment', [FrontAppointmentController::class, 'store'])
    ->name('appointment.store');

/*
|--------------------------------------------------------------------------
| Backend / Admin Routes
|--------------------------------------------------------------------------
*/

Auth::routes();

Route::get('/admin', [AdminController::class, 'index'])
    ->name('admin.home');


/*
|--------------------------------------------------------------------------
| General Settings Routes
|--------------------------------------------------------------------------
*/

Route::get('/admin/settings/general', [GeneralSettingController::class, 'general'])
    ->name('settings.general');

Route::post('/admin/settings/general', [GeneralSettingController::class, 'update'])
    ->name('settings.general.update');

/*
|--------------------------------------------------------------------------
| Home Settings Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/settings/home', [HomeSettingController::class, 'home'])
    ->name('settings.home');

Route::post('/admin/settings/home', [HomeSettingController::class, 'update'])
    ->name('settings.home.update');


/*
|--------------------------------------------------------------------------
| Slider Routes
|--------------------------------------------------------------------------
*/
Route::resource('/admin/sliders', SliderController::class)->names('admin.sliders');


/*
|--------------------------------------------------------------------------
| Doctors Routes
|--------------------------------------------------------------------------
*/
Route::resource('/admin/doctors', DoctorController::class)->names('admin.doctors');


/*
|--------------------------------------------------------------------------
| Appoinments Routes
|--------------------------------------------------------------------------
*/
Route::resource('/admin/appointments', AdminAppointmentController::class)->names('admin.appointments');