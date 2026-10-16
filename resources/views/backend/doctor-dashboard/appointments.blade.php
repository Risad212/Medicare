@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Appointment Clinic</p>
        <h1 class="mc-title">My <em>appointments</em></h1>
        <p class="mc-sub">Approve, complete or cancel patient visits.</p>
    </div>
</div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient Name</th>
                    <th>Phone</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Visit Type</th>
                    @if(\App\Support\Module::enabled('zoom'))
                    <th>Consultation</th>
                    @endif
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                <tr>
                    <td class="mc-idx">{{ $loop->iteration }}</td>
                    <td><b>{{ $appointment->patient_name }}</b></td>
                    <td class="mc-num">{{ $appointment->phone }}</td>
                    <td class="mc-num">{{ $appointment->appointment_date }}</td>
                    <td class="mc-num">{{ $appointment->timeSlot->time ?? 'N/A' }}</td>
                    <td>
                        @if($appointment->visit_type == 1) First Visit
                        @elseif($appointment->visit_type == 2) Second Visit
                        @elseif($appointment->visit_type == 3) Report Review
                        @else N/A
                        @endif
                    </td>
                    @if(\App\Support\Module::enabled('zoom'))
                    <td>
                        @if($appointment->consultation_mode === 'telehealth')
                            @php($zoomStatus = $appointment->zoomMeeting?->sync_status ?? 'pending')
                            @if($zoomStatus === 'ready')
                                <a href="{{ route('doctor.telehealth.host', $appointment) }}" class="mc-btn sm">Start Zoom</a>
                            @elseif($zoomStatus === 'failed')
                                <span class="text-danger">Zoom failed ({{ $appointment->zoomMeeting->last_error_code }})</span>
                                @if($appointment->status == 1)
                                <form action="{{ route('doctor.telehealth.retry', $appointment) }}" method="POST" class="mt-1">@csrf<button type="submit" class="mc-btn sm">Retry</button></form>
                                @endif
                            @else
                                <span>Meeting pending</span>
                            @endif
                        @else
                            <span>In-person</span>
                        @endif
                    </td>
                    @endif
                    <td>
                        @if($appointment->status == 0)
                            <span class="mc-pill p-pending"><i></i>Pending</span>
                            <div class="mc-acts mt-1">
                                <form action="{{ route('doctor.appointments.update', $appointment->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="1">
                                    <button type="submit" class="mc-btn sm">
                                        <i class="bi bi-check"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('doctor.appointments.update', $appointment->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="3">
                                    <button type="submit" class="mc-btn sm danger-ghost">
                                        <i class="bi bi-x"></i> Cancel
                                    </button>
                                </form>
                            </div>
                        @elseif($appointment->status == 1)
                            <span class="mc-pill p-active"><i></i>Confirmed</span>
                            <div class="mc-acts mt-1">
                                <form action="{{ route('doctor.appointments.update', $appointment->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="2">
                                    <button type="submit" class="mc-btn sm dark">
                                        <i class="bi bi-check-circle"></i> Complete
                                    </button>
                                </form>
                                <form action="{{ route('doctor.appointments.update', $appointment->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="3">
                                    <button type="submit" class="mc-btn sm danger-ghost">
                                        <i class="bi bi-x"></i> Cancel
                                    </button>
                                </form>
                            </div>
                        @elseif($appointment->status == 2)
                            <span class="mc-pill p-completed"><i></i>Completed</span>
                        @else
                            <span class="mc-pill p-cancelled"><i></i>Cancelled</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ \App\Support\Module::enabled('zoom') ? 8 : 7 }}"><div class="mc-empty"><b>Nothing on this chart</b>No appointments found.</div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        {{ $appointments->links() }}
    </div>
</div>

@endsection