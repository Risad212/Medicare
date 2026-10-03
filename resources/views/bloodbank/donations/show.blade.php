@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Donation <em>detail</em></h1>
        <p class="mc-sub">Unit facts and every issue linked to this bag.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.blood-donations.edit', $donation->id) }}" class="mc-btn"><i class="bi bi-pencil"></i> Edit</a>
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
        <h6 class="mb-1 text-sm font-semibold text-mut">Donor</h6><p class="mb-0">{{ $donation->donor->name ?? 'Removed donor' }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Blood group</h6><p class="mb-0"><span class="mc-av r sm">{{ $donation->bloodGroup->name ?? '—' }}</span></p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Quantity</h6><p class="mb-0">{{ $donation->quantity }} {{ $donation->unit }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Bag number</h6><p class="mb-0">{{ $donation->bag_number ?: '—' }}</p>
    </div>
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Collection location</h6><p class="mb-0">{{ $donation->collection_location ?: '—' }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Donation date</h6><p class="mb-0">{{ $donation->donation_date->format('Y-m-d') }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Expiry date</h6><p class="mb-0">{{ $donation->expiry_date->format('Y-m-d') }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Status</h6>
        <p class="mb-0">
            <span class="mc-pill {{ in_array($donation->status, ['available', 'reserved', 'issued']) ? 'p-active' : ($donation->status === 'collected' || $donation->status === 'testing' ? '' : 'p-inactive') }}">{{ ucfirst($donation->status) }}</span>
        </p>
        @if($donation->creator)
            <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Recorded by</h6><p class="mb-0">{{ $donation->creator->name }}</p>
        @endif
    </div>
</div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Issue date</th>
                    <th>Request</th>
                    <th>Patient</th>
                    <th>Quantity</th>
                </tr>
            </thead>
            <tbody>
            @forelse($donation->issues as $key => $issue)
                <tr>
                    <td class="mc-idx">{{ $key + 1 }}</td>
                    <td class="mc-num">{{ $issue->issue_date->format('Y-m-d') }}</td>
                    <td>#{{ $issue->request->id }}</td>
                    <td>{{ $issue->patient->name ?? '—' }}</td>
                    <td>{{ $issue->quantity }} {{ $issue->unit }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5"><div class="mc-empty"><b>Not yet issued</b>This bag has no issue records.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
