<?php

use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\BlogCommentController as AdminBlogCommentController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\BloodBankController;
use App\Http\Controllers\Admin\BloodDonationController as AdminBloodDonationController;
use App\Http\Controllers\Admin\BloodDonorController as AdminBloodDonorController;
use App\Http\Controllers\Admin\BloodGroupController as AdminBloodGroupController;
use App\Http\Controllers\Admin\BloodIssueController as AdminBloodIssueController;
use App\Http\Controllers\Admin\BloodReportController as AdminBloodReportController;
use App\Http\Controllers\Admin\BloodRequestController as AdminBloodRequestController;
use App\Http\Controllers\Admin\CategoryController as AdminBlogCategoryController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\ExportController as AdminExportController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\LabOrderController as AdminLabOrderController;
use App\Http\Controllers\Admin\LabTestController;
use App\Http\Controllers\Admin\PatientController as AdminPatientController;
use App\Http\Controllers\Admin\PrescriptionController as AdminPrescriptionController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\TagController as AdminBlogTagController;
use App\Http\Controllers\Admin\TimeSlotController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Doctor\BloodRequestController as DoctorBloodRequestController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\DoctorProfileController as DoctorDashboardProfileController;
use App\Http\Controllers\Doctor\LabOrderController as DoctorLabOrderController;
use App\Http\Controllers\Doctor\PrescriptionController as DoctorPrescriptionController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\AppointmentController as FrontAppointmentController;
use App\Http\Controllers\Frontend\BlogCommentController;
use App\Http\Controllers\Frontend\BlogController as FrontBlogController;
use App\Http\Controllers\Frontend\BloodRequestController as FrontendBloodRequestController;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backend / Admin Routes
|--------------------------------------------------------------------------
*/

Auth::routes(['verify' => false]);

// Google OAuth for patients (also links existing accounts)
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->middleware('throttle:10,1')->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:10,1')->name('auth.google.callback');

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.home')->middleware('can:dashboard.view');

    /*
    |--------------------------------------------------------------------------
    | General Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/general', [GeneralSettingController::class, 'general'])->name('settings.general')->middleware('can:settings.manage');

    Route::post('/admin/settings/general', [GeneralSettingController::class, 'update'])->name('settings.general.update')->middleware('can:settings.manage');

    /*
    |--------------------------------------------------------------------------
    | Home Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/home', [HomeSettingController::class, 'home'])->name('settings.home')->middleware('can:settings.manage');

    Route::post('/admin/settings/home', [HomeSettingController::class, 'update'])->name('settings.home.update')->middleware('can:settings.manage');

    /*
    |--------------------------------------------------------------------------
    | About Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/about', [AboutSettingController::class, 'about'])->name('settings.about')->middleware('can:settings.manage');

    Route::post('/admin/settings/about', [AboutSettingController::class, 'update'])->name('settings.about.update')->middleware('can:settings.manage');

    /*
    |--------------------------------------------------------------------------
    | Service Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/service', [ServiceSettingController::class, 'service'])->name('settings.service')->middleware('can:settings.manage');

    Route::post('/admin/settings/service', [ServiceSettingController::class, 'update'])->name('settings.service.update')->middleware('can:settings.manage');

    /*
    |--------------------------------------------------------------------------
    | Doctor Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/doctor', [DoctorSettingController::class, 'doctor'])->name('settings.doctor')->middleware('can:settings.manage');

    /*
    |--------------------------------------------------------------------------
    | Blog Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/blog', [BlogSettingController::class, 'blog'])->name('settings.blog')->middleware('can:settings.manage');

    /*
    |--------------------------------------------------------------------------
    | Contact Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/contact', [ContactSettingController::class, 'contact'])->name('settings.contact')->middleware('can:settings.manage');

    /*
    |--------------------------------------------------------------------------
    | Appointment Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/settings/appointment', [AppointmentSettingController::class, 'appointment'])->name('settings.appointment')->middleware('can:settings.manage');

    /*
    |--------------------------------------------------------------------------
    | SEO Settings Routes
    |--------------------------------------------------------------------------
    */
    Route::post('/admin/seo-settings', [SeoSettingController::class, 'update'])->name('admin.seo-settings.update')->middleware('can:settings.manage');

    /*
    |--------------------------------------------------------------------------
    | Slider Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/sliders', SliderController::class)->names('admin.sliders')->except(['show'])->middleware('can:content.manage');

    /*
    |--------------------------------------------------------------------------
    | Doctors Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/doctors', AdminDoctorController::class)->names('admin.doctors')->except(['show'])->middleware('can:doctors.manage');

    Route::get('/admin/doctors/{doctor}/availability', [AdminDoctorController::class, 'availability'])->name('admin.doctors.availability')->middleware('can:doctors.manage');
    Route::post('/admin/doctors/{doctor}/availability', [AdminDoctorController::class, 'updateAvailability'])->name('admin.doctors.availability.update')->middleware('can:doctors.manage');
    Route::post('/admin/doctors/{doctor}/off-days', [AdminDoctorController::class, 'storeOffDay'])->name('admin.doctors.off-days.store')->middleware('can:doctors.manage');
    Route::delete('/admin/doctor-off-days/{offDay}', [AdminDoctorController::class, 'destroyOffDay'])->name('admin.doctor-off-days.destroy')->middleware('can:doctors.manage');

    /*
    |--------------------------------------------------------------------------
    | Department Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/departments', AdminDepartmentController::class)->names('admin.departments')->except(['show'])->middleware('can:content.manage');

    /*
    |--------------------------------------------------------------------------
    | Service Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/services', AdminServiceController::class)->names('admin.services')->except(['show'])->middleware('can:content.manage');

    /*
    |--------------------------------------------------------------------------
    | Blogs Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/blogs', AdminBlogController::class)->names('admin.blogs')->middleware('can:content.manage');

    /*
    |--------------------------------------------------------------------------
    | Blog Category Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/blog/categories', AdminBlogCategoryController::class)->names('admin.categories')->except(['show'])->middleware('can:content.manage');

    /*
    |--------------------------------------------------------------------------
    | Blog Tag Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/blog/tags', AdminBlogTagController::class)->names('admin.tags')->except(['show'])->middleware('can:content.manage');

    /*
    |--------------------------------------------------------------------------
    | Blog Comment Routes Admin
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/comments', AdminBlogCommentController::class)
        ->names('admin.comments')
        ->only(['index', 'update', 'destroy'])
        ->middleware('can:reviews.manage');

    /*
    |--------------------------------------------------------------------------
    | Appointments Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/appointments', AdminAppointmentController::class)->names('admin.appointments')->except(['show'])->middleware('can:appointments.manage');
    Route::patch('/admin/appointments/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->name('admin.appointments.status')->middleware('can:appointments.manage');

    /*
    |--------------------------------------------------------------------------
    | Patients Routes
    |--------------------------------------------------------------------------
    */

    Route::resource('/admin/patients', AdminPatientController::class)
        ->names('admin.patients')
        ->middleware('can:patients.manage');

    /*
    |--------------------------------------------------------------------------
    | Time Slot Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/time-slots', TimeSlotController::class)->names('admin.time-slots')->except(['show'])->middleware('can:appointments.manage');

    /*
    |--------------------------------------------------------------------------
    | Laboratory Tests Routes
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/lab-tests', LabTestController::class)->names('admin.lab-tests')->except(['show'])->middleware('can:lab.manage');

    /*
    |--------------------------------------------------------------------------
    | Laboratory Orders Routes (admin / lab staff)
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/lab-orders', [AdminLabOrderController::class, 'index'])->name('admin.lab-orders.index')->middleware('can:lab.manage');

    Route::get('/admin/lab-orders/{order}', [AdminLabOrderController::class, 'show'])->name('admin.lab-orders.show')->middleware('can:lab.manage');

    Route::put('/admin/lab-orders/{order}/status', [AdminLabOrderController::class, 'updateStatus'])->name('admin.lab-orders.status')->middleware('can:lab.manage');

    Route::post('/admin/lab-orders/{order}/reports', [AdminLabOrderController::class, 'storeReport'])->name('admin.lab-orders.reports.store')->middleware('can:lab.manage');

    Route::get('/admin/lab-orders/{order}/pdf', [AdminLabOrderController::class, 'downloadPdf'])->name('admin.lab-orders.pdf')->middleware('can:lab.manage');

    Route::put('/admin/lab-order-items/{item}/result', [AdminLabOrderController::class, 'updateItemResult'])->name('admin.lab-order-items.result')->middleware('can:lab.manage');

    Route::delete('/admin/lab-reports/{report}', [AdminLabOrderController::class, 'destroyReport'])->name('admin.lab-reports.destroy')->middleware('can:lab.manage');

    Route::get('/admin/lab-reports/{report}/download', [AdminLabOrderController::class, 'downloadReport'])->name('admin.lab-reports.download')->middleware('can:lab.manage');

    /*
    |--------------------------------------------------------------------------
    | Activity Logs Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/activity-logs', [AdminActivityLogController::class, 'index'])->name('admin.activity-logs.index')->middleware('can:activity-logs.view');

    /*
    |--------------------------------------------------------------------------
    | Access Control Routes (Roles & Permissions)
    |--------------------------------------------------------------------------
    */
    Route::resource('/admin/roles', AdminRoleController::class)->names('admin.roles')->except(['show'])->middleware('can:roles.manage');

    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index')->middleware('can:users.manage');
    Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit')->middleware('can:users.manage');
    Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update')->middleware('can:users.manage');

    /*
    |--------------------------------------------------------------------------
    | Export Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/exports/appointments', [AdminExportController::class, 'appointments'])->name('admin.exports.appointments')->middleware('can:exports.manage');

    Route::get('/admin/exports/patients', [AdminExportController::class, 'patients'])->name('admin.exports.patients')->middleware('can:exports.manage');

    Route::get('/admin/exports/lab-orders', [AdminExportController::class, 'labOrders'])->name('admin.exports.lab-orders')->middleware('can:exports.manage');

    /*
    |--------------------------------------------------------------------------
    | Invoices Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/invoices', [AdminInvoiceController::class, 'index'])->name('admin.invoices.index')->middleware('can:invoices.manage');
    Route::get('/admin/invoices/{invoice}', [AdminInvoiceController::class, 'show'])->name('admin.invoices.show')->middleware('can:invoices.manage');
    Route::get('/admin/invoices/{invoice}/pdf', [AdminInvoiceController::class, 'download'])->name('admin.invoices.pdf')->middleware('can:invoices.manage');
    Route::post('/admin/lab-orders/{order}/invoice', [AdminInvoiceController::class, 'createFromOrder'])->name('admin.invoices.create-from-order')->middleware('can:invoices.manage');
    Route::patch('/admin/invoices/{invoice}/status', [AdminInvoiceController::class, 'updateStatus'])->name('admin.invoices.status')->middleware('can:invoices.manage');
    Route::delete('/admin/invoices/{invoice}', [AdminInvoiceController::class, 'destroy'])->name('admin.invoices.destroy')->middleware('can:invoices.manage');

    /*
    |--------------------------------------------------------------------------
    | Prescriptions Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/prescriptions', [AdminPrescriptionController::class, 'index'])->name('admin.prescriptions.index')->middleware('can:prescriptions.manage');

    Route::get('/admin/prescriptions/{prescription}', [AdminPrescriptionController::class, 'show'])->name('admin.prescriptions.show')->middleware('can:prescriptions.manage');

    Route::get('/admin/prescriptions/{prescription}/pdf', [AdminPrescriptionController::class, 'pdf'])->name('admin.prescriptions.pdf')->middleware('can:prescriptions.manage');

    Route::delete('/admin/prescriptions/{prescription}', [AdminPrescriptionController::class, 'destroy'])->name('admin.prescriptions.destroy')->middleware('can:prescriptions.manage');

    /*
    |--------------------------------------------------------------------------
    | Blood Bank Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/bloodbank', [BloodBankController::class, 'dashboard'])->name('admin.bloodbank.dashboard')->middleware('can:bloodbank.manage');
    Route::get('/admin/bloodbank/inventory', [BloodBankController::class, 'inventory'])->name('admin.bloodbank.inventory')->middleware('can:bloodbank.manage');
    Route::patch('/admin/bloodbank/settings', [BloodBankController::class, 'updateSettings'])->name('admin.bloodbank.settings.update')->middleware('can:bloodbank.manage');

    Route::resource('/admin/blood-groups', AdminBloodGroupController::class)
        ->names('admin.blood-groups')
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('can:bloodbank.manage');
    Route::patch('/admin/blood-groups/{bloodGroup}/toggle', [AdminBloodGroupController::class, 'toggle'])->name('admin.blood-groups.toggle')->middleware('can:bloodbank.manage');

    Route::resource('/admin/blood-donors', AdminBloodDonorController::class)
        ->names('admin.blood-donors')
        ->parameters(['blood-donors' => 'donor'])
        ->middleware('can:bloodbank.manage');

    Route::resource('/admin/blood-donations', AdminBloodDonationController::class)
        ->names('admin.blood-donations')
        ->parameters(['blood-donations' => 'donation'])
        ->middleware('can:bloodbank.manage');
    Route::patch('/admin/blood-donations/{donation}/status', [AdminBloodDonationController::class, 'updateStatus'])->name('admin.blood-donations.status')->middleware('can:bloodbank.manage');

    Route::get('/admin/blood-requests', [AdminBloodRequestController::class, 'index'])->name('admin.blood-requests.index')->middleware('can:bloodbank.manage');
    Route::get('/admin/blood-requests/create', [AdminBloodRequestController::class, 'create'])->name('admin.blood-requests.create')->middleware('can:bloodbank.manage');
    Route::post('/admin/blood-requests', [AdminBloodRequestController::class, 'store'])->name('admin.blood-requests.store')->middleware('can:bloodbank.manage');
    Route::get('/admin/blood-requests/{bloodRequest}', [AdminBloodRequestController::class, 'show'])->name('admin.blood-requests.show')->middleware('can:bloodbank.manage');
    Route::post('/admin/blood-requests/{bloodRequest}/approve', [AdminBloodRequestController::class, 'approve'])->name('admin.blood-requests.approve')->middleware('can:bloodbank.manage');
    Route::post('/admin/blood-requests/{bloodRequest}/reject', [AdminBloodRequestController::class, 'reject'])->name('admin.blood-requests.reject')->middleware('can:bloodbank.manage');
    Route::post('/admin/blood-requests/{bloodRequest}/cancel', [AdminBloodRequestController::class, 'cancel'])->name('admin.blood-requests.cancel')->middleware('can:bloodbank.manage');
    Route::delete('/admin/blood-requests/{bloodRequest}', [AdminBloodRequestController::class, 'destroy'])->name('admin.blood-requests.destroy')->middleware('can:bloodbank.manage');

    Route::get('/admin/blood-issues', [AdminBloodIssueController::class, 'index'])->name('admin.blood-issues.index')->middleware('can:bloodbank.manage');
    Route::get('/admin/blood-issues/create/{bloodRequest}', [AdminBloodIssueController::class, 'create'])->name('admin.blood-issues.create')->middleware('can:bloodbank.manage');
    Route::post('/admin/blood-issues/{bloodRequest}', [AdminBloodIssueController::class, 'store'])->name('admin.blood-issues.store')->middleware('can:bloodbank.manage');
    Route::get('/admin/blood-issues/{bloodIssue}', [AdminBloodIssueController::class, 'show'])->name('admin.blood-issues.show')->middleware('can:bloodbank.manage');

    Route::get('/admin/blood-reports', [AdminBloodReportController::class, 'index'])->name('admin.bloodbank.reports')->middleware('can:bloodbank.manage');
    Route::get('/admin/blood-reports/export/donations', [AdminBloodReportController::class, 'exportDonations'])->name('admin.bloodbank.reports.donations')->middleware('can:bloodbank.manage');
    Route::get('/admin/blood-reports/export/requests', [AdminBloodReportController::class, 'exportRequests'])->name('admin.bloodbank.reports.requests')->middleware('can:bloodbank.manage');
    Route::get('/admin/blood-reports/export/issues', [AdminBloodReportController::class, 'exportIssues'])->name('admin.bloodbank.reports.issues')->middleware('can:bloodbank.manage');

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

    Route::get('/doctor/lab-orders', [DoctorLabOrderController::class, 'index'])->name('doctor.lab-orders.index');

    Route::get('/doctor/lab-orders/create', [DoctorLabOrderController::class, 'create'])->name('doctor.lab-orders.create');

    Route::post('/doctor/lab-orders', [DoctorLabOrderController::class, 'store'])->name('doctor.lab-orders.store');

    Route::get('/doctor/lab-orders/{order}', [DoctorLabOrderController::class, 'show'])->name('doctor.lab-orders.show');

    Route::get('/doctor/blood-requests', [DoctorBloodRequestController::class, 'index'])->name('doctor.blood-requests.index');

    Route::get('/doctor/blood-requests/create', [DoctorBloodRequestController::class, 'create'])->name('doctor.blood-requests.create');

    Route::post('/doctor/blood-requests', [DoctorBloodRequestController::class, 'store'])->name('doctor.blood-requests.store');

    Route::get('/doctor/blood-requests/{bloodRequest}', [DoctorBloodRequestController::class, 'show'])->name('doctor.blood-requests.show');

    Route::get('/doctor/prescriptions', [DoctorPrescriptionController::class, 'index'])->name('doctor.prescriptions.index');

    Route::get('/doctor/prescriptions/create', [DoctorPrescriptionController::class, 'create'])->name('doctor.prescriptions.create');

    Route::post('/doctor/prescriptions', [DoctorPrescriptionController::class, 'store'])->name('doctor.prescriptions.store');

    Route::get('/doctor/prescriptions/{prescription}', [DoctorPrescriptionController::class, 'show'])->name('doctor.prescriptions.show');

    Route::get('/doctor/prescriptions/{prescription}/edit', [DoctorPrescriptionController::class, 'edit'])->name('doctor.prescriptions.edit');

    Route::get('/doctor/prescriptions/{prescription}/pdf', [DoctorPrescriptionController::class, 'pdf'])->name('doctor.prescriptions.pdf');

    Route::put('/doctor/prescriptions/{prescription}', [DoctorPrescriptionController::class, 'update'])->name('doctor.prescriptions.update');

    Route::delete('/doctor/prescriptions/{prescription}', [DoctorPrescriptionController::class, 'destroy'])->name('doctor.prescriptions.destroy');

});

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/service', [ServiceController::class, 'index'])->name('service');

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

    Route::get('/profile', [FrontProfileController::class, 'index'])->name('profile');

    Route::put('/profile', [FrontProfileController::class, 'update'])->name('profile.update');

    Route::get('/profile/lab-reports/{report}/download', [FrontProfileController::class, 'downloadReport'])->name('profile.lab-reports.download');

    Route::get('/profile/lab-orders/{order}/pdf', [FrontProfileController::class, 'downloadOrderPdf'])->name('profile.lab-orders.pdf');

    Route::get('/profile/blood-requests', [FrontendBloodRequestController::class, 'index'])->name('profile.blood-requests');

    Route::get('/profile/prescriptions/{prescription}/pdf', [FrontProfileController::class, 'downloadPrescriptionPdf'])->name('profile.prescriptions.pdf');

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
