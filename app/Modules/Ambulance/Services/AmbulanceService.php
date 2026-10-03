<?php

namespace App\Modules\Ambulance\Services;

use App\Modules\Ambulance\Models\AmbulanceRequest;
use App\Services\StaffNotifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AmbulanceService
{
    public function create(array $validated): AmbulanceRequest
    {
        return DB::transaction(function () use ($validated) {
            $request = AmbulanceRequest::create(
                $this->fields($validated) + ['user_id' => auth()->id()]
            );

            StaffNotifier::ambulanceRequested($request);

            return $request;
        });
    }

    /**
     * Move a request to a new status. Only forward transitions allowed;
     * Completed and Cancelled are terminal.
     */
    public function updateStatus(AmbulanceRequest $request, int $status): AmbulanceRequest
    {
        return DB::transaction(function () use ($request, $status) {
            if (! AmbulanceRequest::canTransition((int) $request->status, $status)) {
                throw ValidationException::withMessages([
                    'status' => 'This status change is not allowed from '.$request->status_label.'.',
                ]);
            }

            $request->update(['status' => $status]);

            return $request->fresh();
        });
    }

    private function fields(array $validated): array
    {
        return array_intersect_key($validated, array_flip([
            'requester_name',
            'requester_phone',
            'pickup_address',
            'destination',
            'emergency_type',
        ]));
    }
}
