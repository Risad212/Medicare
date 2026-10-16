<?php

namespace App\Modules\Zoom\Models;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoomMeeting extends Model
{
    protected $fillable = [
        'appointment_id',
        'zoom_meeting_id',
        'join_url',
        'start_url',
        'sync_status',
        'last_error_code',
        'patient_notified_at',
        'patient_notification_key',
    ];

    protected function casts(): array
    {
        return [
            'join_url' => 'encrypted',
            'start_url' => 'encrypted',
            'patient_notified_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
