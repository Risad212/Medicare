@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Issue <em>#{{ $issue->id }}</em></h1>
        <p class="mc-sub">Recorded handover details for this unit.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.blood-requests.show', $issue->request_id) }}" class="mc-btn ghost">Back to request</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Patient</h6><p class="mb-0 font-semibold">{{ $issue->patient->name }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Request</h6><p class="mb-0">#{{ $issue->request->id }} · {{ $issue->request->bloodGroup->name }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Blood group</h6><p class="mb-0"><span class="mc-av r sm">{{ $issue->bloodGroup->name }}</span></p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Quantity</h6><p class="mb-0">{{ $issue->quantity }} {{ $issue->unit }}</p>
    </div>
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Issue date</h6><p class="mb-0">{{ $issue->issue_date->format('Y-m-d') }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Bag / unit</h6><p class="mb-0">{{ $issue->donation->bag_number ?: '—' }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Donor</h6><p class="mb-0">{{ $issue->donation->donor->name ?? '—' }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Issued by</h6><p class="mb-0">{{ $issue->issuer->name }}</p>
    </div>
</div>

<div class="mc-card p-3">
    <h6 class="mb-1 text-sm font-semibold text-mut">Receiver</h6><p class="mb-0">{{ $issue->receiver_name ?: $issue->patient->name }} · {{ $issue->receiver_phone ?: '—' }}</p>
    <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Notes</h6><p class="mb-0">{{ $issue->notes ?: '—' }}</p>
</div>

@endsection