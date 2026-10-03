<?php

namespace App\Modules\Pharmacy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Modules\Pharmacy\Http\Requests\DispenseRequest;
use App\Modules\Pharmacy\Services\PharmacyService;

class DispenseController extends Controller
{
    public function store(DispenseRequest $request, Prescription $prescription, PrescriptionItem $item, PharmacyService $service)
    {
        abort_if($item->prescription_id !== $prescription->id, 404);

        $validated = $request->validated();

        $service->dispense($item, (int) $validated['medicine_id'], (int) $validated['quantity'], (int) auth()->id());

        return back()->with('success', 'Medicine dispensed and stock updated.');
    }
}
