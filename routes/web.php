<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\BlogCommentController as AdminBlogCommentController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\CategoryController as AdminBlogCategoryController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\PatientController as AdminPatientController;
use App\Http\Controllers\Admin\PrescriptionController as AdminPrescriptionController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\TagController as AdminBlogTagController;
use App\Http\Controllers\Admin\TimeSlotController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController as AuthLoginController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorProfileController as DoctorDashboardProfileController;
use App\Http\Controllers\Doctor\PrescriptionController as DoctorPrescriptionController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\AppointmentController as FrontAppointmentController;
use App\Http\Controllers\Frontend\BlogCommentController;
use App\Http\Controllers\Frontend\BlogController as FrontBlogController;
use App\Http\Controllers\Frontend\ContactController as FrontContactController;
use App\Http\Controllers\Frontend\DoctorController as FrontendDoctorController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProfileController as FrontProfileController;
use App\Http\Controllers\Frontend\ServiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Settings\AboutSettingController;
use App\Http\Controllers\Settings\AppointmentSettingController;
use App\Http\Controllers\Settings\BlogSettingController;
use App\Http\Controllers\Settings\ContactSettingController;
use App\Http\Controllers\Settings\DoctorSettingController;
use App\Http\Controllers\Settings\GeneralSettingController;
use App\Http\Controllers\Settings\HomeSettingController;
use App\Http\Controllers\Settings\ServiceSettingController;
use App\Support\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backend / Admin Routes
|--------------------------------------------------------------------------
*/

Auth::routes(['verify' => false, 'login' => false]);

// Login wired manually so the POST carries a brute-force throttle when the
// lockout module is on (boot-time flag; reboot after changing it).
// The AuthenticatesUsers trait still applies its own per-account lockout
// (5 attempts, per username+IP) underneath this per-IP rate limit, gated by
// LoginController::hasTooManyLoginAttempts() at runtime.
Route::get('/login', [AuthLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthLoginController::class, 'login'])
    ->middleware(...(Module::enabled('lockout') ? ['throttle:5,1'] : []))
    ->name('login.attempt');

// Google OAuth for patients (also links existing accounts)
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->middleware('throttle:10,1')->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:10,1')->name('auth.google.callback');

Route::middleware(['auth', 'admin', 'staff.modules'])->group(function () {

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.home');

    /*
    |--------------------------------------------------------------------------
    | General Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/general', [GeneralSettingController::class, 'general'])->name('settings.general');

    Route::post('/admin/settings/general', [GeneralSettingController::class, 'update'])->name('settings.general.update');

    /*
    |--------------------------------------------------------------------------
    | Home Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/home', [HomeSettingController::class, 'home'])->name('settings.home');

    Route::post('/admin/settings/home', [HomeSettingController::class, 'update'])->name('settings.home.update');

    /*
    |--------------------------------------------------------------------------
    | About Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/about', [AboutSettingController::class, 'about'])->name('settings.about');

    Route::post('/admin/settings/about', [AboutSettingController::class, 'update'])->name('settings.about.update');

    /*
    |--------------------------------------------------------------------------
    | Service Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/service', [ServiceSettingController::class, 'service'])->name('settings.service');

    Route::post('/admin/settings/service', [ServiceSettingController::class, 'update'])->name('settings.service.update');

    /*
    |--------------------------------------------------------------------------
    | Doctor Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/doctor', [DoctorSettingController::class, 'doctor'])->name('settings.doctor');

    /*
    |--------------------------------------------------------------------------
    | Blog Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/blog', [BlogSettingController::class, 'blog'])->name('settings.blog');

    /*
    |--------------------------------------------------------------------------
    | Contact Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/contact', [ContactSettingController::class, 'contact'])->name('settings.contact');

    /*
    |--------------------------------------------------------------------------
    | Appointment Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/appointment', [AppointmentSettingController::class, 'appointment'])->name('settings.appointment');

    /*
    |--------------------------------------------------------------------------
    | SEO Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/admin/seo-settings', [SeoSettingController::class, 'update'])->name('admin.seo-settings.update');

    /*
    |--------------------------------------------------------------------------
    | Slider Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/sliders', SliderController::class)->names('admin.sliders')->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Doctors Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/doctors', AdminDoctorController::class)->names('admin.doctors')->except(['show']);

    Route::get('/admin/doctors/{doctor}/availability', [AdminDoctorController::class, 'availability'])->name('admin.doctors.availability');
    Route::post('/admin/doctors/{doctor}/availability', [AdminDoctorController::class, 'updateAvailability'])->name('admin.doctors.availability.update');
    Route::post('/admin/doctors/{doctor}/off-days', [AdminDoctorController::class, 'storeOffDay'])->name('admin.doctors.off-days.store');
    Route::delete('/admin/doctor-off-days/{offDay}', [AdminDoctorController::class, 'destroyOffDay'])->name('admin.doctor-off-days.destroy');

    /*
    |--------------------------------------------------------------------------
    | Department Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/departments', AdminDepartmentController::class)->names('admin.departments')->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Service Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/services', AdminServiceController::class)->names('admin.services')->except(['show']);

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
    Route::resource('/admin/blog/categories', AdminBlogCategoryController::class)->names('admin.categories')->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Blog Tag Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/blog/tags', AdminBlogTagController::class)->names('admin.tags')->except(['show']);

    /*
    |--------------------------------------------------------------------------
    | Blog Comment Routes Admin
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/comments', AdminBlogCommentController::class)
        ->names('admin.comments')
        ->only(['index', 'update', 'destroy']);

    // Languages module routes live in app/Modules/Language/routes.php.

    /*
    |--------------------------------------------------------------------------
    | Appointments Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/appointments', AdminAppointmentController::class)->names('admin.appointments')->except(['show']);
    Route::patch('/admin/appointments/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->name('admin.appointments.status');

    /*
    |--------------------------------------------------------------------------
    | Patients Routes
    |--------------------------------------------------------------------------
    */

    Route::resource('/admin/patients', AdminPatientController::class)
        ->names('admin.patients');

    // Vaccination admin routes live in the module: app/Modules/Vaccination/routes.php
    // (registered by VaccinationServiceProvider only when enabled).

    // Ambulance routes live in the module: app/Modules/Ambulance/routes.php
    // (registered by AmbulanceServiceProvider only when enabled).

    // Beds routes live in the module: app/Modules/Beds/routes.php
    // (registered by BedsServiceProvider only when enabled).

    // Pharmacy routes live in the module: app/Modules/Pharmacy/routes.php
    // (registered by PharmacyServiceProvider only when enabled).

    /*
    |--------------------------------------------------------------------------
    | Time Slot Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/time-slots', TimeSlotController::class)->names('admin.time-slots')->except(['show']);

    // Laboratory routes live in the module: app/Modules/Lab/routes.php
    // (registered by LabServiceProvider only when enabled).

    /*
    |--------------------------------------------------------------------------
    | Activity Logs Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/activity-logs', [AdminActivityLogController::class, 'index'])->name('admin.activity-logs.index');

    /*
    |--------------------------------------------------------------------------
    | Access Control Routes (Roles & Permissions)
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');

    /*
    |--------------------------------------------------------------------------
    | Export Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/exports/appointments', [AdminExportController::class, 'appointments'])->name('admin.exports.appointments');

    Route::get('/admin/exports/patients', [AdminExportController::class, 'patients'])->name('admin.exports.patients');

    /*
    |--------------------------------------------------------------------------
    | Invoices Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/invoices', [AdminInvoiceController::class, 'index'])->name('admin.invoices.index');
    Route::get('/admin/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('admin.invoices.show');
    Route::get('/admin/invoices/{invoice}/pdf', [AdminInvoiceController::class, 'download'])->name('admin.invoices.pdf');
    Route::patch('/admin/invoices/{invoice}/status', [AdminInvoiceController::class, 'updateStatus'])->name('admin.invoices.status');
    Route::delete('/admin/invoices/{invoice}', [AdminInvoiceController::class, 'destroy'])->name('admin.invoices.destroy');

    /*
    |--------------------------------------------------------------------------
    | Prescriptions Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/prescriptions', [AdminPrescriptionController::class, 'index'])->name('admin.prescriptions.index');

    Route::get('/admin/prescriptions/{prescription}', [AdminPrescriptionController::class, 'show'])->name('admin.prescriptions.show');

    Route::get('/admin/prescriptions/{prescription}/pdf', [AdminPrescriptionController::class, 'pdf'])->name('admin.prescriptions.pdf');

    Route::delete('/admin/prescriptions/{prescription}', [AdminPrescriptionController::class, 'destroy'])->name('admin.prescriptions.destroy');

    // Blood bank routes live in the module: app/Modules/BloodBank/routes.php
    // (registered by BloodBankServiceProvider only when enabled).

}); // end admin group

// Public contact form - throttle to prevent spam (was incorrectly inside admin middleware)
Route::post('/contact-submit', [FrontContactController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('contact.submit');

Route::middleware(['auth', 'doctor'])->group(function () {

    Route::get('/doctor/dashboard', [DoctorDashboardController::class, 'index'])->name('doctor.dashboard');

    Route::get('/doctor/profile', [DoctorDashboardProfileController::class, 'edit'])->name('doctor.profile.edit');

    Route::put('/doctor/profile', [DoctorDashboardProfileController::class, 'update'])->name('doctor.profile.update');

    Route::get('/doctor/appointments', [DoctorDashboardController::class, 'appointments'])->name('doctor.appointments');

    Route::put('/doctor/appointments/{appointment}', [DoctorDashboardController::class, 'updateStatus'])->name('doctor.appointments.update');

    // Doctor lab order routes live in the module: app/Modules/Lab/routes.php
    // (registered by LabServiceProvider only when enabled).

    // Doctor blood request routes live in the module: app/Modules/BloodBank/routes.php
    // (registered by BloodBankServiceProvider only when enabled).

    Route::get('/doctor/prescriptions', [DoctorPrescriptionController::class, 'index'])->name('doctor.prescriptions.index');

    Route::get('/doctor/prescriptions/create', [DoctorPrescriptionController::class, 'create'])->name('doctor.prescriptions.create');

    Route::post('/doctor/prescriptions', [DoctorPrescriptionController::class, 'store'])->name('doctor.prescriptions.store');

    Route::get('/doctor/prescriptions/{prescription}', [DoctorPrescriptionController::class, 'show'])->name('doctor.prescriptions.show');

    Route::get('/doctor/prescriptions/{prescription}/edit', [DoctorPrescriptionController::class, 'edit'])->name('doctor.prescriptions.edit');

    Route::get('/doctor/prescriptions/{prescription}/pdf', [DoctorPrescriptionController::class, 'pdf'])->name('doctor.prescriptions.pdf');

    Route::put('/doctor/prescriptions/{prescription}', [DoctorPrescriptionController::class, 'update'])->name('doctor.prescriptions.update');

    Route::delete('/doctor/prescriptions/{prescription}', [DoctorPrescriptionController::class, 'destroy'])->name('doctor.prescriptions.destroy');

    // Doctor vaccination routes live in the module: app/Modules/Vaccination/routes.php
    // (registered by VaccinationServiceProvider only when enabled).

});

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/service', [ServiceController::class, 'index'])->name('service');

Route::get('/service/{service:slug}', [ServiceController::class, 'show'])->name('service.show');

Route::get('/doctor', [FrontendDoctorController::class, 'index'])->name('doctor');

Route::get('/doctor/{id}', [FrontendDoctorController::class, 'show'])->name('doctor.show');

Route::get('/blog', [FrontBlogController::class, 'index'])->name('blog');

Route::get('/blog/{slug}', [FrontBlogController::class, 'show'])->name('blog.show');

Route::get('/contact', [FrontContactController::class, 'index'])->name('contact');

Route::get('/appointment', [FrontAppointmentController::class, 'index'])->name('appointment');

Route::post('/appointment', [FrontAppointmentController::class, 'store'])->middleware('throttle:10,1')->name('appointment.store');

Route::get('/get-available-slots', [FrontAppointmentController::class, 'getAvailableSlots'])->middleware('throttle:60,1')->name('get.slots');

Route::post('/blog/{blog_id}/comment', [BlogCommentController::class, 'store'])->middleware('throttle:10,1')->name('blog.comment.store');

Route::middleware('auth')->group(function () {

    // Patient-only frontend profile (doctors use /doctor/profile).
    Route::middleware('patient')->group(function () {

        Route::get('/profile', [FrontProfileController::class, 'index'])->name('profile');

        Route::put('/profile', [FrontProfileController::class, 'update'])->name('profile.update');

        Route::get('/profile/lab-reports/{report}/download', [FrontProfileController::class, 'downloadReport'])->name('profile.lab-reports.download');

        Route::get('/profile/lab-orders/{order}/pdf', [FrontProfileController::class, 'downloadOrderPdf'])->name('profile.lab-orders.pdf');

        Route::get('/profile/prescriptions/{prescription}/pdf', [FrontProfileController::class, 'downloadPrescriptionPdf'])->name('profile.prescriptions.pdf');

    });

    Route::patch('/appointment/{appointment}/cancel', [FrontAppointmentController::class, 'cancel'])->middleware('auth')->name('appointment.cancel');

    // In-app notifications (staff only — guarded in the controller).
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->middleware('throttle:10,1')
        ->name('notifications.read-all');

});
Route::get('/appointment/cancel/{token}', [FrontAppointmentController::class, 'showCancelByToken'])
    ->middleware('throttle:10,1')
    ->name('appointment.cancel-page');

Route::put('/appointment/cancel/{token}', [FrontAppointmentController::class, 'cancelByToken'])
    ->middleware('throttle:10,1')
    ->name('appointment.cancel-by-token');
