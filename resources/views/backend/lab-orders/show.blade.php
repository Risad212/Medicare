@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Laboratory</p>
        <h1 class="mc-title">Lab order <em>#{{ $order->id }}</em></h1>
        <p class="mc-sub">{{ $order->patient_name }} — tests, results and reports.</p>
    </div>
    <div class="mc-head-acts">
        @if($order->status === 'completed')
            @if($order->invoice)
                <a href="{{ route('admin.invoices.show', $order->invoice->id) }}" class="mc-btn"><i class="bi bi-receipt"></i> Invoice {{ $order->invoice->invoice_no }}</a>
            @else
                <form action="{{ route('admin.invoices.create-from-order', $order->id) }}" method="POST" >
                    @csrf
                    <button type="submit" class="mc-btn"><i class="bi bi-receipt"></i> Create Invoice</button>
                </form>
            @endif
        @endif
        <a href="{{ route('admin.lab-orders.pdf', $order->id) }}" class="mc-btn ghost" target="_blank"><i class="bi bi-file-earmark-pdf"></i> Report PDF</a>
        <a href="{{ route('admin.lab-orders.index') }}" class="mc-btn ghost">Back to list</a>
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
            <h5 class="text-[15px] font-bold">Patient & Order</h5>
        </div>
        <div class="overflow-x-auto">
            <table class="mc-tbl">
                <tr><td class="w-1/4 font-bold text-ink-2">Patient</td><td>{{ $order->patient_name }}</td></tr>
                <tr><td class="font-bold text-ink-2">Phone</td><td>{{ $order->phone ?? 'N/A' }}</td></tr>
                <tr><td class="font-bold text-ink-2">Email</td><td>{{ $order->email ?? 'N/A' }}</td></tr>
                <tr><td class="font-bold text-ink-2">Account</td><td>{{ $order->user->name ?? 'Walk-in (no account)' }}</td></tr>
                <tr><td class="font-bold text-ink-2">Requested by</td><td>Dr. {{ $order->doctor->name ?? 'N/A' }}</td></tr>
                <tr><td class="font-bold text-ink-2">Appointment</td><td>{{ $order->appointment ? '#' . $order->appointment->id . ' - ' . $order->appointment->appointment_date : 'N/A' }}</td></tr>
                <tr><td class="font-bold text-ink-2">Created</td><td>{{ $order->created_at->format('d M Y h:i A') }}</td></tr>
                <tr><td class="font-bold text-ink-2">Priority</td><td>
                    @if($order->priority === 'urgent')
                        <span class="mc-pill p-urgent"><i></i>Urgent</span>
                    @else
                        <span class="mc-pill p-cancelled">Normal</span>
                    @endif
                </td></tr>
            </table>
        </div>
    </div>

    <div class="mc-card">
        <div class="border-b border-line-2 px-4.5 py-3">
            <h5 class="text-[15px] font-bold">Status</h5>
        </div>
        <div class="p-4.5">
            <p class="mb-3 text-sm text-mut">Current:
                @if($order->status === 'pending')
                    <span class="mc-pill p-pending"><i></i>Pending</span>
                @elseif($order->status === 'in-progress')
                    <span class="mc-pill p-info"><i></i>In Progress</span>
                @elseif($order->status === 'completed')
                    <span class="mc-pill p-completed"><i></i>Completed</span>
                @else
                    <span class="mc-pill p-cancelled"><i></i>Cancelled</span>
                @endif
            </p>

            <form action="{{ route('admin.lab-orders.status', $order->id) }}" method="POST" class="flex items-center gap-2">
                @csrf
                @method('PUT')
                <select name="status" class="max-w-[220px] rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                    <option value="pending" @selected($order->status === 'pending')>Pending</option>
                    <option value="in-progress" @selected($order->status === 'in-progress')>In Progress</option>
                    <option value="completed" @selected($order->status === 'completed')>Completed</option>
                    <option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option>
                </select>
                <button type="submit" class="mc-btn sm"><i class="bi bi-arrow-repeat"></i> Update</button>
            </form>
        </div>
    </div>
</div>

<div class="mc-card mt-4">
    <div class="border-b border-line-2 px-4.5 py-3">
        <h5 class="text-[15px] font-bold">Tests & Results</h5>
    </div>
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Normal Range</th>
                    <th>Unit</th>
                    <th>Result</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td><b class="text-ink">{{ $item->test->name ?? 'Removed test' }}</b></td>
                        <td>{{ $item->test->normal_range ?? 'N/A' }}</td>
                        <td>{{ $item->test->unit ?? 'N/A' }}</td>
                        <td>
                            @if($item->result)
                                <span class="mc-pill p-active mb-1"><i></i>{{ $item->result }}</span>
                            @else
                                <span class="text-sm text-mut">Not entered</span>
                            @endif
                            <form action="{{ route('admin.lab-order-items.result', $item->id) }}" method="POST" class="mt-1 flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="result" value="{{ $item->result }}" class="max-w-[200px] rounded-lg border border-line bg-white px-3 py-2 text-[13px] text-ink outline-none focus:border-teal" placeholder="Enter reading...">
                                <button type="submit" class="mc-btn sm"><i class="bi bi-check"></i> Save</button>
                            </form>
                        </td>
                        <td class="mc-num">${{ number_format($item->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-line font-bold">
                    <td colspan="3" class="text-right text-ink-2">Total</td>
                    <td class="mc-num">${{ number_format($order->total, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($order->note)
        <div class="border-t border-line-2 px-4.5 py-3">
            <h6 class="mb-1 text-xs font-bold uppercase tracking-wide text-ink-2">Doctor Note</h6>
            <p class="text-sm text-mut">{{ $order->note }}</p>
        </div>
    @endif
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-5 mt-4">
    <div class="lg:col-span-2">
        <div class="mc-card">
            <div class="border-b border-line-2 px-4.5 py-3">
                <h5 class="text-[15px] font-bold">Upload Report</h5>
            </div>
            <div class="p-4.5">
                <form action="{{ route('admin.lab-orders.reports.store', $order->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Report Name <span class="text-[#dc2626]">*</span></label>
                        <input type="text" name="report_name" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" placeholder="e.g. CBC Report" required>
                    </div>
                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">File (PDF / Image) <span class="text-[#dc2626]">*</span></label>
                        <input type="file" name="report_file" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Notes</label>
                        <textarea name="notes" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" rows="2" placeholder="Important notes about this report..."></textarea>
                    </div>
                    <button type="submit" class="mc-btn"><i class="bi bi-cloud-upload"></i> Upload Report</button>
                </form>
            </div>
        </div>
    </div>

    <div class="lg:col-span-3">
        <div class="mc-card">
            <div class="border-b border-line-2 px-4.5 py-3">
                <h5 class="text-[15px] font-bold">Uploaded Reports ({{ $order->reports->count() }})</h5>
            </div>
            <div>
                @forelse($order->reports as $report)
                    <div class="flex items-center justify-between border-b border-line-2 px-4.5 py-3 last:border-b-0">
                        <div>
                            <b class="text-ink">{{ $report->report_name }}</b>
                            <div class="text-xs text-mut">
                                Uploaded {{ $report->created_at->format('d M Y h:i A') }}
                                @if($report->uploader)
                                    by {{ $report->uploader->name }}
                                @endif
                                @if($report->notes)
                                    <br>{{ $report->notes }}
                                @endif
                            </div>
                        </div>
                        <div class="mc-acts">
                            <a href="{{ route('admin.lab-reports.download', $report->id) }}" class="mc-btn sm"><i class="bi bi-download"></i></a>
                            <form action="{{ route('admin.lab-reports.destroy', $report->id) }}" method="POST"  onsubmit="return confirm('Delete this report?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-4.5 text-sm text-mut">No reports uploaded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
