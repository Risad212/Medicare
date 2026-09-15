@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">{{ $donor->name }}<em> profile</em></h1>
        <p class="mc-sub">Blood group, donation history and current eligibility.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.blood-donors.edit', $donor->id) }}" class="mc-btn"><i class="bi bi-pencil"></i> Edit</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Blood group</h6>
        <h4 class="mb-0"><span class="mc-av r sm">{{ $donor->bloodGroup->name ?? '—' }}</span> {{ $donor->bloodGroup->name ?? '—' }}</h4>
    </div>
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Total donations</h6>
        <h4 class="mb-0">{{ $donor->totalDonations() }}</h4>
    </div>
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Eligibility</h6>
        @if($donor->isEligible($minDonationDays))
            <span class="mc-pill p-active"><i></i>Eligible</span>
        @else
            <span class="mc-pill p-inactive"><i></i>Not eligible (interval)</span>
            <small class="mt-1 block text-mut">Minimum interval: {{ $minDonationDays }} days</small>
        @endif
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2">
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Phone</h6><p class="mb-0">{{ $donor->phone }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Email</h6><p class="mb-0">{{ $donor->email ?: '—' }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Gender</h6><p class="mb-0">{{ $donor->gender ? ucfirst($donor->gender) : '—' }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Date of birth</h6><p class="mb-0">{{ $donor->date_of_birth ? $donor->date_of_birth->format('Y-m-d') : '—' }}</p>
    </div>
    <div class="mc-card p-3">
        <h6 class="mb-1 text-sm font-semibold text-mut">Address</h6><p class="mb-0">{{ $donor->address ?: '—' }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Last donation</h6><p class="mb-0">{{ $donor->last_donation_date ? $donor->last_donation_date->format('Y-m-d') : 'Never' }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Notes</h6><p class="mb-0">{{ $donor->notes ?: '—' }}</p>
        <h6 class="mb-1 mt-3 text-sm font-semibold text-mut">Status</h6><p class="mb-0">{{ $donor->status ? 'Active' : 'Inactive' }}</p>
    </div>
</div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Donation date</th>
                    <th>Bag #</th>
                    <th>Quantity</th>
                    <th>Expiry</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            @forelse($donor->donations as $key => $donation)
                <tr>
                    <td class="mc-idx">{{ $key + 1 }}</td>
                    <td class="mc-num">{{ $donation->donation_date->format('Y-m-d') }}</td>
                    <td>{{ $donation->bag_number ?: '—' }}</td>
                    <td>{{ $donation->quantity }} {{ $donation->unit }}</td>
                    <td class="mc-num">{{ $donation->expiry_date->format('Y-m-d') }}</td>
                    <td>
                        @php
                            $statusClass = match($donation->status) {
                                'available' => 'p-active',
                                'reserved', 'issued' => 'p-active',
                                'expired', 'rejected' => 'p-inactive',
                                default => '',
                            };
                        @endphp
                        <span class="mc-pill {{ $statusClass }}">{{ ucfirst($donation->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6"><div class="mc-empty"><b>No donations</b>This donor has not made a recorded donation yet.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
