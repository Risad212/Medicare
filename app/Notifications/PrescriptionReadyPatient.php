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
        $locale = $notifiable->locale ?? app()->getLocale();

        return [
            'prescription_id' => $this->prescription->id,
            'title' => __('messages.notifications.prescription_title', [], $locale),
            'message' => __('messages.notifications.prescription_patient_message', [
                'doctor' => $this->prescription->doctor?->name ?? 'N/A',
                'date' => $this->prescription->created_at->format('d M Y'),
            ], $locale),
        ];
    }
}
