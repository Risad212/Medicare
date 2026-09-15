@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Blood <em>groups</em></h1>
        <p class="mc-sub">The eight canonical types behind every donation and request.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.blood-groups.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add blood group</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<div class="mc-ecg"><span>Type register</span><svg viewBox="0 0 400 22" preserveAspectRatio="none"><polyline points="0,11 60,11 70,11 76,11 82,3 88,19 94,11 150,11 160,11 166,11 172,4 178,18 184,11 260,11 400,11" fill="none" stroke="#05d3b0" stroke-width="1.6"/></svg><span>{{ $bloodGroups->count() }} groups</span></div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Group</th>
                    <th>Donors</th>
                    <th>Donations</th>
                    <th>Requests</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($bloodGroups as $group)
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="mc-who">
                            <span class="mc-av r">{{ $group->name }}</span>
                            <span><b>Blood type {{ $group->name }}</b></span>
                        </div>
                    </td>
                    <td>{{ $group->donors_count }}</td>
                    <td>{{ $group->donations_count }}</td>
                    <td>{{ $group->requests_count }}</td>
                    <td>
                        @if($group->status)
                            <span class="mc-pill p-active"><i></i>Active</span>
                        @else
                            <span class="mc-pill p-inactive"><i></i>Disabled</span>
                        @endif
                    </td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.blood-groups.edit', $group->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.blood-groups.toggle', $group->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="mc-btn sm">{{ $group->status ? 'Disable' : 'Enable' }}</button>
                            </form>
                            <form action="{{ route('admin.blood-groups.destroy', $group->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this blood group?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"><div class="mc-empty"><b>Nothing on this chart</b>No blood groups yet. Add the canonical types to get started.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
