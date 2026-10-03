<?php

namespace App\Modules\Beds\Services;

use App\Modules\Beds\Models\Bed;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BedService
{
    /**
     * Occupy an available bed. Row lock + re-check inside the transaction
     * so two admins assigning at once cannot double-book the same bed.
     */
    public function assign(Bed $bed, int $patientId): Bed
    {
        return DB::transaction(function () use ($bed, $patientId) {
            $locked = Bed::whereKey($bed->id)->lockForUpdate()->firstOrFail();

            if ((int) $locked->status !== Bed::STATUS_AVAILABLE) {
                throw ValidationException::withMessages([
                    'bed' => 'This bed is no longer available.',
                ]);
            }

            $alreadyAdmitted = Bed::where('current_patient_id', $patientId)
                ->where('status', Bed::STATUS_OCCUPIED)
                ->lockForUpdate()
                ->exists();

            if ($alreadyAdmitted) {
                throw ValidationException::withMessages([
                    'patient_user_id' => 'This patient already occupies another bed.',
                ]);
            }

            $locked->update([
                'status' => Bed::STATUS_OCCUPIED,
                'current_patient_id' => $patientId,
                'admitted_at' => now(),
            ]);

            return $locked->fresh(['room.ward', 'currentPatient']);
        });
    }

    /**
     * Free an occupied bed. Clears the patient link and admission time.
     */
    public function discharge(Bed $bed): Bed
    {
        return DB::transaction(function () use ($bed) {
            $locked = Bed::whereKey($bed->id)->lockForUpdate()->firstOrFail();

            if ((int) $locked->status !== Bed::STATUS_OCCUPIED) {
                throw ValidationException::withMessages([
                    'bed' => 'Only an occupied bed can be discharged.',
                ]);
            }

            $locked->update([
                'status' => Bed::STATUS_AVAILABLE,
                'current_patient_id' => null,
                'admitted_at' => null,
            ]);

            return $locked->fresh(['room.ward']);
        });
    }
}
