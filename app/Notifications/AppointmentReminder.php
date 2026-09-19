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
        return [
            'appointment_id' => $this->appointment->id,
            'title' => 'Appointment reminder',
            'message' => sprintf(
                'You have an appointment with %s on %s at %s.',
                $this->appointment->patient_name,
                $this->appointment->appointment_date?->format('d M Y') ?? 'N/A',
                $this->appointment->timeSlot?->time ?? 'N/A',
            ),
        ];
    }
}
