<?php

namespace App\Modules\Pharmacy\Services;

use App\Models\PrescriptionItem;
use App\Modules\Pharmacy\Models\Medicine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PharmacyService
{
    /**
     * Dispense stock against a prescribed line. Medicine row is locked and
     * re-checked inside the transaction so concurrent dispenses cannot
     * drive stock negative.
     */
    public function dispense(PrescriptionItem $item, int $medicineId, int $quantity, int $userId): PrescriptionItem
    {
        return DB::transaction(function () use ($item, $medicineId, $quantity, $userId) {
            $lockedItem = PrescriptionItem::whereKey($item->id)->lockForUpdate()->firstOrFail();

            if ($lockedItem->dispensed_at !== null) {
                throw ValidationException::withMessages([
                    'item' => 'This medicine was already dispensed.',
                ]);
            }

            $medicine = Medicine::whereKey($medicineId)->lockForUpdate()->firstOrFail();

            if ($medicine->is_expired) {
                throw ValidationException::withMessages([
                    'medicine_id' => 'This medicine is expired and cannot be dispensed.',
                ]);
            }

            if ($medicine->stock_quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$medicine->stock_quantity} {$medicine->unit} in stock.",
                ]);
            }

            $medicine->decrement('stock_quantity', $quantity);

            $lockedItem->update([
                'medicine_id' => $medicine->id,
                'dispensed_quantity' => $quantity,
                'dispensed_at' => now(),
                'dispensed_by' => $userId,
            ]);

            return $lockedItem->fresh(['medicine', 'dispenser']);
        });
    }
}
