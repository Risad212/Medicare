<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class AppointmentStatusChanged extends Notification
{
    public function __construct(public Appointment $appointment, public int $status) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $locale = $notifiable->locale ?? app()->getLocale();
        $labels = [
            0 => __('messages.notifications.status_pending', [], $locale),
            1 => __('messages.notifications.status_approved', [], $locale),
            2 => __('messages.notifications.status_completed', [], $locale),
            3 => __('messages.notifications.status_cancelled', [], $locale),
        ];
        $label = $labels[$this->status] ?? __('messages.notifications.status_updated', [], $locale);

        return [
            'appointment_id' => $this->appointment->id,
            'status' => (string) $this->status,
            'title' => __('messages.notifications.status_title', ['status' => $label], $locale),
            'message' => __('messages.notifications.status_staff_message', [
                'doctor' => $this->appointment->doctor?->name ?? 'N/A',
                'patient' => $this->appointment->patient_name,
                'status' => $label,
            ], $locale),
        ];
    }
}
