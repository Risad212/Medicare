@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Diagnostics</p>
        <h1 class="mc-title">Lab request <em>#{{ $order->id }}</em></h1>
        <p class="mc-sub">Patient, order, tests, and uploaded reports.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('doctor.lab-orders.index') }}" class="mc-btn ghost">Back to list</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-2">
    <div class="mc-card">
        <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Patient Information</h5></div>
        <table class="w-full text-[14px]">
            <tbody>
                <tr class="border-b border-line-2">
                    <th class="w-1/4 whitespace-nowrap px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Name</th>
                    <td class="px-4.5 py-2.5 font-semibold">{{ $order->patient_name }}</td>
                </tr>
                <tr class="border-b border-line-2">
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Phone</th>
                    <td class="px-4.5 py-2.5">{{ $order->phone ?? 'N/A' }}</td>
                </tr>
                <tr class="border-b border-line-2">
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Email</th>
                    <td class="px-4.5 py-2.5">{{ $order->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Account</th>
                    <td class="px-4.5 py-2.5">{{ $order->user->name ?? 'Walk-in (no account)' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mc-card">
        <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Order Information</h5></div>
        <table class="w-full text-[14px]">
            <tbody>
                <tr class="border-b border-line-2">
                    <th class="w-1/4 whitespace-nowrap px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Date</th>
                    <td class="px-4.5 py-2.5">{{ $order->created_at->format('d M Y h:i A') }}</td>
                </tr>
                <tr class="border-b border-line-2">
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Doctor</th>
                    <td class="px-4.5 py-2.5">{{ $order->doctor->name ?? 'N/A' }}</td>
                </tr>
                <tr class="border-b border-line-2">
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Appointment</th>
                    <td class="px-4.5 py-2.5">{{ $order->appointment ? '#' . $order->appointment->id . ' - ' . $order->appointment->appointment_date : 'N/A' }}</td>
                </tr>
                <tr class="border-b border-line-2">
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Priority</th>
                    <td class="px-4.5 py-2.5">
                        @if($order->priority === 'urgent')
                            <span class="mc-pill p-urgent">Urgent</span>
                        @else
                            <span class="mc-pill p-info">Normal</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Status</th>
                    <td class="px-4.5 py-2.5">
                        @if($order->status === 'pending')
                            <span class="mc-pill p-pending">Pending</span>
                        @elseif($order->status === 'in-progress')
                            <span class="mc-pill p-info">In Progress</span>
                        @elseif($order->status === 'completed')
                            <span class="mc-pill p-active">Completed</span>
                        @else
                            <span class="mc-pill p-cancelled">Cancelled</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="mc-card">
    <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Requested Tests</h5></div>
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>Test</th>
                    <th>Normal Range</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Result</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td><b>{{ $item->test->name ?? 'Removed test' }}</b></td>
                        <td>{{ $item->test->normal_range ?? 'N/A' }}</td>
                        <td>{{ $item->test->unit ?? 'N/A' }}</td>
                        <td class="mc-num">${{ number_format($item->price, 2) }}</td>
                        <td>
                            @if($item->result)
                                <span class="mc-pill p-active">Reported</span>
                            @else
                                <span class="text-mut">Pending</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3"></th>
                    <th class="text-right">Total</th>
                    <th class="mc-num">${{ number_format($order->total, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($order->note)
        <div class="border-t border-line-2 px-4.5 py-4">
            <h6>Doctor Note</h6>
            <p class="mb-0 text-mut">{{ $order->note }}</p>
        </div>
    @endif
</div>

<div class="mc-card">
    <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Uploaded Reports ({{ $order->reports->count() }})</h5></div>
    @forelse($order->reports as $report)
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-2 px-4.5 py-3">
            <div>
                <strong>{{ $report->report_name }}</strong>
                <div class="text-xs text-mut">
                    Uploaded {{ $report->created_at->format('d M Y h:i A') }}
                    @if($report->notes)
                        <br>{{ $report->notes }}
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.lab-reports.download', $report->id) }}" class="mc-btn sm">
                <i class="bi bi-download"></i> Download
            </a>
        </div>
    @empty
        <div class="px-4.5 py-4 text-mut">No reports uploaded yet.</div>
    @endforelse
</div>

@endsection