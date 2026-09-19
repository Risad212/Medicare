@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Blood <em>issues</em></h1>
        <p class="mc-sub">Complete handover history — every unit, receiver, and date.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<div class="mc-ecg"><span>Live register</span><span>{{ $issues->total() }} issues</span></div>

<div class="mc-bar">
    <form action="{{ route('admin.blood-issues.index') }}" method="GET" class="mc-search" style="flex:1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search patient…" value="{{ request('search') }}" autocomplete="off">
    </form>
    <select name="blood_group_id" onchange="this.form.submit()" class="form-select form-select-sm mc-sel">
        <option value="">All groups</option>
        @foreach($bloodGroups as $group)
            <option value="{{ $group->id }}" {{ request('blood_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
        @endforeach
    </select>
    <form action="{{ route('admin.blood-issues.index') }}" method="GET" class="mc-search flex items-center gap-1" style="width:auto">
        <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" title="From">
        <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" title="To">
        <button class="mc-btn sm" type="submit">Filter</button>
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
                    <th>Issue date</th>
                    <th>Receiver</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($issues as $key => $issue)
                <tr>
                    <td class="mc-idx">#{{ $issue->id }}</td>
                    <td>
                        <div class="mc-who">
                            <span><b>{{ $issue->patient->name }}</b><span class="mc-sub2">Request #{{ $issue->request_id }}</span></span>
                        </div>
                    </td>
                    <td><span class="mc-av r sm">{{ $issue->bloodGroup->name }}</span></td>
                    <td class="mc-num">{{ $issue->quantity }} {{ $issue->unit }}</td>
                    <td class="mc-num">{{ $issue->issue_date->format('Y-m-d') }}</td>
                    <td>{{ $issue->receiver_name ?: $issue->patient->name }}</td>
                    <td class="text-right">
                        <a href="{{ route('admin.blood-issues.show', $issue->id) }}" class="mc-btn sm"><i class="bi bi-eye"></i> View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="mc-empty"><b>Nothing on this chart</b>No blood has been issued yet.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $issues->firstItem() ?? 0 }}–{{ $issues->lastItem() ?? 0 }} of {{ $issues->total() }}</span>
        {{ $issues->links() }}
    </div>
</div>

@endsection