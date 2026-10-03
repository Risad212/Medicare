@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Diagnostics</p>
        <h1 class="mc-title">Lab <em>requests</em></h1>
        <p class="mc-sub">Ordered lab work and its progress.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('doctor.lab-orders.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> New Lab Request</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-bar">
    <form action="{{ route('doctor.lab-orders.index') }}" method="GET" class="flex items-center">
        <select name="status" class="form-select form-select-sm mc-sel" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="in-progress" @selected(request('status') === 'in-progress')>In Progress</option>
            <option value="completed" @selected(request('status') === 'completed')>Completed</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
        </select>
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
                    <th>Tests</th>
                    <th>Priority</th>
                    <th>Total</th>
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
                        <div class="mc-who">
                            <span><b>{{ $order->patient_name }}</b>@if($order->phone)<span class="mc-sub2">{{ $order->phone }}</span>@endif</span>
                        </div>
                    </td>
                    <td>
                        <div class="flex flex-wrap gap-1">
                            @foreach($order->items as $item)
                                <span class="mc-pill p-active">{{ $item->test->name ?? 'Removed test' }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        @if($order->priority === 'urgent')
                            <span class="mc-pill p-urgent">Urgent</span>
                        @else
                            <span class="mc-pill p-info">Normal</span>
                        @endif
                    </td>
                    <td class="mc-num">${{ number_format($order->total, 2) }}</td>
                    <td>
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
                    <td class="text-right">
                        <a href="{{ route('doctor.lab-orders.show', $order->id) }}" class="mc-btn sm"><i class="bi bi-eye"></i> View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8"><div class="mc-empty"><b>Nothing on this chart</b>No lab requests found.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        {{ $orders->links() }}
    </div>
</div>

@endsection