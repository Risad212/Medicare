<?php

namespace App\Services\Sms;

use App\Jobs\SendSms;
use App\Models\Appointment;
use App\Support\Module;

class SmsNotifier
{
    public static function appointmentBooked(Appointment $appointment): void
    {
        self::for($appointment, 'booked', 'messages.sms.booked', [
            'doctor' => $appointment->doctor?->name ?? 'N/A',
            'date' => $appointment->appointment_date?->format('j M Y') ?? 'N/A',
            'time' => $appointment->timeSlot?->time ?? 'N/A',
        ], [
            'appointment_id' => $appointment->id,
        ]);
    }

    public static function appointmentStatusChanged(Appointment $appointment, int $status): void
    {
        $locale = self::locale($appointment);
        $labels = [
            0 => __('messages.notifications.status_pending', [], $locale),
            1 => __('messages.notifications.status_approved', [], $locale),
            2 => __('messages.notifications.status_completed', [], $locale),
            3 => __('messages.notifications.status_cancelled', [], $locale),
        ];

        self::for($appointment, 'status_changed', 'messages.sms.status_changed', [
            'doctor' => $appointment->doctor?->name ?? 'N/A',
            'status' => $labels[$status] ?? __('messages.notifications.status_updated', [], $locale),
        ], [
            'appointment_id' => $appointment->id,
            'status' => $status,
        ]);
    }

    public static function appointmentReminder(Appointment $appointment): void
    {
        self::for($appointment, 'reminder', 'messages.sms.reminder', [
            'doctor' => $appointment->doctor?->name ?? 'N/A',
            'date' => $appointment->appointment_date?->format('j M Y') ?? 'N/A',
            'time' => $appointment->timeSlot?->time ?? 'N/A',
        ], [
            'appointment_id' => $appointment->id,
        ]);
    }

    /**
     * Resolve the recipient for an appointment and queue the message.
     *
     * Linked accounts must have opted in (sms_enabled + phone on file);
     * guest bookings consent to contact on the phone they booked with.
     *
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $payload
     */
    private static function for(Appointment $appointment, string $type, string $key, array $params, array $payload): void
    {
        if (! Module::enabled('sms')) {
            return;
        }

        $user = $appointment->user;

        if ($appointment->user_id) {
            if (! $user || ! $user->sms_enabled || ! $user->phone) {
                return;
            }

            $to = $user->phone;
            $locale = $user->locale ?? app()->getLocale();
        } else {
            if (! $appointment->phone) {
                return;
            }

            $to = $appointment->phone;
            $locale = app()->getLocale();
        }

        SendSms::dispatch($to, __($key, $params, $locale), $type, $payload);
    }

    private static function locale(Appointment $appointment): string
    {
        return $appointment->user?->locale ?? app()->getLocale();
    }
}
