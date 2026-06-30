<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;

use App\Http\Controllers\Settings\GeneralSettingController;
use App\Http\Controllers\Settings\HomeSettingController;

use App\Http\Controllers\Frontend\HomeController;

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/


Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', function () {
    return view('frontend.about');
})->name('about');

Route::get('/service', function () {
    return view('frontend.service');
})->name('service');

Route::get('/doctor', function () {
    return view('frontend.doctor');
})->name('doctor');

Route::get('/blog', function () {
    return view('frontend.blog');
})->name('blog');

Route::get('/contact', function () {
    return view('frontend.contact');
})->name('contact');

Route::get('/appoinment', function () {
    return view('frontend.appoinment');
})->name('appoinment');

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
    ->name('settings.update');

/*
|--------------------------------------------------------------------------
| Home Settings Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/settings/home', [HomeSettingController::class, 'home'])
    ->name('settings.home');

Route::post('/admin/settings/home', [HomeSettingController::class, 'update'])
    ->name('settings.update');


