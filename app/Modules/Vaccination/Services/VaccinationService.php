<?php

namespace App\Modules\Vaccination\Services;

use App\Modules\Vaccination\Models\Vaccination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VaccinationService
{
    public function create(array $validated): Vaccination
    {
        return DB::transaction(function () use ($validated) {
            $fields = $this->fields($validated);
            $this->guardDuplicate($fields);

            return Vaccination::create($fields);
        });
    }

    public function update(Vaccination $vaccination, array $validated): Vaccination
    {
        return DB::transaction(function () use ($vaccination, $validated) {
            $fields = $this->fields($validated);
            $this->guardDuplicate($fields, $vaccination->id);
            $vaccination->update($fields);

            return $vaccination->fresh(['user', 'patient']);
        });
    }

    public function delete(Vaccination $vaccination): void
    {
        DB::transaction(function () use ($vaccination) {
            $vaccination->delete();
        });
    }

    private function fields(array $validated): array
    {
        return array_intersect_key($validated, array_flip([
            'user_id',
            'patient_id',
            'child_name',
            'vaccine_name',
            'dose_number',
            'date_given',
            'next_due_date',
            'administered_by',
            'notes',
            'status',
            'created_by',
        ]));
    }

    /**
     * Reject a second record for the same subject + vaccine + dose.
     * Name match is case-insensitive so "BCG" and "bcg" collide.
     */
    private function guardDuplicate(array $fields, ?int $exceptId = null): void
    {
        $exists = $this->duplicateQuery($fields, $exceptId)->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'vaccine_name' => 'This dose is already recorded for this patient.',
            ]);
        }
    }

    private function duplicateQuery(array $fields, ?int $exceptId = null): Builder
    {
        $query = Vaccination::whereRaw('LOWER(vaccine_name) = ?', [mb_strtolower($fields['vaccine_name'])])
            ->where('dose_number', $fields['dose_number']);

        if (! empty($fields['user_id'])) {
            $query->where('user_id', $fields['user_id']);
        } else {
            $query->whereNull('user_id');
        }

        if (! empty($fields['patient_id'])) {
            $query->where('patient_id', $fields['patient_id']);
        } else {
            $query->whereNull('patient_id');
        }

        if (! empty($fields['child_name'])) {
            $query->whereRaw('LOWER(child_name) = ?', [mb_strtolower(trim($fields['child_name']))]);
        } else {
            $query->where(function ($q) {
                $q->whereNull('child_name')->orWhere('child_name', '');
            });
        }

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query;
    }
}
