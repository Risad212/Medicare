<?php

namespace App\Modules\Vaccination\Models;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends Model
{
    public const STATUS_SCHEDULED = 0;

    public const STATUS_COMPLETED = 1;

    public const STATUS_MISSED = 2;

    public const STATUSES = [
        self::STATUS_SCHEDULED => 'Scheduled',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_MISSED => 'Missed',
    ];

    protected $fillable = [
        'user_id',
        'patient_id',
        'child_name',
        'vaccine_name',
        'dose_number',
        'date_given',
        'next_due_date',
        'administered_by',
        'notes',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'dose_number' => 'integer',
            'status' => 'integer',
            'date_given' => 'date:Y-m-d',
            'next_due_date' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[(int) $this->status] ?? 'N/A';
    }

    public function getSubjectNameAttribute(): string
    {
        if ($this->child_name) {
            return $this->child_name;
        }

        return $this->user?->name ?? $this->patient?->name ?? 'N/A';
    }

    public function getIsOverdueAttribute(): bool
    {
        return (int) $this->status === self::STATUS_SCHEDULED
            && $this->next_due_date
            && $this->next_due_date->isPast();
    }
}
