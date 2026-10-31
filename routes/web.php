<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\PatientController as AdminPatientController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\TimeSlotController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController as AuthLoginController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorProfileController as DoctorDashboardProfileController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\AppointmentController as FrontAppointmentController;
use App\Http\Controllers\Frontend\ContactController as FrontContactController;
use App\Http\Controllers\Frontend\DoctorController as FrontendDoctorController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProfileController as FrontProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientPickerController;
use App\Http\Controllers\Settings\AboutSettingController;
use App\Http\Controllers\Settings\GeneralSettingController;
use App\Http\Controllers\Settings\HomeSettingController;
use App\Support\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backend / Admin Routes
|--------------------------------------------------------------------------
*/

// Auth scaffolding (verification off, custom login below). Wrapped with a
// per-IP throttle so password reset/forgot/register can't be hammered.
Route::middleware(app()->environment('local','testing') ? [] : ['throttle:10,1'])->group(function () {
    Auth::routes(['verify' => true, 'login' => false]);
});

// Login wired manually so the POST carries a brute-force throttle when the
// lockout module is on (boot-time flag; reboot after changing it).
// The AuthenticatesUsers trait still applies its own per-account lockout
// (5 attempts, per username+IP) underneath this per-IP rate limit, gated by
// LoginController::hasTooManyLoginAttempts() at runtime.
Route::get('/login', [AuthLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthLoginController::class, 'login'])
    ->middleware(app()->environment('local','testing') ? [] : (Module::enabled('lockout') ? ['throttle:5,1'] : []))
    ->name('login.attempt');

// Google OAuth for patients (also links existing accounts)
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->middleware(app()->environment('local','testing') ? [] : ['throttle:10,1'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware(app()->environment('local','testing') ? [] : ['throttle:10,1'])->name('auth.google.callback');

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

    // Directory module settings routes live in app/Modules/Directory/routes.php.

    /*
    |--------------------------------------------------------------------------
    | SEO Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/admin/seo-settings', [SeoSettingController::class, 'update'])->name('admin.seo-settings.update');

    // Directory module routes (sliders, departments, services) live in app/Modules/Directory/routes.php.

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

    // Publishing module routes live in app/Modules/Publishing/routes.php.

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

    // Typeahead pickers must be declared before the patients resource so that
    // "/admin/patients/picker" is not captured by "/admin/patients/{patient}".
    Route::get('/admin/patients/picker', [PatientPickerController::class, 'users'])
        ->middleware(app()->environment('local','testing') ? [] : ['throttle:60,1'])
        ->name('admin.patients.picker');

    Route::get('/admin/patient-records/picker', [PatientPickerController::class, 'records'])
        ->middleware(app()->environment('local','testing') ? [] : ['throttle:60,1'])
        ->name('admin.patient-records.picker');

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
    Route::get('/admin/exports/appointments', [AdminExportController::class, 'appointments'])->middleware(app()->environment('local','testing') ? [] : ['throttle:10,60'])->name('admin.exports.appointments');

    Route::get('/admin/exports/patients', [AdminExportController::class, 'patients'])->middleware(app()->environment('local','testing') ? [] : ['throttle:10,60'])->name('admin.exports.patients');

    // Billing module routes live in app/Modules/Billing/routes.php.

    // Prescriptions admin routes live in app/Modules/Prescriptions/routes.php.

    // Blood bank routes live in the module: app/Modules/BloodBank/routes.php
    // (registered by BloodBankServiceProvider only when enabled).

}); // end admin group

// Public contact form - throttle to prevent spam (was incorrectly inside admin middleware)
Route::post('/contact-submit', [FrontContactController::class, 'store'])
    ->middleware(app()->environment('local','testing') ? [] : ['throttle:10,1'])
    ->name('contact.submit');

Route::middleware(['auth', 'doctor'])->group(function () {

    Route::get('/doctor/dashboard', [DoctorDashboardController::class, 'index'])->name('doctor.dashboard');

    Route::get('/doctor/profile', [DoctorDashboardProfileController::class, 'edit'])->name('doctor.profile.edit');

    Route::put('/doctor/profile', [DoctorDashboardProfileController::class, 'update'])->name('doctor.profile.update');

    Route::get('/doctor/appointments', [DoctorDashboardController::class, 'appointments'])->name('doctor.appointments');

    Route::put('/doctor/appointments/{appointment}', [DoctorDashboardController::class, 'updateStatus'])->name('doctor.appointments.update');

    // Doctor prescription routes (incl. appointments picker) live in app/Modules/Prescriptions/routes.php.

    Route::get('/doctor/patients/picker', [PatientPickerController::class, 'users'])
        ->middleware(app()->environment('local','testing') ? [] : ['throttle:60,1'])
        ->name('doctor.patients.picker');

    Route::get('/doctor/patient-records/picker', [PatientPickerController::class, 'records'])
        ->middleware(app()->environment('local','testing') ? [] : ['throttle:60,1'])
        ->name('doctor.patient-records.picker');

    // Doctor lab order routes live in the module: app/Modules/Lab/routes.php
    // (registered by LabServiceProvider only when enabled).

    // Doctor blood request routes live in the module: app/Modules/BloodBank/routes.php
    // (registered by BloodBankServiceProvider only when enabled).

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

// Directory module public routes (/service) live in app/Modules/Directory/routes.php.

Route::get('/doctor', [FrontendDoctorController::class, 'index'])->name('doctor');

Route::get('/doctor/{id}', [FrontendDoctorController::class, 'show'])->name('doctor.show');

Route::get('/department/{name}', [FrontendDoctorController::class, 'department'])->name('department.show');

Route::get('/contact', [FrontContactController::class, 'index'])->name('contact');

Route::get('/appointment', [FrontAppointmentController::class, 'index'])->name('appointment');

Route::post('/appointment', [FrontAppointmentController::class, 'store'])->middleware(app()->environment('local','testing') ? [] : ['throttle:10,1'])->name('appointment.store');

Route::get('/get-available-slots', [FrontAppointmentController::class, 'getAvailableSlots'])->middleware(app()->environment('local','testing') ? [] : ['throttle:60,1'])->name('get.slots');

Route::middleware('auth')->group(function () {

    // Patient-only frontend profile (doctors use /doctor/profile).
    Route::middleware('patient')->group(function () {

        Route::get('/profile', [FrontProfileController::class, 'index'])->name('profile');

        Route::put('/profile', [FrontProfileController::class, 'update'])->name('profile.update');

        Route::get('/profile/lab-reports/{report}/download', [FrontProfileController::class, 'downloadReport'])->name('profile.lab-reports.download');

        Route::get('/profile/lab-orders/{order}/pdf', [FrontProfileController::class, 'downloadOrderPdf'])->name('profile.lab-orders.pdf');

        Route::get('/profile/prescriptions/{prescription}/pdf', [FrontProfileController::class, 'downloadPrescriptionPdf'])->name('profile.prescriptions.pdf');

        Route::delete('/profile/google', [GoogleAuthController::class, 'unlink'])->name('profile.google.unlink');

    });

    Route::patch('/appointment/{appointment}/cancel', [FrontAppointmentController::class, 'cancel'])->middleware('auth')->name('appointment.cancel');

    // In-app notifications (authenticated users — view chosen per role).
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->middleware(app()->environment('local','testing') ? [] : ['throttle:60,1'])
        ->name('notifications.index');

    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->middleware(app()->environment('local','testing') ? [] : ['throttle:120,1'])
        ->name('notifications.unread-count');

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->middleware(app()->environment('local','testing') ? [] : ['throttle:60,1'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->middleware(app()->environment('local','testing') ? [] : ['throttle:10,1'])
        ->name('notifications.read-all');

});
Route::get('/appointment/cancel/{token}', [FrontAppointmentController::class, 'showCancelByToken'])
    ->middleware(app()->environment('local','testing') ? [] : ['throttle:10,1'])
    ->name('appointment.cancel-page');

Route::put('/appointment/cancel/{token}', [FrontAppointmentController::class, 'cancelByToken'])
    ->middleware(app()->environment('local','testing') ? [] : ['throttle:10,1'])
    ->name('appointment.cancel-by-token');
