<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SettingController;


/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('frontend.index');
})->name('home');

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

Route::get('/admin', [HomeController::class, 'index'])
    ->name('admin.home');


/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/

Route::get('/admin/settings/general', [SettingController::class, 'general'])
    ->name('settings.general');

Route::post('/admin/settings/general', [SettingController::class, 'update'])
    ->name('settings.update');