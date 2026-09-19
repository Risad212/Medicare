<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

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

    /**
     * RBAC roles held by this user.
     *
     * @return BelongsToMany<Role>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Super-admin bypass: legacy `admin` role always has every ability.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check a role by slug (checks RBAC roles, falls back to legacy column).
     */
    public function hasRole(string $slug): bool
    {
        if ($this->role === $slug) {
            return true;
        }

        if (! Schema::hasTable('roles')) {
            return false;
        }

        return $this->roles()->where('slug', $slug)->exists();
    }

    /**
     * Check a permission slug through any held role.
     */
    public function hasPermission(string $slug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! Schema::hasTable('permissions')) {
            return false;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('slug', $slug))
            ->exists();
    }

    /**
     * Staff gate for `/admin/*`: anyone holding at least one permission.
     */
    public function canAccessAdminPanel(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (! Schema::hasTable('permissions')) {
            return false;
        }

        return $this->roles()->whereHas('permissions')->exists();
    }
}
