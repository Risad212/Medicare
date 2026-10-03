<?php

namespace App\Modules\Beds\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bed extends Model
{
    public const STATUS_AVAILABLE = 0;

    public const STATUS_OCCUPIED = 1;

    public const STATUS_MAINTENANCE = 2;

    public const STATUSES = [
        self::STATUS_AVAILABLE => 'Available',
        self::STATUS_OCCUPIED => 'Occupied',
        self::STATUS_MAINTENANCE => 'Maintenance',
    ];

    protected $fillable = [
        'room_id',
        'bed_number',
        'status',
        'current_patient_id',
        'admitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'admitted_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function currentPatient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_patient_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[(int) $this->status] ?? 'N/A';
    }

    public function getIsOccupiedAttribute(): bool
    {
        return (int) $this->status === self::STATUS_OCCUPIED;
    }
}
