<?php

namespace App\Notifications;

use App\Models\LabOrder;
use Illuminate\Notifications\Notification;

class LabResultReady extends Notification
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
        return [
            'lab_order_id' => $this->order->id,
            'title' => 'Lab result ready',
            'message' => sprintf(
                'Lab results are ready for %s (order #%d).',
                $this->order->patient_name,
                $this->order->id,
            ),
        ];
    }
}
