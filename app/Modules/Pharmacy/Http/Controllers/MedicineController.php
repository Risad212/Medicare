<?php

namespace App\Modules\Pharmacy\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Pharmacy\Http\Requests\MedicineRequest;
use App\Modules\Pharmacy\Models\Medicine;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MedicineController extends Controller
{
    public function index(Request $request): Response
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

        $medicines->through(fn (Medicine $medicine) => [
            'id' => $medicine->id,
            'name' => $medicine->name,
            'genericName' => $medicine->generic_name,
            'unit' => $medicine->unit,
            'stockQuantity' => $medicine->stock_quantity,
            'unitPrice' => $medicine->unit_price,
            'expiryDate' => $medicine->expiry_date?->format('Y-m-d'),
            'isLowStock' => $medicine->is_low_stock,
            'isExpired' => $medicine->is_expired,
        ]);

        return Inertia::render('Admin/Pharmacy/Index', [
            'medicines' => [
                'data' => $medicines->items(),
                'currentPage' => $medicines->currentPage(),
                'lastPage' => $medicines->lastPage(),
                'firstItem' => $medicines->firstItem(),
                'lastItem' => $medicines->lastItem(),
                'total' => $medicines->total(),
                'previousPageUrl' => $medicines->previousPageUrl(),
                'nextPageUrl' => $medicines->nextPageUrl(),
            ],
            'lowStockCount' => $lowStockCount,
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'lowStock' => $request->boolean('low_stock'),
            ],
            'canManage' => $request->user()->role === 'admin',
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.medicines.index'),
                'create' => route('admin.medicines.create'),
                'editBase' => url('/admin/medicines'),
                'deleteBase' => url('/admin/medicines'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pharmacy/Create', [
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.medicines.index'),
                'store' => route('admin.medicines.store'),
            ],
        ]);
    }

    public function store(MedicineRequest $request)
    {
        $medicine = Medicine::create($request->validated());

        return redirect()->route('admin.medicines.index')
            ->with('success', "Medicine '{$medicine->name}' added successfully.");
    }

    public function edit(Medicine $medicine): Response
    {
        return Inertia::render('Admin/Pharmacy/Edit', [
            'medicine' => [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'genericName' => $medicine->generic_name,
                'unit' => $medicine->unit,
                'stockQuantity' => $medicine->stock_quantity,
                'unitPrice' => $medicine->unit_price,
                'lowStockThreshold' => $medicine->low_stock_threshold,
                'expiryDate' => $medicine->expiry_date?->format('Y-m-d'),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.medicines.index'),
                'update' => route('admin.medicines.update', $medicine),
            ],
        ]);
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
