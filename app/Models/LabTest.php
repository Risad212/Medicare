<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'category',
    'description',
    'price',
    'normal_range',
    'unit',
    'status',
])]
class LabTest extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    /**
     * Order items that reference this laboratory test.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(LabOrderItem::class);
    }
}
