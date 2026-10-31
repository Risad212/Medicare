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
                <div class="alert alert-success" role="status" aria-live="polite">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger" role="alert" aria-atomic="true">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('appointment.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-1">
                    <div class="col-12 col-md-6">
                        <label class="mc-sr-only" for="patient_name">{{ __('messages.appointment.name') }}</label>
                        <input type="text" name="patient_name" id="patient_name" placeholder="{{ __('messages.appointment.name') }}"
                            autocomplete="name" maxlength="255" required
                            value="{{ old('patient_name') }}">
                        @error('patient_name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="mc-sr-only" for="phone">{{ __('messages.appointment.phone') }}</label>
                        <input type="tel" name="phone" id="phone" placeholder="{{ __('messages.appointment.phone') }}"
                            autocomplete="tel" inputmode="tel" maxlength="20" required pattern="[0-9+\-\s]+"
                            value="{{ old('phone') }}">
                        @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="mc-sr-only" for="patient_email">{{ __('messages.appointment.email_telehealth') }}</label>
                        <input type="email" name="email" placeholder="{{ __('messages.appointment.email_cancel') }}"
                            id="patient_email"
                            autocomplete="email" maxlength="255"
                            data-cancel-placeholder="{{ __('messages.appointment.email_cancel') }}"
                            data-telehealth-placeholder="{{ __('messages.appointment.email_telehealth') }}"
                            @if(old('consultation_mode') === 'telehealth') required @endif
                            value="{{ old('email') }}">
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    @if(\App\Support\Module::enabled('zoom'))
                    <div class="col-12 col-md-6">
                        <label class="mc-sr-only" for="consultation_mode">{{ __('messages.appointment.title') }}</label>
                        <select name="consultation_mode" id="consultation_mode" required>
                            <option value="in_person" {{ old('consultation_mode', 'in_person') === 'in_person' ? 'selected' : '' }}>{{ __('messages.appointment.in_person') }}</option>
                            <option value="telehealth" {{ old('consultation_mode') === 'telehealth' ? 'selected' : '' }}>{{ __('messages.appointment.telehealth') }}</option>
                        </select>
                        @error('consultation_mode') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    @endif

                    <div class="col-12 col-md-6">
                        <label class="mc-sr-only" for="doctor_id">{{ __('messages.appointment.doctor') }}</label>
                        <select name="doctor_id" id="doctor_id" required>
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

                    <div class="col-12 col-md-6">
                        <label class="mc-sr-only" for="visit_type">{{ __('messages.appointment.visit_type') }}</label>
                        <select name="visit_type" id="visit_type" required>
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

                    <div class="col-12 col-md-6">
                        <label class="mc-sr-only" for="age">{{ __('messages.appointment.age') }}</label>
                        <input type="number" name="age" id="age" placeholder="{{ __('messages.appointment.age') }}"
                            inputmode="numeric" min="0" max="120"
                            value="{{ old('age') }}">
                        @error('age') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="mc-sr-only" for="gender">{{ __('messages.appointment.gender') }}</label>
                        <select name="gender" id="gender" required>
                            <option value="">{{ __('messages.appointment.gender') }}</option>
                            <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>{{ __('messages.appointment.male') }}</option>
                            <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>{{ __('messages.appointment.female') }}</option>
                            <option value="3" {{ old('gender') == '3' ? 'selected' : '' }}>{{ __('messages.appointment.other') }}</option>
                        </select>
                        @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="mc-sr-only" for="appointment_date">{{ __('messages.appointment.date') }}</label>
                        <input type="date" name="appointment_date" id="appointment_date"
                            min="{{ date('Y-m-d') }}" required
                            value="{{ old('appointment_date') }}">
                        @error('appointment_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="mc-sr-only" for="time_slot_id">{{ __('messages.appointment.time_slot') }}</label>
                        <select name="time_slot_id" id="time_slot_id" required>
                            <option value="">{{ __('messages.appointment.time_slot') }}</option>
                        </select>
                        @error('time_slot_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="appoinment-btn">{{ __('messages.appointment.submit') }}</button>
                </div>

            </form>
        </div>
    </div>
</section>

<script>
    window.getSlotsUrl = "{{ route('get.slots') }}";
    // Deep-link preselect: /appointment?doctor_id=N selects the doctor the
    // visitor came from (services page, referrers). Stale/unknown ids are
    // ignored; a value the server re-populated (old input) always wins.
    (function () {
        var select = document.getElementById('doctor_id');
        if (!select || select.value !== '') return;
        var preselect = new URLSearchParams(window.location.search).get('doctor_id');
        if (preselect && select.querySelector('option[value="' + preselect + '"]')) {
            select.value = preselect;
        }
    })();
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