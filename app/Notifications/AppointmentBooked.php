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

        return [
            'appointment_id' => $appointment->id,
            'title' => 'New appointment booked',
            'message' => sprintf(
                '%s booked Dr. %s on %s at %s',
                $appointment->patient_name,
                $appointment->doctor?->name ?? 'N/A',
                $appointment->appointment_date?->format('d M Y') ?? 'N/A',
                $appointment->timeSlot?->time ?? 'N/A',
            ),
        ];
    }
}
