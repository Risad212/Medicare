<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminPageController;


Route::get('/', function () {
    return view('frontend.index');
})->name('home');;

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

// ================ Start Backend Route ================
Auth::routes();
Route::get('/admin', [HomeController::class, 'index'])->name('home');

/**
 *  Starting Frontend CMS Route
 */
Route::get('/admin/pages/home',    [AdminPageController::class, 'index'])->name('admin.home');
Route::get('/admin/pages/about',   [AdminPageController::class, 'about'])->name('admin.about');
Route::get('/admin/pages/service',[AdminPageController::class, 'service'])->name('admin.service');
Route::get('/admin/pages/doctor', [AdminPageController::class, 'doctor'])->name('admin.doctor');
Route::get('/admin/pages/blog',    [AdminPageController::class, 'blog'])->name('admin.blog');
Route::get('/admin/pages/contact', [AdminPageController::class, 'contact'])->name('admin.contact');



