<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class AdminNavigation
{
    public static function routes(): array
    {
        return [
            'dashboard' => route('admin.home'),
            'appointments' => route('admin.appointments.index'),
            'appointmentCreate' => route('admin.appointments.create'),
            'appointmentStatus' => route('admin.appointments.status', ['appointment' => '__APPOINTMENT__']),
            'doctors' => route('admin.doctors.index'),
            'doctorCreate' => route('admin.doctors.create'),
            'departments' => route('admin.departments.index'),
            'timeSlots' => route('admin.time-slots.index'),
            'services' => route('admin.services.index'),
            'labOrders' => Route::has('admin.lab-orders.index') ? route('admin.lab-orders.index') : null,
            'labTests' => Route::has('admin.lab-tests.index') ? route('admin.lab-tests.index') : null,
            'medicines' => Route::has('admin.medicines.index') ? route('admin.medicines.index') : null,
            'beds' => Route::has('admin.beds.index') ? route('admin.beds.index') : null,
            'ambulanceRequests' => Route::has('admin.ambulance-requests.index') ? route('admin.ambulance-requests.index') : null,
            'bloodBank' => Route::has('admin.bloodbank.dashboard') ? route('admin.bloodbank.dashboard') : null,
            'bloodDonors' => Route::has('admin.blood-donors.index') ? route('admin.blood-donors.index') : null,
            'bloodGroups' => Route::has('admin.blood-groups.index') ? route('admin.blood-groups.index') : null,
            'bloodDonations' => Route::has('admin.blood-donations.index') ? route('admin.blood-donations.index') : null,
            'bloodRequests' => Route::has('admin.blood-requests.index') ? route('admin.blood-requests.index') : null,
            'bloodIssues' => Route::has('admin.blood-issues.index') ? route('admin.blood-issues.index') : null,
            'bloodReports' => Route::has('admin.bloodbank.reports') ? route('admin.bloodbank.reports') : null,
            'vaccinations' => Route::has('admin.vaccinations.index') ? route('admin.vaccinations.index') : null,
            'languages' => Route::has('admin.languages.index') ? route('admin.languages.index') : null,
            'backups' => Route::has('admin.backups.index') ? route('admin.backups.index') : null,
            'serviceSettings' => route('settings.service'),
            'settingsGeneral' => route('settings.general'),
            'settingsHome' => route('settings.home'),
            'settingsAbout' => route('settings.about'),
            'settingsService' => route('settings.service'),
            'settingsDoctorSeo' => route('settings.doctor'),
            'settingsBlogSeo' => route('settings.blog'),
            'settingsContactSeo' => route('settings.contact'),
            'settingsAppointmentSeo' => route('settings.appointment'),
            'blogs' => route('admin.blogs.index'),
            'categories' => route('admin.categories.index'),
            'tags' => route('admin.tags.index'),
            'sliders' => route('admin.sliders.index'),
            'patients' => route('admin.patients.index'),
            'users' => route('admin.users.index'),
            'activityLogs' => route('admin.activity-logs.index'),
            'comments' => route('admin.comments.index'),
            'invoices' => route('admin.invoices.index'),
            'prescriptions' => route('admin.prescriptions.index'),
            'notifications' => route('notifications.index'),
            'logout' => route('logout'),
        ];
    }
}
