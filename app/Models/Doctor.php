<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'degree',
        'department',
        'specialist',
        'image',
        'services',
        'availability',
        'phone',
        'status',
    ];
}
