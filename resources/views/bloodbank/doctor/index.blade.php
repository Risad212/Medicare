@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Blood <em>requests</em></h1>
        <p class="mc-sub">Requests you raised for your patients and their status.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('doctor.blood-requests.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> New request</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<div class="mc-bar">
    <form action="{{ route('doctor.blood-requests.index') }}" method="GET" class="mc-search" style="width:auto">
        <select name="status" class="form-select form-select-sm mc-sel" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(['pending', 'approved', 'partially_approved', 'fulfilled', 'rejected', 'cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Group</th>
                    <th>Qty</th>
                    <th>Urgency</th>
                    <th>Required</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($requests as $key => $r)
                <tr>
                    <td class="mc-idx">#{{ $r->id }}</td>
                    <td><b>{{ $r->patient->name }}</b></td>
                    <td><span class="mc-av r sm">{{ $r->bloodGroup->name }}</span></td>
                    <td class="mc-num">{{ $r->quantity }} {{ $r->unit }}</td>
                    <td><span class="mc-pill {{ $r->urgency === 'emergency' ? 'p-inactive' : ($r->urgency === 'urgent' ? '' : 'p-active') }}">{{ ucfirst($r->urgency) }}</span></td>
                    <td class="mc-num">{{ $r->required_date->format('Y-m-d') }}</td>
                    <td><span class="mc-pill {{ $r->status === 'pending' ? '' : 'p-active' }}">{{ ucfirst(str_replace('_', ' ', $r->status)) }}</span></td>
                    <td class="text-right">
                        <a href="{{ route('doctor.blood-requests.show', $r->id) }}" class="mc-btn sm"><i class="bi bi-eye"></i> View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><div class="mc-empty"><b>Nothing on this chart</b>No blood requests found.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }}</span>
        {{ $requests->links() }}
    </div>
</div>

@endsection