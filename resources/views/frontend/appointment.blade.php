@extends('frontend.layouts.front-app')

@section('meta_title', $seo->meta_title ?? 'Medicare')
@section('meta_description', $seo->meta_description ?? '')
@section('meta_keywords', $seo->meta_keywords ?? '')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => $pageTitle ?? 'Appointment'
])

<!--========== Appoinment Section ==========-->
<section class="appoinment-page">
    <div class="container">
        <div class="form-wrap">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('appointment.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <input type="text" name="patient_name" placeholder="Your Name"
                        value="{{ old('patient_name') }}">
                    @error('patient_name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <input type="number" name="phone" placeholder="Your Number"
                        value="{{ old('phone') }}">
                    @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <select name="doctor_id" id="doctor_id">
                        <option value="">Select Doctor</option>
                        @if(isset($doctors) && $doctors->count())
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->name ?? 'Doctor' }}
                                </option>
                            @endforeach
                        @else
                            <option value="" disabled>No doctors available</option>
                        @endif
                    </select>
                    @error('doctor_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <select name="visit_type">
                        <option value="">Visit Type</option>
                        <option value="1" {{ old('visit_type') == '1' ? 'selected' : '' }}>First Visit</option>
                        <option value="2" {{ old('visit_type') == '2' ? 'selected' : '' }}>Second Visit</option>
                        <option value="3" {{ old('visit_type') == '3' ? 'selected' : '' }}>Report Review</option>
                    </select>
                </div>

                <div class="mb-4">
                    <input type="number" name="age" placeholder="Your Age"
                        value="{{ old('age') }}">
                </div>

                <div class="mb-4">
                    <select name="gender">
                        <option value="">Select Gender</option>
                        <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Male</option>
                        <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>Female</option>
                        <option value="3" {{ old('gender') == '3' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                {{-- Date input --}}
                <div class="mb-4">
                    <input type="date" name="appointment_date" id="appointment_date"
                        min="{{ date('Y-m-d') }}"
                        value="{{ old('date') }}">
                    @error('date') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Time Slot dropdown --}}
                <div class="mb-4">
                    <select name="time_slot_id" id="time_slot_id">
                        <option value="">Select Date & Doctor First</option>
                    </select>
                    @error('time_slot_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="text-center">
                    <button type="submit" class="appoinment-btn">Submit</button>
                </div>

            </form>
        </div>
    </div>
</section>

<script>
    const getSlotsUrl = "{{ route('get.slots') }}";
</script>

@endsection