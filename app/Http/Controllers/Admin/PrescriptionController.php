<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Modules\Pharmacy\Models\Medicine;
use App\Services\PdfService;
use App\Services\PrescriptionService;
use App\Support\AdminNavigation;
use App\Support\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request): Response
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

        $prescriptions->through(fn (Prescription $prescription) => [
            'id' => $prescription->id,
            'createdAt' => $prescription->created_at?->format('d M Y'),
            'patientName' => $prescription->patient_name,
            'phone' => $prescription->phone,
            'doctorName' => $prescription->doctor?->name ?? 'N/A',
            'doctorSpecialist' => $prescription->doctor?->specialist,
            'diagnosis' => $prescription->diagnosis,
            'followUpDate' => $prescription->follow_up_date?->format('d M Y'),
        ]);

        return Inertia::render('Admin/Prescriptions/Index', [
            'prescriptions' => [
                'data' => $prescriptions->items(),
                'currentPage' => $prescriptions->currentPage(),
                'lastPage' => $prescriptions->lastPage(),
                'firstItem' => $prescriptions->firstItem(),
                'lastItem' => $prescriptions->lastItem(),
                'total' => $prescriptions->total(),
                'previousPageUrl' => $prescriptions->previousPageUrl(),
                'nextPageUrl' => $prescriptions->nextPageUrl(),
            ],
            'filters' => ['search' => (string) $request->query('search', '')],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.prescriptions.index'),
                'showBase' => url('/admin/prescriptions'),
            ],
        ]);
    }

    /**
     * Display a single prescription.
     */
    public function show(Prescription $prescription): Response
    {
        $prescription->load(['items', 'doctor', 'appointment.timeSlot', 'patient']);

        $medicines = collect();

        if (Module::enabled('pharmacy')) {
            $prescription->load(['items.medicine', 'items.dispenser']);
            $medicines = Medicine::orderBy('name')->limit(200)->get(['id', 'name', 'stock_quantity', 'unit']);
        }

        return Inertia::render('Admin/Prescriptions/Show', [
            'prescription' => [
                'id' => $prescription->id,
                'patientName' => $prescription->patient_name,
                'age' => $prescription->age,
                'genderLabel' => $prescription->gender_label,
                'phone' => $prescription->phone,
                'email' => $prescription->email,
                'patientAccount' => $prescription->patient?->name ?? 'Walk-in (no account)',
                'createdAt' => $prescription->created_at?->format('d M Y h:i A'),
                'doctorName' => $prescription->doctor?->name ?? 'N/A',
                'doctorSpecialist' => $prescription->doctor?->specialist ?? 'N/A',
                'appointmentId' => $prescription->appointment?->id,
                'appointmentDate' => $prescription->appointment?->appointment_date?->format('d M Y'),
                'appointmentTime' => $prescription->appointment?->timeSlot?->time,
                'symptoms' => $prescription->symptoms,
                'diagnosis' => $prescription->diagnosis,
                'advice' => $prescription->advice,
                'items' => $prescription->items->map(fn ($item) => [
                    'id' => $item->id,
                    'medicineName' => $item->medicine_name,
                    'dosage' => $item->dosage,
                    'frequency' => $item->frequency,
                    'duration' => $item->duration,
                    'quantity' => $item->quantity,
                    'instructions' => $item->instructions,
                    'isDispensed' => $item->is_dispensed,
                    'dispensedQuantity' => $item->dispensed_quantity,
                    'medicineNameDispensed' => Module::enabled('pharmacy') ? $item->medicine?->name : null,
                    'dispenserName' => $item->dispenser?->name,
                ])->values(),
            ],
            'medicines' => $medicines->map(fn (Medicine $medicine) => [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'stockQuantity' => $medicine->stock_quantity,
                'unit' => $medicine->unit,
            ])->values(),
            'features' => ['pharmacy' => Module::enabled('pharmacy')],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.prescriptions.index'),
                'pdf' => route('admin.prescriptions.pdf', $prescription),
                'delete' => route('admin.prescriptions.destroy', $prescription),
                'dispenseBase' => Route::has('admin.prescriptions.items.dispense')
                    ? url("/admin/prescriptions/{$prescription->id}/items")
                    : null,
            ],
        ]);
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
