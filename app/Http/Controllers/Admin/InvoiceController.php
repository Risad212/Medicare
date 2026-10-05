<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Modules\Lab\Models\LabOrder;
use App\Services\InvoiceService;
use App\Services\PdfService;
use App\Support\AdminNavigation;
use App\Support\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): Response
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

        $invoices->through(fn (Invoice $invoice) => [
            'id' => $invoice->id,
            'number' => $invoice->invoice_no,
            'patientName' => $invoice->patient_name,
            'orderId' => $invoice->order?->id,
            'total' => number_format((float) $invoice->total, 2),
            'status' => $invoice->status,
            'createdAt' => $invoice->created_at?->format('d M Y'),
        ]);

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => [
                'data' => $invoices->items(),
                'currentPage' => $invoices->currentPage(),
                'lastPage' => $invoices->lastPage(),
                'firstItem' => $invoices->firstItem(),
                'lastItem' => $invoices->lastItem(),
                'total' => $invoices->total(),
                'previousPageUrl' => $invoices->previousPageUrl(),
                'nextPageUrl' => $invoices->nextPageUrl(),
            ],
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'status' => in_array($request->query('status'), ['pending', 'paid', 'void'], true) ? $request->query('status') : '',
            ],
            'features' => ['lab' => Module::enabled('lab')],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.invoices.index'),
                'showBase' => url('/admin/invoices'),
                'pdfBase' => url('/admin/invoices'),
                'labOrderBase' => Route::has('admin.lab-orders.show') ? url('/admin/lab-orders') : null,
            ],
        ]);
    }

    /**
     * Display a single invoice.
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->load(['items', 'creator']);

        if (Module::enabled('lab')) {
            $invoice->load('order.doctor');
        } else {
            $invoice->setRelation('order', null);
        }

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => [
                'id' => $invoice->id,
                'number' => $invoice->invoice_no,
                'patientName' => $invoice->patient_name,
                'phone' => $invoice->phone,
                'email' => $invoice->email,
                'orderId' => $invoice->order?->id,
                'orderDoctor' => $invoice->order?->doctor?->name,
                'createdAt' => $invoice->created_at?->format('d M Y h:i A'),
                'createdBy' => $invoice->creator?->name ?? 'N/A',
                'paidAt' => $invoice->paid_at?->format('d M Y h:i A'),
                'status' => $invoice->status,
                'subtotal' => number_format((float) $invoice->subtotal, 2),
                'tax' => number_format((float) $invoice->tax, 2),
                'discount' => number_format((float) $invoice->discount, 2),
                'total' => number_format((float) $invoice->total, 2),
                'items' => $invoice->items->map(fn ($item) => [
                    'id' => $item->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unitPrice' => number_format((float) $item->unit_price, 2),
                    'lineTotal' => number_format((float) $item->line_total, 2),
                ])->values(),
            ],
            'routes' => [
                ...AdminNavigation::routes(),
                'index' => route('admin.invoices.index'),
                'pdf' => route('admin.invoices.pdf', $invoice),
                'status' => route('admin.invoices.status', $invoice),
                'delete' => route('admin.invoices.destroy', $invoice),
                'labOrder' => Route::has('admin.lab-orders.show') && $invoice->order
                    ? route('admin.lab-orders.show', $invoice->order)
                    : null,
            ],
        ]);
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
