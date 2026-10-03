@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Billing</p>
        <h1 class="mc-title">Invoice <em>{{ $invoice->invoice_no }}</em></h1>
        <p class="mc-sub">Full invoice breakdown with items and totals.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.invoices.pdf', $invoice->id) }}" class="mc-btn ghost" target="_blank"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <a href="{{ route('admin.invoices.index') }}" class="mc-btn ghost">Back to list</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 list-disc pl-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="mc-card">
        <div class="border-b border-line-2 px-4.5 py-3">
            <h5 class="text-[15px] font-bold">Invoice</h5>
        </div>
        <div class="overflow-x-auto">
            <table class="mc-tbl">
                <tr><td class="w-1/4 font-bold text-ink-2">Invoice No</td><td class="mc-num">{{ $invoice->invoice_no }}</td></tr>
                <tr><td class="font-bold text-ink-2">Patient</td><td>{{ $invoice->patient_name }}</td></tr>
                <tr><td class="font-bold text-ink-2">Phone</td><td>{{ $invoice->phone ?? 'N/A' }}</td></tr>
                <tr><td class="font-bold text-ink-2">Email</td><td>{{ $invoice->email ?? 'N/A' }}</td></tr>
                <tr><td class="font-bold text-ink-2">Lab Order</td><td>
                    @if($invoice->order)
                        <a href="{{ route('admin.lab-orders.show', $invoice->order->id) }}" class="text-teal-dk font-semibold hover:underline">#{{ $invoice->order->id }}</a>
                        @if($invoice->order->doctor) by Dr. {{ $invoice->order->doctor->name }} @endif
                    @else
                        N/A
                    @endif
                </td></tr>
                <tr><td class="font-bold text-ink-2">Created</td><td>{{ $invoice->created_at->format('d M Y h:i A') }} by {{ $invoice->creator->name ?? 'N/A' }}</td></tr>
                @if($invoice->paid_at)
                    <tr><td class="font-bold text-ink-2">Paid On</td><td>{{ $invoice->paid_at->format('d M Y h:i A') }}</td></tr>
                @endif
            </table>
        </div>
    </div>

    <div class="mc-card">
        <div class="border-b border-line-2 px-4.5 py-3">
            <h5 class="text-[15px] font-bold">Status</h5>
        </div>
        <div class="p-4.5">
            <p class="mb-3 text-sm text-mut">Current:
                @if($invoice->status === 'paid')
                    <span class="mc-pill p-paid"><i></i>Paid</span>
                @elseif($invoice->status === 'void')
                    <span class="mc-pill p-void"><i></i>Void</span>
                @else
                    <span class="mc-pill p-pending"><i></i>Pending</span>
                @endif
            </p>

            <form action="{{ route('admin.invoices.status', $invoice->id) }}" method="POST" class="mb-4 flex items-center gap-2">
                @csrf
                @method('PATCH')
                <select name="status" class="max-w-[200px] rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                    <option value="pending" @selected($invoice->status === 'pending')>Pending</option>
                    <option value="paid" @selected($invoice->status === 'paid')>Paid</option>
                    <option value="void" @selected($invoice->status === 'void')>Void</option>
                </select>
                <button type="submit" class="mc-btn sm"><i class="bi bi-check-lg"></i> Update</button>
            </form>

            <hr class="mb-4 border-line-2">

            <form action="{{ route('admin.invoices.destroy', $invoice->id) }}" method="POST" onsubmit="return confirm('Delete this invoice permanently?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete invoice</button>
            </form>
        </div>
    </div>
</div>

<div class="mc-card mt-4">
    <div class="border-b border-line-2 px-4.5 py-3">
        <h5 class="text-[15px] font-bold">Items</h5>
    </div>
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $index => $item)
                    <tr>
                        <td class="mc-idx">{{ $index + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="mc-num text-right">{{ $item->quantity }}</td>
                        <td class="mc-num text-right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="mc-num text-right">${{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-mut">No line items recorded for this invoice.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="border-t border-line font-bold">
                    <td colspan="4" class="text-right text-ink-2">Subtotal</td>
                    <td class="mc-num text-right">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                <tr class="font-bold">
                    <td colspan="4" class="text-right text-ink-2">Tax</td>
                    <td class="mc-num text-right">${{ number_format($invoice->tax, 2) }}</td>
                </tr>
                <tr class="font-bold">
                    <td colspan="4" class="text-right text-ink-2">Discount</td>
                    <td class="mc-num text-right">-${{ number_format($invoice->discount, 2) }}</td>
                </tr>
                <tr class="font-bold">
                    <td colspan="4" class="text-right text-ink-2">Total</td>
                    <td class="mc-num text-right">${{ number_format($invoice->total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection
