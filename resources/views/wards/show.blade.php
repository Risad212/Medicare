@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · In-patient</p>
        <h1 class="mc-title">{{ $ward->name }}</h1>
        <p class="mc-sub">{{ $ward->description ?? 'No description' }}</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.wards.index') }}" class="mc-btn ghost">Back to wards</a>
        <a href="{{ route('admin.wards.edit', $ward) }}" class="mc-btn"><i class="bi bi-pencil"></i> Edit</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-card">
    <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Rooms ({{ $ward->rooms->count() }})</h5></div>
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Beds</th>
                    <th>Occupied</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($ward->rooms as $room)
                <tr>
                    <td><b>{{ $room->room_number }}</b></td>
                    <td>{{ $room->room_type }}</td>
                    <td class="mc-num">{{ $room->beds->count() }}</td>
                    <td class="mc-num">{{ $room->beds->where('status', 1)->count() }}</td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.rooms.show', $room->id) }}" class="mc-btn sm">View</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5"><div class="mc-empty"><b>No rooms</b>Add rooms to this ward first.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
