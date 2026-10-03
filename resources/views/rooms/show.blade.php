@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · In-patient</p>
        <h1 class="mc-title">Room {{ $room->room_number }}</h1>
        <p class="mc-sub">{{ $room->ward->name ?? 'No ward' }} · {{ $room->room_type }}</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.rooms.index') }}" class="mc-btn ghost">Back to rooms</a>
        <a href="{{ route('admin.rooms.edit', $room) }}" class="mc-btn"><i class="bi bi-pencil"></i> Edit</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-card">
    <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Beds ({{ $room->beds->count() }})</h5></div>
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>Bed</th>
                    <th>Status</th>
                    <th>Patient</th>
                    <th>Admitted</th>
                </tr>
            </thead>
            <tbody>
            @forelse($room->beds as $bed)
                <tr>
                    <td><b>{{ $bed->bed_number }}</b></td>
                    <td>{{ $bed->status_label }}</td>
                    <td>{{ $bed->currentPatient?->name ?? '—' }}</td>
                    <td class="mc-num">{{ $bed->admitted_at?->format('d M Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4"><div class="mc-empty"><b>No beds</b>Add beds to this room.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
