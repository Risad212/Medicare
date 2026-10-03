<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PARTIALLY_APPROVED = 'partially_approved';

    public const STATUS_FULFILLED = 'fulfilled';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'patient_id',
        'blood_group_id',
        'quantity',
        'unit',
        'required_date',
        'urgency',
        'department',
        'reason',
        'doctor_id',
        'status',
        'requested_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'required_date' => 'date',
            'quantity' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function bloodGroup(): BelongsTo
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BloodIssue::class, 'request_id');
    }

    /**
     * Total quantity already issued against this request.
     */
    public function issuedQuantity(): int
    {
        return (int) $this->issues()->sum('quantity');
    }
}
