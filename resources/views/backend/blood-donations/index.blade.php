@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Blood <em>donations</em></h1>
        <p class="mc-sub">Every collected unit — from draw to issue or expiry.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.blood-donations.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Record donation</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<div class="mc-ecg"><span>Live register</span><span>{{ $donations->total() }} donations</span></div>

<form action="{{ route('admin.blood-donations.index') }}" method="GET" class="mc-bar">
    <div class="mc-search" style="flex:1">
        <i class="bi bi-search text-faint"></i>
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search donor or bag no…" autocomplete="off" aria-label="Search donations">
    </div>
    <select name="blood_group_id" class="mc-sel" onchange="this.form.submit()" aria-label="Filter by blood group">
        <option value="">All groups</option>
        @foreach($bloodGroups as $group)
            <option value="{{ $group->id }}" @selected(request('blood_group_id') == $group->id)>{{ $group->name }}</option>
        @endforeach
    </select>
    <select name="status" class="mc-sel" onchange="this.form.submit()" aria-label="Filter by status">
        <option value="">All statuses</option>
        @foreach(['collected', 'testing', 'available', 'reserved', 'issued', 'expired', 'rejected'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <input type="date" name="from" class="mc-date" value="{{ request('from') }}" title="From" aria-label="From date">
    <input type="date" name="to" class="mc-date" value="{{ request('to') }}" title="To" aria-label="To date">
    <button class="mc-btn sm" type="submit">Filter</button>
    @if(array_filter(request()->only('search', 'blood_group_id', 'status', 'from', 'to')))
        <a href="{{ route('admin.blood-donations.index') }}" class="mc-btn sm ghost">Clear</a>
    @endif
</form>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Donor</th>
                    <th>Group</th>
                    <th>Qty</th>
                    <th>Donation date</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($donations as $key => $donation)
                @php
                    $initials = $donation->donor ? implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $donation->donor->name)), 0, 2)) : '—';
                @endphp
                <tr>
                    <td class="mc-idx">{{ $donations->firstItem() + $key }}</td>
                    <td>
                        <div class="mc-who">
                            <span class="mc-av b">{{ strtoupper($initials) }}</span>
                            <span><b>{{ $donation->donor->name ?? 'Removed' }}</b></span>
                        </div>
                    </td>
                    <td><span class="mc-av r sm">{{ $donation->bloodGroup->name ?? '—' }}</span></td>
                    <td class="mc-num">{{ $donation->quantity }} {{ $donation->unit }}</td>
                    <td class="mc-num">{{ $donation->donation_date->format('Y-m-d') }}</td>
                    <td class="mc-num">{{ $donation->expiry_date->format('Y-m-d') }}</td>
                    <td>
                        @php
                            $sClass = match($donation->status) {
                                'available' => 'p-active',
                                'reserved', 'issued' => 'p-active',
                                'expired', 'rejected' => 'p-inactive',
                                default => '',
                            };
                        @endphp
                        <span class="mc-pill {{ $sClass }}">{{ ucfirst($donation->status) }}</span>
                    </td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.blood-donations.show', $donation->id) }}" class="mc-btn sm"><i class="bi bi-eye"></i></a>
                            @if(in_array($donation->status, ['collected', 'testing']))
                                <form action="{{ route('admin.blood-donations.status', $donation->id) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="available">
                                    <button type="submit" class="mc-btn sm" title="Make available" onclick="return confirm('Mark this donation as available?')"><i class="bi bi-check-lg"></i> Available</button>
                                </form>
                            @endif
                            <a href="{{ route('admin.blood-donations.edit', $donation->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.blood-donations.destroy', $donation->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this donation?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8"><div class="mc-empty"><b>Nothing on this chart</b>No donations match your filters.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $donations->firstItem() ?? 0 }}–{{ $donations->lastItem() ?? 0 }} of {{ $donations->total() }}</span>
        {{ $donations->links() }}
    </div>
</div>

@endsection
