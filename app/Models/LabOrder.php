<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LabOrder extends Model
{
    protected $fillable = [
        'doctor_id',
        'user_id',
        'appointment_id',
        'patient_name',
        'phone',
        'email',
        'priority',
        'note',
        'status',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabOrderItem::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(LabReport::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'lab_order_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
