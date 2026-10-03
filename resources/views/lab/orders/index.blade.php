@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Laboratory</p>
        <h1 class="mc-title">Lab <em>orders</em></h1>
        <p class="mc-sub">All lab orders — search, filter by status, and export.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.exports.lab-orders') }}" class="mc-btn ghost"><i class="bi bi-download"></i> Export CSV</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-bar">
    <form action="{{ route('admin.lab-orders.index') }}" method="GET" class="flex flex-1 flex-wrap items-center gap-2.5">
        <div class="mc-search min-h-[42px] min-w-[200px]" style="flex: 7 1 0%">
            <i class="bi bi-search text-faint"></i>
            <input type="text" name="search" placeholder="Search patient or #ID..." value="{{ request('search') }}" autocomplete="off">
        </div>
        <select name="status" class="h-[42px] rounded-lg border border-line bg-white px-2.5 text-[13px] leading-none text-ink outline-none focus:border-teal" style="flex: 2.5 1 0%; min-width: 120px" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="in-progress" @selected(request('status') === 'in-progress')>In Progress</option>
            <option value="completed" @selected(request('status') === 'completed')>Completed</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
        </select>
        <button type="submit" class="mc-btn sm h-[42px] whitespace-nowrap" style="flex: 0.5 1 0%; min-width: 96px"><i class="bi bi-search"></i> Filter</button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.lab-orders.index') }}" class="mc-btn sm ghost h-[42px] flex-none">Reset</a>
        @endif
    </form>
</div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Tests</th>
                    <th>Priority</th>
                    <th>Total</th>
                    <th>Reports</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="mc-idx">{{ $order->id }}</td>
                        <td class="mc-num">{{ $order->created_at->format('d M Y') }}</td>
                        <td>
                            <b class="text-ink">{{ $order->patient_name }}</b>
                            @if($order->phone)
                                <div class="text-xs text-mut">{{ $order->phone }}</div>
                            @endif
                        </td>
                        <td>{{ $order->doctor->name ?? 'N/A' }}</td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @foreach($order->items as $item)
                                    <span class="mc-pill p-active text-[11px]">{{ $item->test->name ?? 'Removed test' }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @if($order->priority === 'urgent')
                                <span class="mc-pill p-urgent"><i></i>Urgent</span>
                            @else
                                <span class="mc-pill p-cancelled">Normal</span>
                            @endif
                        </td>
                        <td class="mc-num">${{ number_format($order->total, 2) }}</td>
                        <td class="mc-num">{{ $order->reports->count() }} <i class="bi bi-file-earmark-pdf"></i></td>
                        <td>
                            @if($order->status === 'pending')
                                <span class="mc-pill p-pending"><i></i>Pending</span>
                            @elseif($order->status === 'in-progress')
                                <span class="mc-pill p-info"><i></i>In Progress</span>
                            @elseif($order->status === 'completed')
                                <span class="mc-pill p-completed"><i></i>Completed</span>
                            @else
                                <span class="mc-pill p-cancelled"><i></i>Cancelled</span>
                            @endif
                        </td>
                        <td>
                            <div class="mc-acts">
                                <a href="{{ route('admin.lab-orders.show', $order->id) }}" class="mc-btn sm"><i class="bi bi-eye"></i> Manage</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10"><div class="mc-empty"><b>Nothing on this chart</b>No lab orders found.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }}</span>
        {{ $orders->links() }}
    </div>
</div>

@endsection
