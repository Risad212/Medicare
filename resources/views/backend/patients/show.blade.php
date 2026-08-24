@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

    <div class="tile">

        <h3 class="tile-title">Patient Details</h3>

        <div class="row">

            <div class="col-lg-6 mb-3">
                <strong>Name:</strong>
                <p>{{ $patient->name }}</p>
            </div>

            <div class="col-lg-6 mb-3">
                <strong>Email:</strong>
                <p>{{ $patient->email }}</p>
            </div>

            <div class="col-lg-6 mb-3">
                <strong>Phone:</strong>
                <p>{{ $patient->phone ?? 'N/A' }}</p>
            </div>

            <div class="col-lg-6 mb-3">
                <strong>Date of Birth:</strong>
                <p>{{ $patient->date_of_birth ?? 'N/A' }}</p>
            </div>

            <div class="col-lg-6 mb-3">
                <strong>Gender:</strong>
                <p>{{ $patient->gender ? ucfirst($patient->gender) : 'N/A' }}</p>
            </div>

            <div class="col-lg-6 mb-3">
                <strong>Blood Group:</strong>
                <p>{{ $patient->blood_group ?? 'N/A' }}</p>
            </div>

            <div class="col-lg-12 mb-3">
                <strong>Address:</strong>
                <p>{{ $patient->address ?? 'N/A' }}</p>
            </div>

        </div>

        <hr>

        <h4 class="mb-3">Appointment History</h4>

        @if($patient->appointments->count())

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Visit Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    @foreach($patient->appointments as $key => $appointment)

                        <tr>
                            <td>{{ $key + 1 }}</td>

                            <td>
                                {{ $appointment->doctor->name ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $appointment->appointment_date }}
                            </td>

                            <td>
                                {{ $appointment->timeSlot->time ?? 'N/A' }}
                            </td>

                            <td>
                                @if($appointment->visit_type == 1)
                                    First Visit
                                @elseif($appointment->visit_type == 2)
                                    Second Visit
                                @else
                                    Report Review
                                @endif
                            </td>

                            <td>
                                @if($appointment->status == 0)
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($appointment->status == 1)
                                    <span class="badge bg-success">Approved</span>
                                @elseif($appointment->status == 2)
                                    <span class="badge bg-info">Completed</span>
                                @else
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <p>No appointments found for this patient.</p>

        @endif

        <div class="mt-3">

            <a href="{{ route('admin.patients.edit', $patient->id) }}"
               class="btn btn-primary">
                Edit Patient
            </a>

            <a href="{{ route('admin.patients.index') }}"
               class="btn btn-secondary">
                Back
            </a>

        </div>

    </div>

</div>


</div>

@endsection
