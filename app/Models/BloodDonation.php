<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodDonation extends Model
{
    public const STATUS_COLLECTED = 'collected';

    public const STATUS_TESTING = 'testing';

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_RESERVED = 'reserved';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'donor_id',
        'blood_group_id',
        'donation_date',
        'quantity',
        'unit',
        'bag_number',
        'collection_location',
        'expiry_date',
        'status',
        'reserved_for_request_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'donation_date' => 'date',
            'expiry_date' => 'date',
            'quantity' => 'integer',
        ];
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(BloodDonor::class);
    }

    public function bloodGroup(): BelongsTo
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BloodIssue::class, 'donation_id');
    }

    public function reservedForRequest(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class, 'reserved_for_request_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isIssuable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE
            && $this->expiry_date !== null
            && $this->expiry_date->greaterThanOrEqualTo(now()->toDateString());
    }

    /**
     * Statuses that may still be given away; expired/issued/rejected are
     * terminal states for the expiry sweep.
     */
    public static function activeStatuses(): array
    {
        return [
            self::STATUS_COLLECTED,
            self::STATUS_TESTING,
            self::STATUS_AVAILABLE,
            self::STATUS_RESERVED,
        ];
    }
}
