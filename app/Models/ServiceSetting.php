<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSetting extends Model
{
    protected $fillable = [
        'emergency_subtitle',
        'emergency_title',
        'emergency_description',
        'emergency_image',
        'emergency_phone',
        'emergency_email',
        'prevention_subtitle',
        'prevention_title',
        'prevention_1_title',
        'prevention_1_desc',
        'prevention_2_title',
        'prevention_2_desc',
        'prevention_3_title',
        'prevention_3_desc',
        'prevention_4_title',
        'prevention_4_desc',
        'prevention_5_title',
        'prevention_5_desc',
        'prevention_6_title',
        'prevention_6_desc',
        'prevention_7_title',
        'prevention_7_desc',
        'prevention_8_title',
        'prevention_8_desc',
    ];
}
