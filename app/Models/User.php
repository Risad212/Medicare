<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'date_of_birth',
    'gender',
    'blood_group',
    'address',
    'profile_image',
    'google_id',
    'provider',
    'avatar',
    'role',
    'email_verified_at',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
        ];
    }

    /**
     * All appointments booked by this user (as a patient).
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * All blood requests raised for this user (as a patient).
     */
    public function bloodRequests(): HasMany
    {
        return $this->hasMany(BloodRequest::class, 'patient_id');
    }

    /**
     * All blood issues received by this user (as a patient).
     */
    public function bloodIssues(): HasMany
    {
        return $this->hasMany(BloodIssue::class, 'patient_id');
    }

    /**
     * Blood requests this user created on behalf of a patient.
     */
    public function bloodRequestsRequested(): HasMany
    {
        return $this->hasMany(BloodRequest::class, 'requested_by');
    }

    /**
     * Blood donations recorded by this user.
     */
    public function bloodDonationsCreated(): HasMany
    {
        return $this->hasMany(BloodDonation::class, 'created_by');
    }

    /**
     * Blood issues authorized by this user.
     */
    public function bloodIssuesIssued(): HasMany
    {
        return $this->hasMany(BloodIssue::class, 'issued_by');
    }

    /**
     * Prescriptions written for this user (as a patient).
     */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'patient_user_id');
    }
}
