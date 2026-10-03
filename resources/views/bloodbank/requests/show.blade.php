@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Request <em>#{{ $request->id }}</em></h1>
        <p class="mc-sub">Requested vs available, reservation state, and issue history.</p>
    </div>
    <div class="mc-head-acts">
        @if($request->status === 'pending')
            <form action="{{ route('admin.blood-requests.approve', $request->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Approve and reserve blood for this request?')">
                @csrf
                <button type="submit" class="mc-btn"><i class="bi bi-check-lg"></i> Approve &amp; reserve</button>
            </form>
            <form action="{{ route('admin.blood-requests.reject', $request->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Reject this request?')">
                @csrf
                <button type="submit" class="mc-btn danger-ghost"><i class="bi bi-x-lg"></i> Reject</button>
            </form>
            <form action="{{ route('admin.blood-requests.destroy', $request->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this pending request?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="mc-btn ghost"><i class="bi bi-trash"></i> Delete</button>
            </form>
        @else
            <a href="{{ route('admin.blood-issues.create', $request->id) }}" class="mc-btn"><i class="bi bi-droplet-half"></i> Issue blood</a>
            @if(! in_array($request->status, ['fulfilled', 'rejected', 'cancelled']))
                <form action="{{ route('admin.blood-requests.cancel', $request->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Cancel this request and release reservations?')">
                    @csrf
                    <button type="submit" class="mc-btn ghost">Cancel</button>
                </form>
            @endif
        @endif
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Patient</h6><p class="mb-0 font-semibold">{{ $request->patient->name }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Blood group</h6><p class="mb-0"><span class="mc-av r sm">{{ $request->bloodGroup->name }}</span></p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Quantity</h6><p class="mb-0">{{ $request->quantity }} {{ $request->unit }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Doctor</h6><p class="mb-0">{{ $request->doctor->name ?? '—' }}</p>
    </div>
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Urgency</h6>
        <p class="mb-0"><span class="mc-pill {{ $request->urgency === 'emergency' ? 'p-inactive' : ($request->urgency === 'urgent' ? '' : 'p-active') }}">{{ ucfirst($request->urgency) }}</span></p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Required date</h6><p class="mb-0">{{ $request->required_date->format('Y-m-d') }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Request date</h6><p class="mb-0">{{ $request->created_at->format('Y-m-d H:i') }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Status</h6><p class="mb-0">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</p>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Available (<b>{{ $request->bloodGroup->name }}</b>)</h6>
        <h4 class="mb-0">{{ $availableUnits }} units <small class="text-mut">({{ $availableQty }} ml)</small></h4>
    </div>
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Issued so far</h6>
        <h4 class="mb-0">{{ $request->issuedQuantity() }} {{ $request->unit }}</h4>
    </div>
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Remaining need</h6>
        <h4 class="mb-0">{{ max(0, $request->quantity - $request->issuedQuantity()) }} {{ $request->unit }}</h4>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
    <div class="mc-card">
        <div class="border-b border-line p-3"><h5 class="mb-0">Reason</h5></div>
        <div class="p-3">{{ $request->reason ?: '—' }}</div>
    </div>
    <div class="mc-card">
        <div class="border-b border-line p-3"><h5 class="mb-0">Reserved bags</h5></div>
        <div class="overflow-x-auto">
            <table class="mc-tbl">
                <thead><tr><th>Bag</th><th>Qty</th><th>Expiry</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($reservedUnits as $bag)
                    <tr>
                        <td>{{ $bag->bag_number ?: '#'.$bag->id }}</td>
                        <td>{{ $bag->quantity }} {{ $bag->unit }}</td>
                        <td class="mc-num">{{ $bag->expiry_date->format('Y-m-d') }}</td>
                        <td><span class="mc-pill {{ $bag->status === 'reserved' ? '' : 'p-active' }}">{{ ucfirst($bag->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="mc-empty"><b>No reservations</b>Nothing is held against this request.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mc-card">
    <div class="border-b border-line p-3"><h5 class="mb-0">Issue history</h5></div>
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Issue date</th>
                    <th>Bag</th>
                    <th>Qty</th>
                    <th>Receiver</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($request->issues as $key => $issue)
                <tr>
                    <td class="mc-idx">{{ $key + 1 }}</td>
                    <td class="mc-num">{{ $issue->issue_date->format('Y-m-d') }}</td>
                    <td>{{ $issue->donation->bag_number ?: '#'.$issue->donation_id }}</td>
                    <td>{{ $issue->quantity }} {{ $issue->unit }}</td>
                    <td>{{ $issue->receiver_name ?: $request->patient->name }}</td>
                    <td class="text-right">
                        <a href="{{ route('admin.blood-issues.show', $issue->id) }}" class="mc-btn sm">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="mc-empty"><b>Not yet issued</b>No blood has been handed over for this request.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection