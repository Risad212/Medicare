@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Request <em>#{{ $request->id }}</em></h1>
        <p class="mc-sub">Status and issue history for this patient's request.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('doctor.blood-requests.index') }}" class="mc-btn ghost">Back to requests</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Patient</h6><p class="mb-0 font-semibold">{{ $request->patient->name }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Blood group</h6><p class="mb-0"><span class="mc-av r sm">{{ $request->bloodGroup->name }}</span></p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Quantity</h6><p class="mb-0">{{ $request->quantity }} {{ $request->unit }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Issued so far</h6><p class="mb-0">{{ $request->issuedQuantity() }} {{ $request->unit }}</p>
    </div>
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Urgency</h6>
        <p class="mb-0"><span class="mc-pill {{ $request->urgency === 'emergency' ? 'p-inactive' : ($request->urgency === 'urgent' ? '' : 'p-active') }}">{{ ucfirst($request->urgency) }}</span></p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Required date</h6><p class="mb-0">{{ $request->required_date->format('Y-m-d') }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Request date</h6><p class="mb-0">{{ $request->created_at->format('Y-m-d') }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Status</h6><p class="mb-0">{{ ucfirst(str_replace('_', ' ', $request->status)) }}</p>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Reason</h6><p class="mb-0">{{ $request->reason ?: '—' }}</p>
    </div>
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Reserved bags</h6>
        @forelse($reservedUnits as $bag)
            <p class="mb-1">{{ $bag->bag_number ?: '#' . $bag->id }} — {{ $bag->quantity }} ml — {{ ucfirst($bag->status) }}</p>
        @empty
            <p class="mb-0 text-mut">Nothing reserved yet.</p>
        @endforelse
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
                    <th>Qty</th>
                    <th>Receiver</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
            @forelse($request->issues as $key => $issue)
                <tr>
                    <td class="mc-idx">{{ $key + 1 }}</td>
                    <td class="mc-num">{{ $issue->issue_date->format('Y-m-d') }}</td>
                    <td>{{ $issue->quantity }} {{ $issue->unit }}</td>
                    <td>{{ $issue->receiver_name ?: $request->patient->name }}</td>
                    <td>{{ $issue->notes ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><div class="mc-empty"><b>Not yet issued</b>No blood has been handed over yet.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection