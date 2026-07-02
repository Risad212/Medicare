@extends('frontend.layouts.front-app')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'Appoinment'
])

<!--========== Appoinment Section ==========-->
<section class="appoinment-page">
    <div class="container">
        <div class="form-wrap">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('appointment.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <input type="text" name="name" placeholder="Your Name"
                        value="{{ old('patient_name') }}">
                    @error('patient_name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <input type="number" name="phone" placeholder="Your Number"
                        value="{{ old('phone') }}">
                    @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <select name="doctor_id">
                        <option value="">Select Doctor</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('doctor_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <select name="visit_type">
                        <option value="">Visit Type</option>
                        <option value="1">First Visit</option>
                        <option value="2">Second Visit</option>
                        <option value="3">Report Review</option>
                    </select>
                </div>

                <div class="mb-4">
                    <input type="number" name="age" placeholder="Your Age"
                        value="{{ old('age') }}">
                </div>

                <div class="mb-4">
                    <select name="gender">
                        <option value="">Select Gender</option>
                        <option value="1">Male</option>
                        <option value="2">Female</option>
                        <option value="3">Other</option>
                    </select>
                </div>

                <div class="mb-4">
                    <input type="date" name="date"
                        value="{{ old('appointment_date') }}">
                    @error('appointment_date') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="text-center">
                    <button type="submit" class="appoinment-btn">Submit</button>
                </div>

            </form>
        </div>
    </div>
</section>

@endsection