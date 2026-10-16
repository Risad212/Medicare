<?php

namespace App\Modules\Zoom\Observers;

use App\Models\Appointment;
use App\Modules\Zoom\Jobs\DeleteZoomMeeting;
use App\Modules\Zoom\Jobs\SyncZoomMeeting;
use App\Modules\Zoom\Models\ZoomMeeting;

class AppointmentZoomObserver
{
    public function created(Appointment $appointment): void
    {
        if ($appointment->consultation_mode === 'telehealth' && (int) $appointment->status === 1) {
            SyncZoomMeeting::dispatch($appointment->id)->afterCommit();
        }
    }

    public function updated(Appointment $appointment): void
    {
        if ($appointment->wasChanged([
            'doctor_id',
            'appointment_date',
            'time_slot_id',
            'status',
            'consultation_mode',
            'email',
        ])) {
            SyncZoomMeeting::dispatch($appointment->id)->afterCommit();
        }
    }

    public function deleting(Appointment $appointment): void
    {
        $meetingId = ZoomMeeting::where('appointment_id', $appointment->id)->value('zoom_meeting_id');

        if ($meetingId) {
            DeleteZoomMeeting::dispatch($meetingId)->afterCommit();
        }
    }
}
