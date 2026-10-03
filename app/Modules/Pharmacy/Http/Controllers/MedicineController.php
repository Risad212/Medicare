<?php

namespace App\Modules\Pharmacy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pharmacy\Http\Requests\MedicineRequest;
use App\Modules\Pharmacy\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search ? str_replace(['%', '_'], ['\%', '\_'], $request->search) : null;

        $medicines = Medicine::when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('generic_name', 'like', '%'.$search.'%');
            });
        })
            ->when($request->boolean('low_stock'), fn ($query) => $query->lowStock())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $lowStockCount = Medicine::lowStock()->count();

        return view('pharmacy.index', compact('medicines', 'lowStockCount'));
    }

    public function create()
    {
        return view('pharmacy.create');
    }

    public function store(MedicineRequest $request)
    {
        $medicine = Medicine::create($request->validated());

        return redirect()->route('admin.medicines.index')
            ->with('success', "Medicine '{$medicine->name}' added successfully.");
    }

    public function edit(Medicine $medicine)
    {
        return view('pharmacy.edit', compact('medicine'));
    }

    public function update(MedicineRequest $request, Medicine $medicine)
    {
        $medicine->update($request->validated());

        return redirect()->route('admin.medicines.index')
            ->with('success', 'Medicine updated successfully.');
    }

    public function destroy(Medicine $medicine)
    {
        if ($medicine->prescriptionItems()->exists()) {
            return back()->withErrors(['medicine' => 'This medicine has dispensing history and cannot be deleted.']);
        }

        $medicine->delete();

        return redirect()->route('admin.medicines.index')
            ->with('success', 'Medicine deleted successfully.');
    }
}
