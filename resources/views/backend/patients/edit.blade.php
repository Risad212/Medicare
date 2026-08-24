@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        
    <div class="tile">
        <h3 class="tile-title">Edit Patient</h3>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.patients.update', $patient->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">

                <div class="col-lg-6 mb-2">
                    <label class="mb-2">Patient Name</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $patient->name) }}"
                           required>
                </div>

                <div class="col-lg-6 mb-2">
                    <label class="mb-2">Email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ old('email', $patient->email) }}"
                           required>
                </div>

                <div class="col-lg-6 mb-2">
                    <label class="mb-2">Phone</label>
                    <input type="text"
                           name="phone"
                           class="form-control"
                           value="{{ old('phone', $patient->phone) }}">
                </div>

                <div class="col-lg-6 mb-2">
                    <label class="mb-2">Date of Birth</label>
                    <input type="date"
                           name="date_of_birth"
                           class="form-control"
                           value="{{ old('date_of_birth', $patient->date_of_birth) }}">
                </div>

                <div class="col-lg-6 mb-2">
                    <label class="mb-2">Gender</label>

                    <select name="gender" class="form-control">
                        <option value="">Select Gender</option>

                        <option value="male"
                            {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>
                            Male
                        </option>

                        <option value="female"
                            {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>
                            Female
                        </option>

                        <option value="other"
                            {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>
                            Other
                        </option>
                    </select>
                </div>

                <div class="col-lg-6 mb-2">
                    <label class="mb-2">Blood Group</label>

                    <select name="blood_group" class="form-control">
                        <option value="">Select Blood Group</option>

                        <option value="A+"
                            {{ old('blood_group', $patient->blood_group) == 'A+' ? 'selected' : '' }}>
                            A+
                        </option>

                        <option value="A-"
                            {{ old('blood_group', $patient->blood_group) == 'A-' ? 'selected' : '' }}>
                            A-
                        </option>

                        <option value="B+"
                            {{ old('blood_group', $patient->blood_group) == 'B+' ? 'selected' : '' }}>
                            B+
                        </option>

                        <option value="B-"
                            {{ old('blood_group', $patient->blood_group) == 'B-' ? 'selected' : '' }}>
                            B-
                        </option>

                        <option value="AB+"
                            {{ old('blood_group', $patient->blood_group) == 'AB+' ? 'selected' : '' }}>
                            AB+
                        </option>

                        <option value="AB-"
                            {{ old('blood_group', $patient->blood_group) == 'AB-' ? 'selected' : '' }}>
                            AB-
                        </option>

                        <option value="O+"
                            {{ old('blood_group', $patient->blood_group) == 'O+' ? 'selected' : '' }}>
                            O+
                        </option>

                        <option value="O-"
                            {{ old('blood_group', $patient->blood_group) == 'O-' ? 'selected' : '' }}>
                            O-
                        </option>
                    </select>
                </div>

                <div class="col-lg-12 mb-2">
                    <label class="mb-2">Address</label>

                    <textarea name="address"
                              class="form-control"
                              rows="4">{{ old('address', $patient->address) }}</textarea>
                </div>

            </div>

            <div class="mt-3">

                <button type="submit" class="btn btn-success">
                    Update Patient
                </button>

                <a href="{{ route('admin.patients.index') }}"
                   class="btn btn-secondary">
                    Back
                </a>

            </div>

        </form>

    </div>

</div>

</div>

@endsection
