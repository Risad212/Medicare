<?php

namespace App\Notifications;

use App\Models\Prescription;
use Illuminate\Notifications\Notification;

class PrescriptionReadyPatient extends Notification
{
    public function __construct(public Prescription $prescription) {}

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
            'prescription_id' => $this->prescription->id,
            'title' => 'Prescription ready',
            'message' => sprintf(
                'Dr. %s prepared a prescription for you on %s. You can view it in your profile.',
                $this->prescription->doctor?->name ?? 'N/A',
                $this->prescription->created_at->format('d M Y'),
            ),
        ];
    }
}
