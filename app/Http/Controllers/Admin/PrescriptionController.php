<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Modules\Pharmacy\Models\Medicine;
use App\Services\PdfService;
use App\Services\PrescriptionService;
use App\Support\Module;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request)
    {
        $prescriptions = Prescription::with(['doctor', 'items'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('patient_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhereHas('doctor', function ($doctor) use ($search) {
                            $doctor->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.prescriptions.index', compact('prescriptions'));
    }

    /**
     * Display a single prescription.
     */
    public function show(Prescription $prescription)
    {
        $prescription->load(['items.dispenser', 'doctor', 'appointment.timeSlot', 'patient']);

        $medicines = collect();

        if (Module::enabled('pharmacy')) {
            $prescription->load('items.medicine');
            $medicines = Medicine::orderBy('name')->limit(200)->get(['id', 'name', 'stock_quantity', 'unit']);
        }

        return view('backend.prescriptions.show', compact('prescription', 'medicines'));
    }

    /**
     * Stream a printable PDF of the prescription.
     */
    public function pdf(Prescription $prescription, PdfService $pdf)
    {
        $prescription->load(['items', 'doctor', 'appointment.timeSlot']);

        return $pdf->stream('pdf.prescription', ['prescription' => $prescription], 'prescription-'.$prescription->id.'.pdf');
    }

    /**
     * Remove the specified prescription from storage.
     */
    public function destroy(Prescription $prescription, PrescriptionService $service)
    {
        $service->delete($prescription);

        return redirect()->route('admin.prescriptions.index')->with('success', 'Prescription deleted successfully.');
    }
}
