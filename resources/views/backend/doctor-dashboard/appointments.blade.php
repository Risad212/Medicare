@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <h3 class="tile-title">My Appointments</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient Name</th>
                            <th>Phone</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Visit Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $appointment->patient_name }}</td>
                            <td>{{ $appointment->phone }}</td>
                            <td>{{ $appointment->appointment_date }}</td>
                            <td>{{ $appointment->timeSlot->time ?? 'N/A' }}</td>
                            <td>
                                @if($appointment->visit_type == 1) First Visit
                                @elseif($appointment->visit_type == 2) Second Visit
                                @else Report Review
                                @endif
                            </td>
                          <td>
                            @if($appointment->status == 0)
                                <span class="badge badge-warning" style="background-color: #ffc107; color: #000; padding: 5px 10px;">Pending</span>
                                <form action="{{ route('doctor.appointments.update', $appointment->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="1">
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('doctor.appointments.update', $appointment->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="2">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-x"></i> Cancel
                                    </button>
                                </form>
                            @elseif($appointment->status == 1)
                                <span class="badge badge-success" style="background-color: #28a745; color: #fff; padding: 5px 10px;">Confirmed</span>
                            @else
                                <span class="badge badge-danger" style="background-color: #dc3545; color: #fff; padding: 5px 10px;">Cancelled</span>
                            @endif
                        </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No appointments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection