@extends('frontend.layouts.front-app')

@section('meta_title', 'My Profile')
@section('meta_description', 'Manage your patient profile')
@section('meta_keywords', 'patient profile, appointments, medicare')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'My Profile'
])

<section class="patient-profile py-5">
    <div class="container">

        {{-- Messages --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">

            <li class="nav-item" role="presentation">
                <button
                    class="nav-link active"
                    id="profile-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#profile"
                    type="button"
                    role="tab">
                    My Profile
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    id="appointments-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#appointments"
                    type="button"
                    role="tab">
                    My Appointments
                </button>
            </li>

        </ul>

        {{-- Tab Content --}}
        <div class="tab-content" id="profileTabsContent">

            {{-- ================= PROFILE ================= --}}
            <div
                class="tab-pane fade show active"
                id="profile"
                role="tabpanel">

                <div class="row g-4">

                    {{-- Patient Info --}}
                    <div class="col-lg-4">

                        <div class="card border-0 shadow-sm text-center p-4">

                            @if($user->profile_image)

                                <img
                                    src="{{ asset('storage/' . $user->profile_image) }}"
                                    alt="{{ $user->name }}"
                                    class="rounded-circle mx-auto mb-3"
                                    style="width:110px;height:110px;object-fit:cover;">

                            @else

                                <div
                                    class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3"
                                    style="width:110px;height:110px;">

                                    <span class="fs-1 text-muted">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>

                                </div>

                            @endif

                            <h4 class="mb-1">
                                {{ $user->name }}
                            </h4>

                            <p class="text-muted mb-4">
                                Patient
                            </p>

                            <div class="text-start">

                                <p class="mb-3">
                                    <strong>Email:</strong><br>
                                    <span class="text-muted">
                                        {{ $user->email }}
                                    </span>
                                </p>

                                <p class="mb-3">
                                    <strong>Phone:</strong><br>
                                    <span class="text-muted">
                                        {{ $user->phone ?? 'Not added' }}
                                    </span>
                                </p>

                                <p class="mb-0">
                                    <strong>Blood Group:</strong><br>
                                    <span class="text-muted">
                                        {{ $user->blood_group ?? 'Not added' }}
                                    </span>
                                </p>

                            </div>

                            <form
                                action="{{ route('logout') }}"
                                method="POST"
                                class="mt-4">

                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-outline-danger w-100">
                                    Logout
                                </button>

                            </form>

                        </div>

                    </div>


                    {{-- Edit Profile --}}
                    <div class="col-lg-8">

                        <div class="card border-0 shadow-sm">

                            <div class="card-header bg-white py-3">
                                <h4 class="mb-0">
                                    Personal Information
                                </h4>
                            </div>

                            <div class="card-body p-4">

                                <form
                                    action="{{ route('profile.update') }}"
                                    method="POST"
                                    enctype="multipart/form-data">

                                    @csrf
                                    @method('PUT')

                                    <div class="row">

                                        {{-- Name --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                Full Name
                                            </label>

                                            <input
                                                type="text"
                                                name="name"
                                                class="form-control @error('name') is-invalid @enderror"
                                                value="{{ old('name', $user->name) }}"
                                                required>

                                            @error('name')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>


                                        {{-- Email --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                Email
                                            </label>

                                            <input
                                                type="email"
                                                name="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                value="{{ old('email', $user->email) }}"
                                                required>

                                            @error('email')
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>


                                        {{-- Phone --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                Phone
                                            </label>

                                            <input
                                                type="text"
                                                name="phone"
                                                class="form-control"
                                                value="{{ old('phone', $user->phone) }}">
                                        </div>


                                        {{-- DOB --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                Date of Birth
                                            </label>

                                            <input
                                                type="date"
                                                name="date_of_birth"
                                                class="form-control"
                                                value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                                        </div>


                                        {{-- Gender --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                Gender
                                            </label>

                                            <select name="gender" class="form-select">

                                                <option value="">
                                                    Select Gender
                                                </option>

                                                <option
                                                    value="male"
                                                    {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>
                                                    Male
                                                </option>

                                                <option
                                                    value="female"
                                                    {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>
                                                    Female
                                                </option>

                                                <option
                                                    value="other"
                                                    {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>
                                                    Other
                                                </option>

                                            </select>
                                        </div>


                                        {{-- Blood Group --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                Blood Group
                                            </label>

                                            <select name="blood_group" class="form-select">

                                                <option value="">
                                                    Select Blood Group
                                                </option>

                                                @foreach([
                                                    'A+', 'A-',
                                                    'B+', 'B-',
                                                    'AB+', 'AB-',
                                                    'O+', 'O-'
                                                ] as $group)

                                                    <option
                                                        value="{{ $group }}"
                                                        {{ old('blood_group', $user->blood_group) === $group ? 'selected' : '' }}>
                                                        {{ $group }}
                                                    </option>

                                                @endforeach

                                            </select>
                                        </div>


                                        {{-- Address --}}
                                        <div class="col-12 mb-3">
                                            <label class="form-label">
                                                Address
                                            </label>

                                            <textarea
                                                name="address"
                                                rows="4"
                                                class="form-control">{{ old('address', $user->address) }}</textarea>
                                        </div>


                                        {{-- Profile Image --}}
                                        <div class="col-12 mb-4">
                                            <label class="form-label">
                                                Profile Photo
                                            </label>

                                            <input
                                                type="file"
                                                name="profile_image"
                                                class="form-control"
                                                accept="image/jpeg,image/png,image/webp">

                                            <small class="text-muted">
                                                JPG, PNG or WEBP. Maximum 2MB.
                                            </small>
                                        </div>

                                    </div>

                                    <button
                                        type="submit"
                                        class="btn btn-primary px-4">
                                        Update Profile
                                    </button>

                                </form>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ================= APPOINTMENTS ================= --}}
            <div
                class="tab-pane fade"
                id="appointments"
                role="tabpanel">

                <div class="card border-0 shadow-sm">

                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0">
                            My Appointments
                        </h4>
                    </div>

                    <div class="card-body">

                        @if($appointments->count())

                            <div class="table-responsive">

                                <table class="table table-hover align-middle">

                                    <thead>
                                        <tr>
                                            <th>Doctor</th>
                                            <th>Date</th>
                                            <th>Time Slot</th>
                                            <th>Visit Type</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach($appointments as $appointment)

                                            <tr>

                                                {{-- Doctor --}}
                                                <td>
                                                    {{ $appointment->doctor->name ?? 'N/A' }}
                                                </td>

                                                {{-- Date --}}
                                                <td>
                                                    {{ $appointment->appointment_date
                                                        ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y')
                                                        : 'N/A'
                                                    }}
                                                </td>

                                                {{-- Specific Time Slot --}}
                                                <td>
                                                    @if($appointment->timeSlot)
                                                        {{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('h:i A') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('h:i A') }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>

                                                {{-- Visit Type --}}
                                                <td>
                                                    {{ ucfirst($appointment->visit_type ?? 'N/A') }}
                                                </td>

                                                {{-- Status --}}
                                                <td>

                                                    @if($appointment->status == 1)

                                                        <span class="badge bg-success">
                                                            Confirmed
                                                        </span>

                                                    @else

                                                        <span class="badge bg-warning text-dark">
                                                            Pending
                                                        </span>

                                                    @endif

                                                </td>

                                                <td>
                                                    @if($appointment->status == 0)
                                                        <form action="{{ route('appointment.cancel', $appointment->id) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')

                                                            <button type="submit"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Are you sure you want to cancel this appointment?')">
                                                                Cancel
                                                            </button>
                                                        </form>
                                                    @elseif($appointment->status == 1)
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif($appointment->status == 2)
                                                        <span class="badge bg-danger">Cancelled</span>
                                                    @endif
                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        @else

                            <div class="text-center py-5">

                                <h5>
                                    No Appointments Found
                                </h5>

                                <p class="text-muted">
                                    You haven't booked any appointments yet.
                                </p>

                                <a
                                    href="{{ route('appointment') }}"
                                    class="btn btn-primary">
                                    Book Appointment
                                </a>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>

@endsection