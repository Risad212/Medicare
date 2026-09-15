@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Records</p>
        <h1 class="mc-title">Patient <em>details</em></h1>
        <p class="mc-sub">Profile overview and appointment history.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.patients.edit', $patient->id) }}" class="mc-btn"><i class="bi bi-pencil"></i> Edit Patient</a>
        <a href="{{ route('admin.patients.index') }}" class="mc-btn ghost">Back</a>
    </div>
</div>

<div class="grid grid-cols-2 gap-4">
    <div class="mc-card p-5">
        <h5 class="mb-4 text-[15px] font-bold">Personal Information</h5>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-mut">Name</span>
                <p class="mt-1 text-ink">{{ $patient->name }}</p>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-mut">Email</span>
                <p class="mt-1 text-ink">{{ $patient->email }}</p>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-mut">Phone</span>
                <p class="mt-1 text-ink">{{ $patient->phone ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-mut">Date of Birth</span>
                <p class="mt-1 text-ink">{{ $patient->date_of_birth ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-mut">Gender</span>
                <p class="mt-1 text-ink">{{ $patient->gender ? ucfirst($patient->gender) : 'N/A' }}</p>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wide text-mut">Blood Group</span>
                <p class="mt-1 text-ink">{{ $patient->blood_group ?? 'N/A' }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-xs font-bold uppercase tracking-wide text-mut">Address</span>
                <p class="mt-1 text-ink">{{ $patient->address ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <div class="mc-card">
        <div class="border-b border-line-2 px-4.5 py-3">
            <h5 class="text-[15px] font-bold">Appointment History</h5>
        </div>

        @if($patient->appointments->count())
            <div class="overflow-x-auto">
                <table class="mc-tbl">
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
                                <td class="mc-idx">{{ $key + 1 }}</td>
                                <td>{{ $appointment->doctor->name ?? 'N/A' }}</td>
                                <td class="mc-num">{{ $appointment->appointment_date }}</td>
                                <td class="mc-num">{{ $appointment->timeSlot->time ?? 'N/A' }}</td>
                                <td>
                                    @if($appointment->visit_type == 1)
                                        First Visit
                                    @elseif($appointment->visit_type == 2)
                                        Second Visit
                                    @elseif($appointment->visit_type == 3)
                                        Report Review
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if($appointment->status == 0)
                                        <span class="mc-pill p-pending"><i></i>Pending</span>
                                    @elseif($appointment->status == 1)
                                        <span class="mc-pill p-active"><i></i>Approved</span>
                                    @elseif($appointment->status == 2)
                                        <span class="mc-pill p-completed"><i></i>Completed</span>
                                    @else
                                        <span class="mc-pill p-cancelled"><i></i>Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mc-empty"><b>Nothing on this chart</b>No appointments found for this patient.</div>
        @endif
    </div>
</div>

@endsection
