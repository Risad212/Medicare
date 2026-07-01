@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">
            <h3 class="tile-title">Edit Appointment</h3>

            <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">

                    <div class="col-lg-6 mb-2">
                        <label>Doctor</label>
                        <input type="text" class="form-control" value="{{ $appointment->doctor->name ?? 'N/A' }}" readonly>
                        <input type="hidden" name="doctor_id" value="{{ $appointment->doctor_id }}">
                    </div>

                    <div class="col-lg-6 mb-2">
                        <label>Visit Type</label>
                        <select name="visit_type" class="form-control">
                            <option value="1" {{ $appointment->visit_type == 1 ? 'selected' : '' }}>First Visit</option>
                            <option value="2" {{ $appointment->visit_type == 2 ? 'selected' : '' }}>Second Visit</option>
                            <option value="3" {{ $appointment->visit_type == 3 ? 'selected' : '' }}>Report Review</option>
                        </select>
                    </div>

                    <div class="col-lg-6 mb-2">
                        <label>Patient Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $appointment->patient_name }}">
                    </div>

                    <div class="col-lg-6 mb-2">
                        <label>Age</label>
                        <input type="number" name="age" class="form-control" value="{{ $appointment->age }}">
                    </div>

                    <div class="col-lg-6 mb-2">
                        <label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="1" {{ $appointment->gender == 1 ? 'selected' : '' }}>Male</option>
                            <option value="2" {{ $appointment->gender == 2 ? 'selected' : '' }}>Female</option>
                            <option value="3" {{ $appointment->gender == 3 ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-lg-6 mb-2">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ $appointment->phone }}">
                    </div>

                    <div class="col-lg-6 mb-2">
                        <label>Appointment Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $appointment->appointment_date }}">
                    </div>

                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Update Appointment</button>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">Back</a>
                </div>

            </form>

        </div>

    </div>
</div>

@endsection