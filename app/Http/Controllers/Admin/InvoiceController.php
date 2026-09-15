<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\LabOrder;
use App\Services\InvoiceService;
use App\Services\PdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $invoices = Invoice::with(['order', 'items'])
            ->when(in_array($request->status, ['pending', 'paid', 'void'], true), function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = str_replace(['%', '_'], ['\%', '\_'], $request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', '%'.$search.'%')
                        ->orWhere('patient_name', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('backend.invoices.index', compact('invoices'));
    }

    /**
     * Display a single invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['items', 'order.doctor', 'creator']);

        return view('backend.invoices.show', compact('invoice'));
    }

    /**
     * Create an invoice from a completed lab order.
     */
    public function createFromOrder(LabOrder $order, InvoiceService $service)
    {
        $invoice = $service->createFromOrder($order, auth()->id());

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice '.$invoice->invoice_no.' created successfully.');
    }

    /**
     * Mark an invoice as paid / pending / void.
     */
    public function updateStatus(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,void',
        ]);

        $invoice->update([
            'status' => $validated['status'],
            'paid_at' => $validated['status'] === 'paid' ? now() : null,
        ]);

        return back()->with('success', 'Invoice marked as '.$validated['status'].'.');
    }

    /**
     * Delete an invoice.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return redirect()->route('admin.invoices.index')->with('success', 'Invoice deleted.');
    }

    /**
     * Stream the printable invoice PDF.
     */
    public function download(Invoice $invoice, PdfService $pdf)
    {
        $invoice->load('items');

        return $pdf->stream('pdf.invoice', ['invoice' => $invoice], 'invoice-'.$invoice->invoice_no.'.pdf');
    }
}
