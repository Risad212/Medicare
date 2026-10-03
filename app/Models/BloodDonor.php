<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class BloodDonor extends Model
{
    protected $fillable = [
        'name',
        'blood_group_id',
        'phone',
        'email',
        'date_of_birth',
        'gender',
        'address',
        'last_donation_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'last_donation_date' => 'date',
            'status' => 'boolean',
        ];
    }

    public function bloodGroup(): BelongsTo
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(BloodDonation::class, 'donor_id');
    }

    /**
     * Total successful (bagged) donations for this donor.
     */
    public function totalDonations(): int
    {
        return $this->donations()
            ->whereIn('status', ['collected', 'testing', 'available', 'reserved', 'issued'])
            ->count();
    }

    /**
     * Administrative eligibility check based on the configured minimum
     * interval since the last donation. This is a software screening
     * flag only and never medical advice.
     */
    public function isEligible(int $minDonationDays = 90): bool
    {
        if ($this->last_donation_date === null) {
            return true;
        }

        return Carbon::parse($this->last_donation_date)
            ->addDays($minDonationDays)
            ->lessThanOrEqualTo(Carbon::today());
    }
}
