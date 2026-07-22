<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;
use App\Models\TimeSlot;

class Appointment extends Model
{
    protected $fillable = [
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