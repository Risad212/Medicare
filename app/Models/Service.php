<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'title',
        'description',
        'icon',
        'button_text',
        'button_url',
        'order',
        'status',
    ];
}
