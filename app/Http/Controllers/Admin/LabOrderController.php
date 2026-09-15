<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LabResultReadyMail;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabReport;
use App\Services\LabReportService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class LabOrderController extends Controller
{
    /**
     * Display all lab orders across the hospital.
     */
    public function index(Request $request)
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
            ->paginate(10);

        return view('backend.lab-orders.index', compact('orders'));
    }

    /**
     * Display a single lab order with report management.
     */
    public function show(LabOrder $order)
    {
        $order->load(['items.test', 'doctor', 'reports.uploader', 'appointment.timeSlot', 'user']);

        return view('backend.lab-orders.show', compact('order'));
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

        return $pdf->stream('pdf.lab-order-result', ['order' => $order], 'lab-order-'.$order->id.'-report.pdf');
    }
}
