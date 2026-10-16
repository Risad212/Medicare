<?php

namespace App\Modules\Zoom\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Modules\Zoom\Jobs\SyncZoomMeeting;
use App\Modules\Zoom\Models\ZoomMeeting;
use App\Modules\Zoom\Services\TelehealthJoinLink;
use Illuminate\Http\RedirectResponse;

class TelehealthController extends Controller
{
    public function join(Appointment $appointment, TelehealthJoinLink $link): RedirectResponse
    {
        abort_unless(
            $appointment->consultation_mode === 'telehealth'
                && (int) $appointment->status === 1,
            410,
        );

        abort_unless($link->isJoinable($appointment->loadMissing('timeSlot')), 403);

        $meeting = ZoomMeeting::where('appointment_id', $appointment->id)
            ->where('sync_status', 'ready')
            ->firstOrFail();

        return redirect()->away($meeting->join_url);
    }

    public function host(Appointment $appointment, TelehealthJoinLink $link): RedirectResponse
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        abort_unless(
            $appointment->doctor_id === $doctor->id
                && $appointment->consultation_mode === 'telehealth'
                && (int) $appointment->status === 1,
            404,
        );
        abort_unless($link->isJoinable($appointment->loadMissing('timeSlot')), 403);

        $meeting = ZoomMeeting::where('appointment_id', $appointment->id)
            ->where('sync_status', 'ready')
            ->firstOrFail();

        return redirect()->away($meeting->start_url);
    }

    public function retry(Appointment $appointment): RedirectResponse
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
        abort_unless($appointment->doctor_id === $doctor->id, 404);
        abort_unless(
            $appointment->consultation_mode === 'telehealth'
                && (int) $appointment->status === 1,
            409,
        );

        ZoomMeeting::where('appointment_id', $appointment->id)
            ->update(['sync_status' => 'pending', 'last_error_code' => null]);
        SyncZoomMeeting::dispatch($appointment->id)->afterCommit();

        return back()->with('success', 'Telehealth meeting synchronization queued.');
    }
}
