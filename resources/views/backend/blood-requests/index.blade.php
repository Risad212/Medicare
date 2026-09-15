@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Blood <em>requests</em></h1>
        <p class="mc-sub">Patient needs, urgency levels, and approval status.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.blood-requests.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> New request</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<div class="mc-ecg"><span>Live register</span><svg viewBox="0 0 400 22" preserveAspectRatio="none"><polyline points="0,11 60,11 70,11 76,11 82,3 88,19 94,11 150,11 160,11 166,11 172,4 178,18 184,11 260,11 400,11" fill="none" stroke="#05d3b0" stroke-width="1.6"/></svg><span>{{ $requests->total() }} requests</span></div>

<div class="mc-bar">
    <form action="{{ route('admin.blood-requests.index') }}" method="GET" class="mc-search" style="flex:1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search patient…" value="{{ request('search') }}" autocomplete="off">
    </form>
    <select name="blood_group_id" onchange="this.form.submit()" class="form-select form-select-sm mc-sel">
        <option value="">All groups</option>
        @foreach($bloodGroups as $group)
            <option value="{{ $group->id }}" {{ request('blood_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
        @endforeach
    </select>
    <select name="urgency" onchange="this.form.submit()" class="form-select form-select-sm mc-sel">
        <option value="">All urgencies</option>
        <option value="normal" {{ request('urgency') === 'normal' ? 'selected' : '' }}>Normal</option>
        <option value="urgent" {{ request('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
        <option value="emergency" {{ request('urgency') === 'emergency' ? 'selected' : '' }}>Emergency</option>
    </select>
    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm mc-sel">
        <option value="">All statuses</option>
        @foreach(['pending', 'approved', 'partially_approved', 'fulfilled', 'rejected', 'cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
        @endforeach
    </select>
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
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($requests as $key => $r)
                <tr>
                    <td class="mc-idx">#{{ $r->id }}</td>
                    <td>
                        <div class="mc-who">
                            <span><b>{{ $r->patient->name }}</b><span class="mc-sub2">{{ $r->department ?: 'General' }}</span></span>
                        </div>
                    </td>
                    <td><span class="mc-av r sm">{{ $r->bloodGroup->name }}</span></td>
                    <td class="mc-num">{{ $r->quantity }} {{ $r->unit }}</td>
                    <td>
                        @php
                            $uClass = match($r->urgency) {
                                'emergency' => 'p-inactive',
                                'urgent' => '',
                                default => 'p-active',
                            };
                        @endphp
                        <span class="mc-pill {{ $uClass }}">{{ ucfirst($r->urgency) }}</span>
                    </td>
                    <td class="mc-num">{{ $r->required_date->format('Y-m-d') }}</td>
                    <td><span class="mc-pill {{ $r->status === 'pending' ? '' : 'p-active' }}">{{ ucfirst(str_replace('_', ' ', $r->status)) }}</span></td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.blood-requests.show', $r->id) }}" class="mc-btn sm"><i class="bi bi-eye"></i> View</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8"><div class="mc-empty"><b>Nothing on this chart</b>No blood requests match your filters.</div></td></tr>
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