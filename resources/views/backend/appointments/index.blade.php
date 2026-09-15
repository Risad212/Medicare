@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Front desk</p>
        <h1 class="mc-title">Appoint<em>ments</em></h1>
        <p class="mc-sub">Every booking in one register. Pending needs a decision; the rest is history.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.appointments.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Book appointment</a>
        <a href="{{ route('admin.exports.appointments') }}" class="mc-btn ghost"><i class="bi bi-download"></i> Export CSV</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-ecg"><span>Live register</span><svg viewBox="0 0 400 22" preserveAspectRatio="none"><polyline points="0,11 60,11 70,11 76,11 82,3 88,19 94,11 150,11 160,11 166,11 172,4 178,18 184,11 260,11 400,11" fill="none" stroke="#05d3b0" stroke-width="1.6"/></svg><span>{{ $appointments->total() }} records</span></div>

<div class="mc-bar">
    <form action="{{ route('admin.appointments.index') }}" method="GET" class="mc-search flex-1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search patient, phone, doctor…" value="{{ request('search') }}" autocomplete="off">
    </form>
</div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Visit type</th>
                    <th>Date</th>
                    <th>Time slot</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($appointments as $key => $appointment)
                @php
                    $initials = implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $appointment->patient_name)), 0, 2));
                    $av = ['t', 'a', 'b', 'r', ''][$key % 5];
                    $statusMap = [0 => ['Pending', 'p-pending'], 1 => ['Confirmed', 'p-confirmed'], 2 => ['Completed', 'p-completed']];
                    [$statusLabel, $statusClass] = $statusMap[(int) $appointment->status] ?? ['Cancelled', 'p-cancelled'];
                @endphp
                <tr>
                    <td class="mc-idx">{{ $appointments->firstItem() + $key }}</td>
                    <td>
                        <div class="mc-who">
                            <span class="mc-av {{ $av }}">{{ strtoupper($initials) }}</span>
                            <span><b>{{ $appointment->patient_name }}</b></span>
                        </div>
                    </td>
                    <td><b>{{ $appointment->doctor->name ?? 'N/A' }}</b></td>
                    <td class="mc-num">{{ $appointment->age ?? '–' }}</td>
                    <td>
                        @if($appointment->gender == 1)
                            Male
                        @elseif($appointment->gender == 2)
                            Female
                        @else
                            Other
                        @endif
                    </td>
                    <td class="mc-num">{{ $appointment->phone }}</td>
                    <td>{{ $appointment->email ?? 'N/A' }}</td>
                    <td>{{ $appointment->visit_type_label }}</td>
                    <td class="mc-num">{{ $appointment->appointment_date }}</td>
                    <td class="mc-num">{{ $appointment->timeSlot->time ?? 'N/A' }}</td>
                    <td><span class="mc-pill {{ $statusClass }}"><i></i>{{ $statusLabel }}</span></td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="mc-btn sm dark">Edit</a>
                            <form action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST" >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12"><div class="mc-empty"><b>Nothing on this chart</b>No appointments found. Book the first one to get the day moving.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $appointments->firstItem() ?? 0 }}–{{ $appointments->lastItem() ?? 0 }} of {{ $appointments->total() }}</span>
        {{ $appointments->links() }}
    </div>
</div>

@endsection
