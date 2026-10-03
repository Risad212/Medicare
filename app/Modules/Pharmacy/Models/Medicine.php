<?php

namespace App\Modules\Pharmacy\Models;

use App\Models\PrescriptionItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    protected $fillable = [
        'name',
        'generic_name',
        'unit',
        'stock_quantity',
        'unit_price',
        'low_stock_threshold',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'low_stock_threshold' => 'integer',
            'expiry_date' => 'date:Y-m-d',
        ];
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
