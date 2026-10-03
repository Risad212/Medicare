<?php

namespace App\Modules\Ambulance\Notifications;

use App\Modules\Ambulance\Models\AmbulanceRequest;
use Illuminate\Notifications\Notification;

class AmbulanceRequested extends Notification
{
    public function __construct(public AmbulanceRequest $ambulanceRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $request = $this->ambulanceRequest;

        return [
            'ambulance_request_id' => $request->id,
            'title' => 'New ambulance request',
            'message' => sprintf(
                '%s requested pickup at %s (%s)',
                $request->requester_name,
                mb_substr($request->pickup_address, 0, 80),
                $request->requester_phone,
            ),
        ];
    }
}
