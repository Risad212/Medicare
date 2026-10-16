<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class AppointmentReminder extends Notification
{
    public function __construct(public Appointment $appointment) {}

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

        return [
            'appointment_id' => $this->appointment->id,
            'title' => __('messages.notifications.reminder_title', [], $locale),
            'message' => __('messages.notifications.reminder_staff_message', [
                'patient' => $this->appointment->patient_name,
                'date' => $this->appointment->appointment_date?->format('d M Y') ?? 'N/A',
                'time' => $this->appointment->timeSlot?->time ?? 'N/A',
            ], $locale),
        ];
    }
}
