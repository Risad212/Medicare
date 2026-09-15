<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class);
    }

    public function bloodRequests(): HasMany
    {
        return $this->hasMany(BloodRequest::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function offDays(): HasMany
    {
        return $this->hasMany(DoctorOffDay::class);
    }

    /**
     * Whether the doctor has any configured schedule rows at all. An
     * entirely unconfigured doctor stays "all slots open" so the booking
     * flow never breaks before an admin sets up weekly availability.
     */
    public function hasConfiguredSchedule(): bool
    {
        return $this->schedules()->exists();
    }

    /**
     * IDs of the time slots this doctor accepts on the given date,
     * honouring both the weekly schedule and booked/off-day rules.
     *
     * @return Collection<int, int>
     */
    public function openSlotIdsForDate(Carbon|string $date): Collection
    {
        if ($this->offDays()->whereDate('date', $date)->exists()) {
            return collect();
        }

        $open = $this->schedules()
            ->where('weekday', Carbon::parse($date)->dayOfWeek)
            ->whereHas('timeSlot', fn ($q) => $q->where('status', 1))
            ->pluck('time_slot_id');

        if ($open->isNotEmpty()) {
            return $open;
        }

        // Only hit the DB a second time when no slots are open, to tell
        // "unconfigured doctor" (all slots open) apart from "configured
        // but closed that weekday" (empty).
        if (! $this->hasConfiguredSchedule()) {
            return TimeSlot::where('status', 1)->pluck('id');
        }

        return $open;
    }
}
