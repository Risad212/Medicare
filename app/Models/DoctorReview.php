<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorReview extends Model
{
    public const STATUS_PENDING = 0;

    public const STATUS_APPROVED = 1;

    public const STATUS_REJECTED = 2;

    protected $fillable = [
        'doctor_id',
        'appointment_id',
        'user_id',
        'rating',
        'comment',
        'status',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(self $query): self
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * @return array{avg: float, count: int}
     */
    public static function summaryFor(Doctor $doctor): array
    {
        $stats = self::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', self::STATUS_APPROVED)
            ->selectRaw('avg(rating) as avg_rating, count(*) as cnt')
            ->first();

        return [
            'avg' => round((float) ($stats->avg_rating ?? 0), 1),
            'count' => (int) ($stats->cnt ?? 0),
        ];
    }
}
