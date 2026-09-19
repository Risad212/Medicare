<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    protected $fillable = [
        'doctor_id',
        'appointment_id',
        'patient_user_id',
        'patient_name',
        'age',
        'gender',
        'phone',
        'email',
        'symptoms',
        'diagnosis',
        'advice',
        'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'gender' => 'integer',
            'follow_up_date' => 'date:Y-m-d',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function getGenderLabelAttribute(): string
    {
        return match ((int) $this->gender) {
            1 => 'Male',
            2 => 'Female',
            3 => 'Other',
            default => 'N/A',
        };
    }
}
