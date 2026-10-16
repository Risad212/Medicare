<?php

namespace App\Modules\Zoom\Services;

use App\Models\Appointment;
use App\Modules\Zoom\Mail\TelehealthReadyMail;
use App\Modules\Zoom\Models\ZoomMeeting;
use Illuminate\Support\Facades\Mail;

class ZoomMeetingSync
{
    public function __construct(private readonly ZoomClient $client) {}

    public function sync(int $appointmentId): void
    {
        $appointment = Appointment::with(['doctor', 'timeSlot'])->find($appointmentId);
        $meeting = ZoomMeeting::firstOrNew(['appointment_id' => $appointmentId]);

        if (! $appointment || $appointment->consultation_mode !== 'telehealth' || (int) $appointment->status !== 1) {
            if ($meeting->exists) {
                try {
                    if ($meeting->zoom_meeting_id) {
                        $this->client->deleteMeeting($meeting->zoom_meeting_id);
                    }
                    $meeting->delete();
                } catch (ZoomException $exception) {
                    $meeting->update([
                        'sync_status' => 'failed',
                        'last_error_code' => $exception->reason,
                    ]);
                    throw $exception;
                }
            }

            return;
        }

        if (! $appointment->email) {
            $this->fail($meeting, 'patient_email_missing');
        }

        $meeting->fill([
            'sync_status' => 'pending',
            'last_error_code' => null,
        ])->save();

        try {
            $previousJoinUrl = $meeting->join_url;
            $details = $meeting->zoom_meeting_id
                ? $this->client->updateMeeting($meeting->zoom_meeting_id, $appointment)
                : null;

            if (! $details) {
                $details = $this->client->createMeeting($appointment);
            } else {
                $details['join_url'] ??= $meeting->join_url;
                $details['start_url'] ??= $meeting->start_url;
            }

            if (! is_string($details['join_url'] ?? null) || ! is_string($details['start_url'] ?? null)) {
                throw new ZoomException('invalid_zoom_response');
            }

            $meeting->fill($details + [
                'sync_status' => 'ready',
                'last_error_code' => null,
                'patient_notified_at' => $previousJoinUrl !== ($details['join_url'] ?? $meeting->join_url)
                    ? null
                    : $meeting->patient_notified_at,
            ])->save();

            $notificationKey = $this->notificationKey($appointment, $meeting->join_url);

            if ($appointment->email && $meeting->patient_notification_key !== $notificationKey) {
                Mail::to($appointment->email)->queue(new TelehealthReadyMail(
                    $appointment,
                    app(TelehealthJoinLink::class)->for($appointment),
                ));
                $meeting->update([
                    'patient_notified_at' => now(),
                    'patient_notification_key' => $notificationKey,
                ]);
            }
        } catch (ZoomException $exception) {
            $meeting->update([
                'sync_status' => 'failed',
                'last_error_code' => $exception->reason,
            ]);
            throw $exception;
        }
    }

    private function fail(ZoomMeeting $meeting, string $reason): never
    {
        $meeting->fill([
            'sync_status' => 'failed',
            'last_error_code' => $reason,
        ])->save();

        throw new ZoomException($reason);
    }

    private function notificationKey(Appointment $appointment, string $joinUrl): string
    {
        return hash_hmac('sha256', implode('|', [
            $appointment->id,
            $appointment->email,
            $appointment->appointment_date->format('Y-m-d'),
            $appointment->timeSlot->time,
            $joinUrl,
            config('services.zoom.duration_minutes', 60),
        ]), (string) config('app.key'));
    }
}
