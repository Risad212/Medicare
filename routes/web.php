<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\PageSectionController;
use App\Http\Controllers\Frontend\PageController;

/**
 * Front End Page View Route
 */
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
 *  Admin Frontend Page CMS
 */
Route::get('/admin/pages/home',    [AdminPageController::class, 'about'])->name('admin.home');

/**
 * CMS CRUD
 */
Route::apiResource('page-sections', PageSectionController::class);

/**
 * CMS Frontend Data Read Route
 */
Route::get('/', [PageController::class, 'home']);



