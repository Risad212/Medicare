@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">
            <h3 class="tile-title">Appointments List</h3>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Doctor</th>
                        <th>Patient Name</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Phone</th>
                        <th>Visit Type</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($appointments as $key => $appointment)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $appointment->doctor->name ?? 'N/A' }}</td>
                            <td>{{ $appointment->patient_name }}</td>

                            {{-- AGE --}}
                            <td>{{ $appointment->age }}</td>

                            {{-- GENDER --}}
                            <td>
                                @if($appointment->gender == 1)
                                    Male
                                @elseif($appointment->gender == 2)
                                    Female
                                @else
                                    Other
                                @endif
                            </td>

                            <td>{{ $appointment->phone }}</td>

                            <td>
                                @if($appointment->visit_type == 1)
                                    First Visit
                                @elseif($appointment->visit_type == 2)
                                    Second Visit
                                @else
                                    Report Review
                                @endif
                            </td>

                            <td>{{ $appointment->appointment_date }}</td>

                            <td>
                                @if($appointment->status == 0)
                                    <span class="badge bg-warning">Pending</span>
                                @else
                                    <span class="badge bg-success">Completed</span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-primary">Edit</a>

                                <form action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </div>
</div>

@endsection