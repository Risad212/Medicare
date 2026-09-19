<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class AppointmentStatusChangedPatient extends Notification
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
        $labels = [0 => 'pending', 1 => 'approved', 2 => 'completed', 3 => 'cancelled'];
        $label = $labels[$this->status] ?? 'updated';

        return [
            'appointment_id' => $this->appointment->id,
            'status' => (string) $this->status,
            'title' => 'Appointment '.$label,
            'message' => sprintf(
                'Your appointment with Dr. %s on %s at %s is now %s.',
                $this->appointment->doctor?->name ?? 'N/A',
                $this->appointment->appointment_date?->format('d M Y') ?? 'N/A',
                $this->appointment->timeSlot?->time ?? 'N/A',
                $label,
            ),
        ];
    }
}
