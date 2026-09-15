<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\LabOrder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Create an invoice from a completed lab order, snapshotting its items.
     */
    public function createFromOrder(LabOrder $order, int $createdBy): Invoice
    {
        abort_if($order->status !== 'completed', 422, 'Only completed lab orders can be invoiced.');
        abort_if($order->invoice !== null, 409, 'This lab order already has an invoice.');

        // nextInvoiceNo() is max(id)+1, so two concurrent creates can pick
        // the same number. Retry on unique violation; the winner commits
        // first and the loser recomputes a fresh number.
        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                return DB::transaction(function () use ($order, $createdBy) {
                    $items = $order->items->map(fn ($item): array => [
                        'description' => $item->test->name ?? 'Lab test',
                        'quantity' => 1,
                        'unit_price' => (float) $item->price,
                        'line_total' => (float) $item->price,
                    ])->all();

                    $subtotal = array_sum(array_column($items, 'line_total'));

                    $invoice = Invoice::create([
                        'invoice_no' => $this->nextInvoiceNo(),
                        'lab_order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'patient_name' => $order->patient_name,
                        'phone' => $order->phone,
                        'email' => $order->email,
                        'subtotal' => $subtotal,
                        'tax' => 0,
                        'discount' => 0,
                        'total' => $subtotal,
                        'status' => 'pending',
                        'created_by' => $createdBy,
                    ]);

                    foreach ($items as $item) {
                        $invoice->items()->create($item);
                    }

                    return $invoice;
                });
            } catch (QueryException $e) {
                $message = $e->getMessage();
                $code = (string) $e->getCode();
                // Only treat UNIQUE violations on this table/column as a
                // duplicate-invoice signal. A bare column-name match would
                // also catch FK/type errors mentioning lab_order_id.
                // MySQL names the key invoices_lab_order_id_unique; SQLite
                // reports UNIQUE constraint failed: invoices.lab_order_id.
                $isUniqueViolation = $code === '23000' || $code === '19'
                    || str_contains($message, 'UNIQUE constraint failed')
                    || str_contains($message, 'Duplicate entry')
                    || str_contains($message, 'unique constraint');
                $isLabDuplicate = $isUniqueViolation
                    && (str_contains($message, 'invoices_lab_order_id_unique')
                        || str_contains($message, 'invoices.lab_order_id'));
                // Second invoice for same order -> 409, not a retryable number clash.
                if ($isLabDuplicate) {
                    abort(409, 'This lab order already has an invoice.');
                }
                $isDuplicateNo = $isUniqueViolation
                    && (str_contains($message, 'invoices_invoice_no_unique')
                        || str_contains($message, 'invoices.invoice_no'));
                if ($isDuplicateNo && $attempt < 2) {
                    continue;
                }
                throw $e;
            }
        }

        abort(409, 'Could not generate a unique invoice number, please retry.');
    }

    /**
     * Generate the next sequential invoice number, e.g. INV-2026-0001.
     *
     * Based on the highest primary key (not a per-year count) so the number
     * is never reused after an invoice is deleted and two invoices never
     * claim the same sequence.
     */
    public function nextInvoiceNo(): string
    {
        $year = now()->year;
        $next = (int) Invoice::query()->max('id') + 1;

        return 'INV-'.$year.'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
