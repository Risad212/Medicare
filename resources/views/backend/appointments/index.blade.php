@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">

            <div class="tile-title-w-btn d-flex justify-content-between align-items-center mb-3">

                <h3 class="tile-title mb-0">All Appointment</h3>

                <form action="{{ route('admin.appointments.index') }}" method="GET" class="d-flex">
                    <input type="text"
                           name="search"
                           style="width: 300px;"
                           class="form-control me-2"
                           id="appointmentSearch"
                           placeholder="Search appoinetment..."
                           value="{{ request('search') }}">
                </form>

            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif


            <div class="table-responsive">

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
                            <th>Time Slot</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>


                    <tbody>

                    @forelse($appointments as $key => $appointment)

                        <tr>

                            <td>
                                {{ $appointments->firstItem() + $key }}
                            </td>


                            <td>
                                {{ $appointment->doctor->name ?? 'N/A' }}
                            </td>


                            <td>
                                {{ $appointment->patient_name }}
                            </td>


                            <td>
                                {{ $appointment->age }}
                            </td>


                            <td>
                                @if($appointment->gender == 1)
                                    Male
                                @elseif($appointment->gender == 2)
                                    Female
                                @else
                                    Other
                                @endif
                            </td>


                            <td>
                                {{ $appointment->phone }}
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
                                {{ $appointment->appointment_date }}
                            </td>

                            <td>
                                {{ $appointment->time }}
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

                            <td>

                                <a href="{{ route('admin.appointments.edit', $appointment->id) }}"
                                   class="btn btn-sm btn-primary">

                                    Edit

                                </a>


                                <form action="{{ route('admin.appointments.destroy', $appointment->id) }}"
                                      method="POST"
                                      style="display:inline;">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?')">

                                        Delete

                                    </button>

                                </form>

                            </td>

                        </tr>


                    @empty

                        <tr>
                            <td colspan="10" class="text-center">
                                No appointments found.
                            </td>
                        </tr>

                    @endforelse


                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            <div class="mt-3 table-pagination">
                {{ $appointments->links() }}
            </div>


        </div>

    </div>
</div>

@endsection