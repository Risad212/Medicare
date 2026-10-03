@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · In-patient</p>
        <h1 class="mc-title">Bed <em>dashboard</em></h1>
        <p class="mc-sub">Live occupancy per ward — green is free, red is taken, gray is maintenance.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.wards.index') }}" class="mc-btn ghost">Wards</a>
        <a href="{{ route('admin.rooms.index') }}" class="mc-btn ghost">Rooms</a>
        <a href="{{ route('admin.beds.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add bed</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 mt-0 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@forelse($wards as $ward)
    <div class="mc-card mb-4">
        <div class="border-b border-line px-4.5 py-3.5 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">{{ $ward->name }}</h5>
            <span class="mc-pill p-progress">{{ $ward->available_beds_count }}/{{ $ward->beds_count }} available</span>
        </div>
        <div class="px-4.5 py-4">
            @forelse($ward->rooms as $room)
                <div class="mb-3">
                    <div class="small fw-bold text-mut mb-2">Room {{ $room->room_number }} · {{ $room->room_type }}</div>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($room->beds as $bed)
                            @php
                                $chip = [
                                    'background' => '#dcfce7',
                                    'border' => '#bbf7d0',
                                    'color' => '#166534',
                                ];
                                if ($bed->status == 1) {
                                    $chip = ['background' => '#fee2e2', 'border' => '#fecaca', 'color' => '#991b1b'];
                                } elseif ($bed->status == 2) {
                                    $chip = ['background' => '#f1f5f9', 'border' => '#e2e8f0', 'color' => '#64748b'];
                                }
                            @endphp
                            <div class="px-3 py-2" style="background:{{ $chip['background'] }};border:1px solid {{ $chip['border'] }};color:{{ $chip['color'] }};border-radius:12px;min-width:150px">
                                <div class="fw-bold">{{ $bed->bed_number }}</div>
                                <div class="small">{{ $bed->status_label }}</div>
                                @if($bed->status == 1)
                                    <div class="small fw-semibold">{{ $bed->currentPatient?->name ?? '—' }}</div>
                                    <form action="{{ route('admin.beds.discharge', $bed->id) }}" method="POST" class="mt-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius:8px" onclick="return confirm('Discharge this patient?')">Discharge</button>
                                    </form>
                                @elseif($bed->status == 0)
                                    <form action="{{ route('admin.beds.assign', $bed->id) }}" method="POST" class="mt-1 d-flex gap-1">
                                        @csrf
                                        <select name="patient_user_id" class="form-select form-select-sm" style="min-width:120px" required>
                                            <option value="">Patient…</option>
                                            @foreach($patients as $patient)
                                                <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-success" style="border-radius:8px">Assign</button>
                                    </form>
                                @endif
                                <div class="mt-1 d-flex gap-2">
                                    <a href="{{ route('admin.beds.edit', $bed->id) }}" class="small">Edit</a>
                                    <form action="{{ route('admin.beds.destroy', $bed->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="small text-danger border-0 bg-transparent p-0" onclick="return confirm('Delete this bed?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <span class="text-mut small">No beds in this room yet.</span>
                        @endforelse
                    </div>
                </div>
            @empty
                <p class="text-mut small mb-0">No rooms in this ward yet.</p>
            @endforelse
        </div>
    </div>
@empty
    <div class="mc-card"><div class="mc-empty"><b>No wards</b>Create wards, rooms and beds to see live availability here.</div></div>
@endforelse

@endsection
