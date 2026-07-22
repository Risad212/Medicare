<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;

use App\Http\Controllers\Settings\GeneralSettingController;
use App\Http\Controllers\Settings\HomeSettingController;
use App\Http\Controllers\Settings\AboutSettingController;
use App\Http\Controllers\Settings\ServiceSettingController;
use App\Http\Controllers\Settings\DoctorSettingController;
use App\Http\Controllers\Settings\BlogSettingController;
use App\Http\Controllers\Settings\ContactSettingController;

use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\CategoryController as AdminBlogCategoryController;
use App\Http\Controllers\Admin\TagController as AdminBlogTagController;
use App\Http\Controllers\Admin\BlogCommentController as AdminBlogCommentController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\TimeSlotController;



use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\BlogController as FrontBlogController;
use App\Http\Controllers\Frontend\DoctorController as FrontendDoctorController;
use App\Http\Controllers\Frontend\AppointmentController as FrontAppointmentController;
use App\Http\Controllers\Frontend\BlogCommentController;
use App\Http\Controllers\Frontend\ContactController as FrontContactController;


/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/service', function () {return view('frontend.service');})->name('service');

Route::get('/doctor', [FrontendDoctorController::class, 'index'])->name('doctor');

Route::get('/doctor/{id}', [FrontendDoctorController::class, 'show'])->name('doctor.show');

Route::get('/blog', [FrontBlogController::class, 'index'])->name('blog');

Route::get('/blog/{slug}', [FrontBlogController::class, 'show'])->name('blog.show');

Route::get('/contact', [FrontContactController::class, 'index'])->name('contact');

Route::get('/appointment', [FrontAppointmentController::class, 'index'])->name('appointment');

Route::post('/appointment', [FrontAppointmentController::class, 'store'])->name('appointment.store');

Route::post('/blog/{blog_id}/comment', [BlogCommentController::class, 'store'])->name('blog.comment.store');

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
| About Settings Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/settings/about', [AboutSettingController::class, 'about'])
    ->name('settings.about');

/*
|--------------------------------------------------------------------------
| Service Settings Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/settings/service', [ServiceSettingController::class, 'service'])
    ->name('settings.service');

/*
|--------------------------------------------------------------------------
| Doctor Settings Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/settings/doctor', [DoctorSettingController::class, 'doctor'])
    ->name('settings.doctor');

/*
|--------------------------------------------------------------------------
| Blog Settings Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/settings/blog', [BlogSettingController::class, 'blog'])
    ->name('settings.blog');

/*
|--------------------------------------------------------------------------
| Contact Settings Routes
|--------------------------------------------------------------------------
*/
Route::get('/admin/settings/contact', [ContactSettingController::class, 'contact'])
    ->name('settings.contact');

/*
|--------------------------------------------------------------------------
| Seo Settings Routes
|--------------------------------------------------------------------------
*/
Route::post('/admin/seo-settings', [SeoSettingController::class, 'update'])
    ->name('admin.seo-settings.update');


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
Route::resource('/admin/doctors', AdminDoctorController::class)->names('admin.doctors');


/*
|--------------------------------------------------------------------------
| Blogs Routes
|--------------------------------------------------------------------------
*/
Route::resource('/admin/blogs', AdminBlogController::class)->names('admin.blogs');


/*
|--------------------------------------------------------------------------
| Blog Category Routes
|--------------------------------------------------------------------------
*/
Route::resource('/admin/blog/categories', AdminBlogCategoryController::class)->names('admin.categories');


/*
|--------------------------------------------------------------------------
| Blog Tag Routes
|--------------------------------------------------------------------------
*/
Route::resource('/admin/blog/tags', AdminBlogTagController::class)->names('admin.tags');


/*
|--------------------------------------------------------------------------
| Blog Comment Routes Admin
|--------------------------------------------------------------------------
*/
Route::resource('/admin/comments', AdminBlogCommentController::class)
    ->names('admin.comments')
    ->only(['index', 'update', 'destroy']);

/*
|--------------------------------------------------------------------------
| Appointments Routes
|--------------------------------------------------------------------------
*/
Route::resource('/admin/appointments', AdminAppointmentController::class)->names('admin.appointments');


/*
|--------------------------------------------------------------------------
| Time Slot Routes
|--------------------------------------------------------------------------
*/
Route::resource('/admin/time-slots', TimeSlotController::class)->names('admin.time-slots');


/*
|--------------------------------------------------------------------------
| Mail Routes
|--------------------------------------------------------------------------
*/
Route::post('/contact-submit', [FrontContactController::class, 'store'])
    ->name('contact.submit');
