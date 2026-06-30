<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSetting extends Model
{
        protected $fillable = [
        'about_title',
        'about_description',
        'about_button_text',
        'about_image_one',
        'about_image_two',
        'about_image_three',
        'counter_one_text',
        'counter_one_number',
        'counter_two_text',
        'counter_two_number',
        'counter_three_text',
        'counter_three_number',
        'counter_four_text',
        'counter_four_number',
    ];
}
