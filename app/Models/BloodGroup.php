<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodGroup extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function donors(): HasMany
    {
        return $this->hasMany(BloodDonor::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(BloodDonation::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(BloodRequest::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BloodIssue::class);
    }
}
