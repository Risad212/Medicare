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
            'patients' => route('admin.patients.index'),
            'comments' => route('admin.comments.index'),
            'invoices' => route('admin.invoices.index'),
            'prescriptions' => route('admin.prescriptions.index'),
            'notifications' => route('notifications.index'),
            'labOrders' => Route::has('admin.lab-orders.index') ? route('admin.lab-orders.index') : null,
            'logout' => route('logout'),
        ];
    }
}
