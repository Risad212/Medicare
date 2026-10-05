<?php

namespace App\Modules\Lab\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LabResultReadyMail;
use App\Modules\Lab\Models\LabOrder;
use App\Modules\Lab\Models\LabOrderItem;
use App\Modules\Lab\Models\LabReport;
use App\Modules\Lab\Services\LabReportService;
use App\Services\PatientNotifier;
use App\Services\PdfService;
use App\Services\StaffNotifier;
use App\Support\AdminNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LabOrderController extends Controller
{
    /**
     * Display all lab orders across the hospital.
     */
    public function index(Request $request): Response
    {
        $orders = LabOrder::with(['items.test', 'doctor', 'reports'])
            ->when(in_array($request->status, ['pending', 'in-progress', 'completed', 'cancelled'], true), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
                $query->where(function ($q) use ($search, $request) {
                    $q->where('patient_name', 'like', '%'.$search.'%');
                    if (is_numeric($request->search)) {
                        $q->orWhere('id', $request->search);
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $orders->through(fn (LabOrder $order) => [
            'id' => $order->id,
            'createdAt' => $order->created_at?->format('d M Y'),
            'patientName' => $order->patient_name,
            'phone' => $order->phone,
            'doctorName' => $order->doctor?->name ?? 'N/A',
            'tests' => $order->items->map(fn ($item) => $item->test?->name ?? 'Removed test')->values(),
            'priority' => $order->priority,
            'total' => number_format((float) $order->total, 2),
            'reportsCount' => $order->reports->count(),
            'status' => $order->status,
        ]);

        return Inertia::render('Admin/Lab/Orders/Index', [
            'orders' => [
                'data' => $orders->items(),
                'currentPage' => $orders->currentPage(),
                'lastPage' => $orders->lastPage(),
                'firstItem' => $orders->firstItem(),
                'lastItem' => $orders->lastItem(),
                'total' => $orders->total(),
                'previousPageUrl' => $orders->previousPageUrl(),
                'nextPageUrl' => $orders->nextPageUrl(),
            ],
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'status' => in_array($request->query('status'), ['pending', 'in-progress', 'completed', 'cancelled'], true) ? $request->query('status') : '',
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.lab-orders.index'),
                'export' => route('admin.exports.lab-orders'),
                'showBase' => url('/admin/lab-orders'),
            ],
        ]);
    }

    /**
     * Display a single lab order with report management.
     */
    public function show(LabOrder $order): Response
    {
        $order->load(['items.test', 'doctor', 'reports.uploader', 'appointment.timeSlot', 'user', 'invoice']);

        return Inertia::render('Admin/Lab/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'patientName' => $order->patient_name,
                'phone' => $order->phone,
                'email' => $order->email,
                'accountName' => $order->user?->name ?? 'Walk-in (no account)',
                'doctorName' => $order->doctor?->name ?? 'N/A',
                'appointmentId' => $order->appointment?->id,
                'appointmentDate' => $order->appointment?->appointment_date?->format('d M Y'),
                'createdAt' => $order->created_at?->format('d M Y h:i A'),
                'priority' => $order->priority,
                'status' => $order->status,
                'total' => number_format((float) $order->total, 2),
                'note' => $order->note,
                'invoiceId' => $order->invoice?->id,
                'invoiceNumber' => $order->invoice?->invoice_no,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'testName' => $item->test?->name ?? 'Removed test',
                    'normalRange' => $item->test?->normal_range,
                    'unit' => $item->test?->unit,
                    'result' => $item->result,
                    'price' => number_format((float) $item->price, 2),
                ])->values(),
                'reports' => $order->reports->map(fn (LabReport $report) => [
                    'id' => $report->id,
                    'name' => $report->report_name,
                    'notes' => $report->notes,
                    'createdAt' => $report->created_at?->format('d M Y h:i A'),
                    'uploaderName' => $report->uploader?->name,
                ])->values(),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.lab-orders.index'),
                'status' => route('admin.lab-orders.status', $order),
                'pdf' => route('admin.lab-orders.pdf', $order),
                'reportsStore' => route('admin.lab-orders.reports.store', $order),
                'itemResultBase' => url('/admin/lab-order-items'),
                'reportDownloadBase' => url('/admin/lab-reports'),
                'reportDeleteBase' => url('/admin/lab-reports'),
                'invoiceCreate' => route('admin.invoices.create-from-order', $order),
                'invoiceShowBase' => url('/admin/invoices'),
            ],
        ]);
    }

    /**
     * Update the order status (pending / in-progress / completed / cancelled).
     */
    public function updateStatus(Request $request, LabOrder $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in-progress,completed,cancelled',
        ]);

        $previousStatus = $order->getOriginal('status');

        $order->update(['status' => $validated['status']]);

        // Notify once per completion - not on re-toggles that land back on
        // "completed" (mirrors the appointment approved-guard pattern).
        if ($validated['status'] === 'completed' && $previousStatus !== 'completed') {
            $email = $order->email ?: $order->user?->email;

            if ($email) {
                Mail::to($email)->queue(new LabResultReadyMail($order->load(['items.test', 'doctor', 'user'])));
            }

            StaffNotifier::labResultReady($order, (int) auth()->id());

            PatientNotifier::labResultReady($order);
        }

        return back()->with('success', 'Lab order status updated to '.ucwords(str_replace('-', ' ', $validated['status'])).'.');
    }

    /**
     * Upload a report file (PDF/image) for the order.
     */
    public function storeReport(Request $request, LabOrder $order, LabReportService $service)
    {
        $validated = $request->validate([
            'report_name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'report_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $service->store($order, $validated, auth()->user());

        return back()->with('success', 'Lab report uploaded successfully.');
    }

    /**
     * Fill the lab technician's reading for a single test item.
     */
    public function updateItemResult(Request $request, LabOrderItem $item)
    {
        $validated = $request->validate([
            'result' => 'nullable|string|max:1000',
        ]);

        $item->update(['result' => $validated['result'] ?? null]);

        return back()->with('success', 'Test result saved.');
    }

    /**
     * Delete an uploaded report.
     */
    public function destroyReport(LabReport $report, LabReportService $service)
    {
        $service->delete($report);

        return back()->with('success', 'Lab report deleted.');
    }

    /**
     * Download / stream a stored report file.
     */
    public function downloadReport(LabReport $report)
    {
        abort_if(! Storage::disk('public')->exists($report->file_path), 404, 'Report file not found.');

        return Storage::disk('public')->download($report->file_path);
    }

    /**
     * Stream a printable PDF report sheet for the whole order.
     */
    public function downloadPdf(LabOrder $order, PdfService $pdf)
    {
        $order->load(['items.test', 'doctor', 'reports']);

        return $pdf->stream('lab.order-result-pdf', ['order' => $order], 'lab-order-'.$order->id.'-report.pdf');
    }
}
