@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Blood <em>donors</em></h1>
        <p class="mc-sub">Registered donors, their groups, and donation readiness.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.blood-donors.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add donor</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-ecg"><span>Live register</span><span>{{ $donors->total() }} donors</span></div>

<div class="mc-bar">
    <form action="{{ route('admin.blood-donors.index') }}" method="GET" class="mc-search" style="flex:1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search name, phone or email…" value="{{ request('search') }}" autocomplete="off">
    </form>
    <select name="blood_group_id" onchange="this.form.submit()" class="form-select form-select-sm mc-sel">
        <option value="">All blood groups</option>
        @foreach($bloodGroups as $group)
            <option value="{{ $group->id }}" {{ request('blood_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
        @endforeach
    </select>
    <select name="gender" onchange="this.form.submit()" class="form-select form-select-sm mc-sel">
        <option value="">All genders</option>
        <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
        <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
    </select>
    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm mc-sel">
        <option value="">All statuses</option>
        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
    </select>
</div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Donor</th>
                    <th>Blood group</th>
                    <th>Phone</th>
                    <th>Last donation</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($donors as $key => $donor)
                @php
                    $initials = implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $donor->name)), 0, 2));
                    $av = ['t', 'a', 'b', 'r', ''][(int) $donor->id % 5];
                @endphp
                <tr>
                    <td class="mc-idx">{{ $donors->firstItem() + $key }}</td>
                    <td>
                        <div class="mc-who">
                            <span class="mc-av {{ $av }}">{{ strtoupper($initials) }}</span>
                            <span><b>{{ $donor->name }}</b><span class="mc-sub2">{{ $donor->email }}</span></span>
                        </div>
                    </td>
                    <td><span class="mc-av r sm">{{ $donor->bloodGroup->name ?? '—' }}</span></td>
                    <td class="mc-num">{{ $donor->phone }}</td>
                    <td class="mc-num">{{ $donor->last_donation_date ? $donor->last_donation_date->format('Y-m-d') : 'Never' }}</td>
                    <td>
                        @if($donor->status)
                            <span class="mc-pill p-active"><i></i>Active</span>
                        @else
                            <span class="mc-pill p-inactive"><i></i>Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.blood-donors.show', $donor->id) }}" class="mc-btn sm"><i class="bi bi-eye"></i> View</a>
                            <a href="{{ route('admin.blood-donors.edit', $donor->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.blood-donors.destroy', $donor->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this donor and their donation history?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"><div class="mc-empty"><b>Nothing on this chart</b>No donors match your criteria.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $donors->firstItem() ?? 0 }}–{{ $donors->lastItem() ?? 0 }} of {{ $donors->total() }}</span>
        {{ $donors->links() }}
    </div>
</div>

@endsection
