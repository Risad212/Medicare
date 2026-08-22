<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_id',
        'patient_name',
        'age',
        'gender',
        'phone',
        'visit_type',
        'appointment_date',
        'time_slot_id',
        'status',
    ];

    /**
     * Appointment belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Appointment belongs to a Doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Time Slot
     */
    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}