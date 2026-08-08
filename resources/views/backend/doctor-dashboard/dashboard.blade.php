@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <h3 class="tile-title">Welcome, Dr. {{ auth()->user()->name }}</h3>
        </div>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row">
    <div class="col-md-4">
        <div class="widget-small primary coloured-icon">
            <i class="icon bi bi-calendar-check fs-1"></i>
            <div class="info">
                <h4>Today's Appointments</h4>
                <p><b>{{ $todayAppointments }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small info coloured-icon">
            <i class="icon bi bi-calendar2 fs-1"></i>
            <div class="info">
                <h4>Total Appointments</h4>
                <p><b>{{ $totalAppointments }}</b></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="widget-small warning coloured-icon">
            <i class="icon bi bi-hourglass-split fs-1"></i>
            <div class="info">
                <h4>Pending Appointments</h4>
                <p><b>{{ $pendingAppointments }}</b></p>
            </div>
        </div>
    </div>
</div>

{{-- Today's Appointments Table --}}
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <h3 class="tile-title">Today's Appointments</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient Name</th>
                            <th>Phone</th>
                            <th>Time</th>
                            <th>Visit Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayAppointmentsList as $appointment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $appointment->patient_name }}</td>
                            <td>{{ $appointment->phone }}</td>
                            <td>{{ $appointment->timeSlot->time ?? 'N/A' }}</td>
                            <td>
                                @if($appointment->visit_type == 1) First Visit
                                @elseif($appointment->visit_type == 2) Second Visit
                                @else Report Review
                                @endif
                            </td>
                            <td>
                                @if($appointment->status == 0)
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($appointment->status == 1)
                                    <span class="badge bg-info">Approved</span>
                                @else
                                    <span class="badge bg-success">Completed</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No appointments today.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection