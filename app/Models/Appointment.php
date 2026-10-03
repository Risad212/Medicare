<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_id',
        'patient_name',
        'age',
        'gender',
        'phone',
        'email',
        'cancellation_token',
        'visit_type',
        'appointment_date',
        'time_slot_id',
        'status',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date:Y-m-d',
        ];
    }

    public const VISIT_TYPES = [
        1 => 'First Visit',
        2 => 'Second Visit',
        3 => 'Report Review',
    ];

    public function getVisitTypeLabelAttribute(): string
    {
        return self::VISIT_TYPES[(int) $this->visit_type] ?? 'N/A';
    }

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

    /**
     * Prescriptions issued from this visit.
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}
