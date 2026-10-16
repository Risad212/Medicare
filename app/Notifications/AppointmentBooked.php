<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class AppointmentBooked extends Notification
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
        $appointment = $this->appointment;
        $locale = $notifiable->locale ?? app()->getLocale();

        return [
            'appointment_id' => $appointment->id,
            'title' => __('messages.notifications.booking_new_title', [], $locale),
            'message' => __('messages.notifications.booking_new_message', [
                'patient' => $appointment->patient_name,
                'doctor' => $appointment->doctor?->name ?? 'N/A',
                'date' => $appointment->appointment_date?->format('d M Y') ?? 'N/A',
                'time' => $appointment->timeSlot?->time ?? 'N/A',
            ], $locale),
        ];
    }
}
