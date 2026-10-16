@extends('frontend.layouts.front-app')

@section('meta_title', $seo->meta_title ?? 'Medicare')
@section('meta_description', $seo->meta_description ?? '')
@section('meta_keywords', $seo->meta_keywords ?? '')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => __('messages.appointment.title')
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
                    <input type="text" name="patient_name" placeholder="{{ __('messages.appointment.name') }}"
                        value="{{ old('patient_name') }}">
                    @error('patient_name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <input type="tel" name="phone" placeholder="{{ __('messages.appointment.phone') }}"
                        value="{{ old('phone') }}">
                    @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                @if(\App\Support\Module::enabled('zoom'))
                <div class="mb-4">
                    <select name="consultation_mode" id="consultation_mode">
                        <option value="in_person" {{ old('consultation_mode', 'in_person') === 'in_person' ? 'selected' : '' }}>{{ __('messages.appointment.in_person') }}</option>
                        <option value="telehealth" {{ old('consultation_mode') === 'telehealth' ? 'selected' : '' }}>{{ __('messages.appointment.telehealth') }}</option>
                    </select>
                    @error('consultation_mode') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                @endif

                <div class="mb-4">
                    <input type="email" name="email" placeholder="{{ __('messages.appointment.email_cancel') }}"
                        id="patient_email"
                        data-cancel-placeholder="{{ __('messages.appointment.email_cancel') }}"
                        data-telehealth-placeholder="{{ __('messages.appointment.email_telehealth') }}"
                        @if(old('consultation_mode') === 'telehealth') required @endif
                        value="{{ old('email') }}">
                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <select name="doctor_id" id="doctor_id">
                        <option value="">{{ __('messages.appointment.doctor') }}</option>
                        @if(isset($doctors) && $doctors->count())
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->name ?? 'Doctor' }}
                                </option>
                            @endforeach
                        @else
                            <option value="" disabled>{{ __('messages.appointment.no_doctors') }}</option>
                        @endif
                    </select>
                    @error('doctor_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <select name="visit_type">
                        <option value="">{{ __('messages.appointment.visit_type') }}</option>

                        <option value="1" {{ old('visit_type') == '1' ? 'selected' : '' }}>
                            {{ __('messages.appointment.first_visit') }}
                        </option>

                        <option value="2" {{ old('visit_type') == '2' ? 'selected' : '' }}>
                            {{ __('messages.appointment.second_visit') }}
                        </option>

                        <option value="3" {{ old('visit_type') == '3' ? 'selected' : '' }}>
                            {{ __('messages.appointment.report_review') }}
                        </option>
                    </select>
                    @error('visit_type') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <input type="number" name="age" placeholder="{{ __('messages.appointment.age') }}"
                        value="{{ old('age') }}">
                </div>

                <div class="mb-4">
                    <select name="gender">
                        <option value="">{{ __('messages.appointment.gender') }}</option>
                        <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>{{ __('messages.appointment.male') }}</option>
                        <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>{{ __('messages.appointment.female') }}</option>
                        <option value="3" {{ old('gender') == '3' ? 'selected' : '' }}>{{ __('messages.appointment.other') }}</option>
                    </select>
                </div>

                {{-- Date input --}}
                <div class="mb-4">
                    <input type="date" name="appointment_date" id="appointment_date"
                        min="{{ date('Y-m-d') }}"
                        aria-label="{{ __('messages.appointment.date') }}"
                        value="{{ old('appointment_date') }}">
                    @error('appointment_date') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- Time Slot dropdown --}}
                <div class="mb-4">
                    <select name="time_slot_id" id="time_slot_id">
                        <option value="">{{ __('messages.appointment.time_slot') }}</option>
                    </select>
                    @error('time_slot_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="text-center">
                    <button type="submit" class="appoinment-btn">{{ __('messages.appointment.submit') }}</button>
                </div>

            </form>
        </div>
    </div>
</section>

<script>
    window.getSlotsUrl = "{{ route('get.slots') }}";
    const consultationMode = document.getElementById('consultation_mode');
    const updatePatientEmailRequirement = function () {
        const email = document.getElementById('patient_email');
        if (email) {
            email.required = consultationMode?.value === 'telehealth';
            email.placeholder = email.required
                ? email.dataset.telehealthPlaceholder
                : email.dataset.cancelPlaceholder;
        }
    };
    consultationMode?.addEventListener('change', updatePatientEmailRequirement);
    updatePatientEmailRequirement();
</script>

@endsection