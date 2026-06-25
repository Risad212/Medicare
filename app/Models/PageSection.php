<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = [
    'content',
    'status'
    ];

    protected $casts = [
        'content' => 'array'
    ];
}