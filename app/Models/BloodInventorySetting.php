<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloodInventorySetting extends Model
{
    protected $fillable = [
        'low_stock_threshold',
        'min_donation_days',
    ];

    protected function casts(): array
    {
        return [
            'low_stock_threshold' => 'integer',
            'min_donation_days' => 'integer',
        ];
    }

    /**
     * Single-row settings, similar to GeneralSetting/HomeSetting.
     */
    public static function setting(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['low_stock_threshold' => 2, 'min_donation_days' => 90]
        );
    }

    public function lowStockThreshold(): int
    {
        return $this->low_stock_threshold;
    }

    public function minDonationDays(): int
    {
        return $this->min_donation_days;
    }
}
