<?php

namespace App\Notifications;

use App\Modules\Lab\Models\LabOrder;
use Illuminate\Notifications\Notification;

class LabResultReadyPatient extends Notification
{
    public function __construct(public LabOrder $order) {}

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
            'lab_order_id' => $this->order->id,
            'title' => __('messages.notifications.lab_title', [], $locale),
            'message' => __('messages.notifications.lab_patient_message', [
                'id' => $this->order->id,
            ], $locale),
        ];
    }
}
