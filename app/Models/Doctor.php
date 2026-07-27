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
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function appointment()
    {
        return $this->hasMany(Appointment::class);
    }
}