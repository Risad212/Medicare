@extends('frontend.layouts.front-app')

@section('meta_title', 'My Profile')
@section('meta_description', 'Manage your patient profile')
@section('meta_keywords', 'patient profile, medicare')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'My Profile'
])

<section class="patient-profile py-5">
    <div class="container">

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Validation Errors --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">

            {{-- =========================
                Patient Information
            ========================== --}}
            <div class="col-lg-4">

                <div class="card border-0 shadow-sm text-center p-4">

                    {{-- Profile Image --}}
                    @if($user->profile_image)

                        <img
                            src="{{ asset('storage/' . $user->profile_image) }}"
                            alt="{{ $user->name }}"
                            class="rounded-circle mx-auto mb-3"
                            style="width: 110px; height: 110px; object-fit: cover;"
                        >

                    @else

                        <div
                            class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width: 110px; height: 110px;"
                        >
                            <span class="fs-1 text-muted">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>

                    @endif

                    {{-- Patient Name --}}
                    <h4 class="mb-1">
                        {{ $user->name }}
                    </h4>

                    {{-- Role --}}
                    <p class="text-muted mb-4">
                        Patient
                    </p>

                    {{-- Patient Information --}}
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

                    {{-- Logout --}}
                    <form
                        action="{{ route('logout') }}"
                        method="POST"
                        class="mt-4"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-outline-danger w-100"
                        >
                            Logout
                        </button>

                    </form>

                </div>

            </div>


            {{-- =========================
                Edit Profile
            ========================== --}}
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
                            enctype="multipart/form-data"
                        >

                            @csrf
                            @method('PUT')

                            <div class="row">

                                {{-- Full Name --}}
                                <div class="col-md-6 mb-3">

                                    <label
                                        for="name"
                                        class="form-label"
                                    >
                                        Full Name
                                    </label>

                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $user->name) }}"
                                        required
                                    >

                                    @error('name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                {{-- Email --}}
                                <div class="col-md-6 mb-3">

                                    <label
                                        for="email"
                                        class="form-label"
                                    >
                                        Email
                                    </label>

                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}"
                                        required
                                    >

                                    @error('email')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                {{-- Phone --}}
                                <div class="col-md-6 mb-3">

                                    <label
                                        for="phone"
                                        class="form-label"
                                    >
                                        Phone
                                    </label>

                                    <input
                                        type="text"
                                        name="phone"
                                        id="phone"
                                        class="form-control @error('phone') is-invalid @enderror"
                                        value="{{ old('phone', $user->phone) }}"
                                    >

                                    @error('phone')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                {{-- Date of Birth --}}
                                <div class="col-md-6 mb-3">

                                    <label
                                        for="date_of_birth"
                                        class="form-label"
                                    >
                                        Date of Birth
                                    </label>

                                    <input
                                        type="date"
                                        name="date_of_birth"
                                        id="date_of_birth"
                                        class="form-control @error('date_of_birth') is-invalid @enderror"
                                        value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                                    >

                                    @error('date_of_birth')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                {{-- Gender --}}
                                <div class="col-md-6 mb-3">

                                    <label
                                        for="gender"
                                        class="form-label"
                                    >
                                        Gender
                                    </label>

                                    <select
                                        name="gender"
                                        id="gender"
                                        class="form-select @error('gender') is-invalid @enderror"
                                    >

                                        <option value="">
                                            Select Gender
                                        </option>

                                        <option
                                            value="male"
                                            {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}
                                        >
                                            Male
                                        </option>

                                        <option
                                            value="female"
                                            {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}
                                        >
                                            Female
                                        </option>

                                        <option
                                            value="other"
                                            {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}
                                        >
                                            Other
                                        </option>

                                    </select>

                                    @error('gender')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                {{-- Blood Group --}}
                                <div class="col-md-6 mb-3">

                                    <label
                                        for="blood_group"
                                        class="form-label"
                                    >
                                        Blood Group
                                    </label>

                                    <select
                                        name="blood_group"
                                        id="blood_group"
                                        class="form-select @error('blood_group') is-invalid @enderror"
                                    >

                                        <option value="">
                                            Select Blood Group
                                        </option>

                                        @foreach([
                                            'A+',
                                            'A-',
                                            'B+',
                                            'B-',
                                            'AB+',
                                            'AB-',
                                            'O+',
                                            'O-'
                                        ] as $group)

                                            <option
                                                value="{{ $group }}"
                                                {{ old('blood_group', $user->blood_group) === $group ? 'selected' : '' }}
                                            >
                                                {{ $group }}
                                            </option>

                                        @endforeach

                                    </select>

                                    @error('blood_group')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                {{-- Address --}}
                                <div class="col-12 mb-3">

                                    <label
                                        for="address"
                                        class="form-label"
                                    >
                                        Address
                                    </label>

                                    <textarea
                                        name="address"
                                        id="address"
                                        rows="4"
                                        class="form-control @error('address') is-invalid @enderror"
                                    >{{ old('address', $user->address) }}</textarea>

                                    @error('address')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                {{-- Profile Image --}}
                                <div class="col-12 mb-4">

                                    <label
                                        for="profile_image"
                                        class="form-label"
                                    >
                                        Profile Photo
                                    </label>

                                    <input
                                        type="file"
                                        name="profile_image"
                                        id="profile_image"
                                        class="form-control @error('profile_image') is-invalid @enderror"
                                        accept="image/jpeg,image/png,image/webp"
                                    >

                                    @error('profile_image')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <small class="text-muted">
                                        JPG, PNG or WEBP. Maximum 2MB.
                                    </small>

                                </div>

                            </div>


                            {{-- Update Button --}}
                            <button
                                type="submit"
                                class="btn btn-primary px-4"
                            >
                                Update Profile
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>

@endsection