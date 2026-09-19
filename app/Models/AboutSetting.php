<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutSetting extends Model
{
    protected $fillable = [
        'subtitle',
        'title',
        'tagline',
        'description',
        'button_text',
        'button_url',
        'image_one',
        'image_two',
        'mission_title',
        'mission_description',
        'planning_title',
        'planning_description',
        'vision_title',
        'vision_description',
    ];
}
