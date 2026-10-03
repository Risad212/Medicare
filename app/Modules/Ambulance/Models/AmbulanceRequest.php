<?php

namespace App\Modules\Ambulance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbulanceRequest extends Model
{
    public const STATUS_REQUESTED = 0;

    public const STATUS_DISPATCHED = 1;

    public const STATUS_COMPLETED = 2;

    public const STATUS_CANCELLED = 3;

    public const STATUSES = [
        self::STATUS_REQUESTED => 'Requested',
        self::STATUS_DISPATCHED => 'Dispatched',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    /**
     * Allowed next states per current state. Completed/Cancelled are terminal.
     */
    public const TRANSITIONS = [
        self::STATUS_REQUESTED => [self::STATUS_DISPATCHED, self::STATUS_CANCELLED],
        self::STATUS_DISPATCHED => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
        self::STATUS_COMPLETED => [],
        self::STATUS_CANCELLED => [],
    ];

    protected $fillable = [
        'user_id',
        'requester_name',
        'requester_phone',
        'pickup_address',
        'destination',
        'emergency_type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[(int) $this->status] ?? 'N/A';
    }

    public static function canTransition(int $from, int $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }
}
