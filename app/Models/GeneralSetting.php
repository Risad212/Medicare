<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $fillable = [
        'site_name',
        'logo',
        'favicon',
        'address',
        'working_hours',
        'facebook',
        'twitter',
        'linkedin',
        'youtube',
        'email',
        'phone',
        'footer_logo',
        'footer_description',
        'copyright',
    ];
}
