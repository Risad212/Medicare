<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index()
        {
            $doctor = Doctor::where('user_id', auth()->id())->first();

            $todayAppointments     = Appointment::where('doctor_id', $doctor->id)
                                                ->where('appointment_date', today())
                                                ->count();

            $totalAppointments     = Appointment::where('doctor_id', $doctor->id)->count();

            $pendingAppointments   = Appointment::where('doctor_id', $doctor->id)
                                                ->where('status', 0)
                                                ->count();

            $todayAppointmentsList = Appointment::with('timeSlot')
                                                ->where('doctor_id', $doctor->id)
                                                ->where('appointment_date', today())
                                                ->get();

            return view('backend.doctor-dashboard.dashboard', compact(
                'doctor',
                'todayAppointments',
                'totalAppointments',
                'pendingAppointments',
                'todayAppointmentsList'
            ));
        }
}