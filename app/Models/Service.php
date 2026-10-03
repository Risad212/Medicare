<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'icon',
        'button_text',
        'button_url',
        'order',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(fn (Service $service) => $service->slug = $service->slug ?: Str::slug($service->title));
        static::updating(fn (Service $service) => $service->slug = $service->slug ?: Str::slug($service->title));
    }
}
