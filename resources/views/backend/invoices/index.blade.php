@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Billing</p>
        <h1 class="mc-title">In<em>voices</em></h1>
        <p class="mc-sub">All invoices — filter by status or search by number and patient.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-bar">
    <form method="GET" action="{{ route('admin.invoices.index') }}" class="flex flex-1 items-center gap-2.5">
        <div class="mc-search flex-1">
            <i class="bi bi-search text-faint"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search invoice no / patient..." autocomplete="off">
        </div>
        <select name="status" class="rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
            <option value="">All statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="paid" @selected(request('status') === 'paid')>Paid</option>
            <option value="void" @selected(request('status') === 'void')>Void</option>
        </select>
        <button type="submit" class="mc-btn sm"><i class="bi bi-search"></i> Filter</button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.invoices.index') }}" class="mc-btn sm ghost">Reset</a>
        @endif
    </form>
</div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Patient</th>
                    <th>Lab Order</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td class="mc-num"><b>{{ $invoice->invoice_no }}</b></td>
                        <td>{{ $invoice->patient_name }}</td>
                        <td>
                            @if($invoice->order)
                                <a href="{{ route('admin.lab-orders.show', $invoice->order->id) }}" class="text-teal-dk font-semibold hover:underline">#{{ $invoice->order->id }}</a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="mc-num">${{ number_format($invoice->total, 2) }}</td>
                        <td>
                            @if($invoice->status === 'paid')
                                <span class="mc-pill p-paid"><i></i>Paid</span>
                            @elseif($invoice->status === 'void')
                                <span class="mc-pill p-void"><i></i>Void</span>
                            @else
                                <span class="mc-pill p-pending"><i></i>Pending</span>
                            @endif
                        </td>
                        <td class="mc-num">{{ $invoice->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="mc-acts">
                                <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="mc-btn sm"><i class="bi bi-eye"></i> View</a>
                                <a href="{{ route('admin.invoices.pdf', $invoice->id) }}" class="mc-btn sm ghost" target="_blank"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"><div class="mc-empty"><b>Nothing on this chart</b>No invoices found yet. Generate one from a completed lab order.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }}</span>
        {{ $invoices->links() }}
    </div>
</div>

@endsection
