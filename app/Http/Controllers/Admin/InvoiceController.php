<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Modules\Lab\Models\LabOrder;
use App\Services\InvoiceService;
use App\Services\PdfService;
use App\Support\Module;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        // The lab-order link only exists while the lab module ships.
        $with = Module::enabled('lab') ? ['order', 'items'] : ['items'];

        $invoices = Invoice::with($with)
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

        if (! Module::enabled('lab')) {
            $invoices->getCollection()->each(fn (Invoice $i) => $i->setRelation('order', null));
        }

        return view('backend.invoices.index', compact('invoices'));
    }

    /**
     * Display a single invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['items', 'creator']);

        if (Module::enabled('lab')) {
            $invoice->load('order.doctor');
        } else {
            $invoice->setRelation('order', null);
        }

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

        if (! Module::enabled('lab')) {
            $invoice->setRelation('order', null);
        }

        return $pdf->stream('pdf.invoice', ['invoice' => $invoice], 'invoice-'.$invoice->invoice_no.'.pdf');
    }
}
