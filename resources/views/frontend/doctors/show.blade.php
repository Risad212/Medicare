@extends('frontend.layouts.front-app')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'Doctor Details'
])

<section class="doctor-details">
    <div class="container my-4">
        <div class="row">

            <div class="col-lg-4">
                <div class="doctor-details-image">
                    @if($doctor->image)
                        <img src="{{ asset('storage/'.$doctor->image) }}"
                             alt="{{ $doctor->name }}"
                             class="img-fluid">
                    @else
                        <div class="mc-avatar-fallback" data-notranslate>{{ mb_strtoupper(mb_substr(trim((string) ($doctor->name ?? '')), 0, 1) ?: 'D') }}</div>
                    @endif
                </div>
            </div>

            <div class="col-lg-8">
                <div class="doctor-info">
                    @php
                        $facts = [
                            [__('messages.doctor_profile.degree'), (string) $doctor->degree],
                            [__('messages.doctor_profile.department'), (string) $doctor->department],
                            [__('messages.doctor_profile.services'), (string) $doctor->services],
                            [__('messages.doctor_profile.availability'), (string) $doctor->availability],
                            [__('messages.doctor_profile.phone'), (string) $doctor->phone],
                        ];
                        $hasFacts = collect($facts)->contains(fn (array $fact) => trim($fact[1]) !== '');
                    @endphp

                    <h2 class="doctor-name" data-notranslate>{{ $doctor->name }}</h2>

                    @if(trim((string) $doctor->specialist) !== '')
                        <p class="doctor-speacilist" data-notranslate>{{ $doctor->specialist }}</p>
                    @endif

                    @if($hasFacts)
                        <dl class="mc-doctor-facts">
                            @foreach($facts as [$label, $value])
                                @if(trim($value) !== '')
                                    <div class="mc-fact row">
                                        <dt class="col-5 col-sm-4">{{ $label }}</dt>
                                        <dd class="col-7 col-sm-8 mb-0" data-notranslate>{{ $value }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    @endif
                </div>
            </div>

            @if(\App\Support\Module::enabled('reviews'))
            <div class="col-lg-12">
                <div class="doctor-reviews mt-5">
                    <h3>{{ __('messages.reviews.heading') }}</h3>

                    @if($ratingSummary['count'] > 0)
                        <div class="mb-3 fw-semibold" data-notranslate>
                            <span class="text-warning">&#9733;</span>
                            {{ __('messages.reviews.rating_of', ['avg' => $ratingSummary['avg']]) }}
                            &middot; {{ __('messages.reviews.based_on', ['count' => $ratingSummary['count']]) }}
                        </div>
                    @endif

                    <div class="row g-3">
                        @forelse($reviews as $review)
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong>{{ $review->user?->name ?? __('messages.reviews.anonymous') }}</strong>
                                        <span class="text-warning" data-notranslate>{{ str_repeat('★', (int) $review->rating) }}</span>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        {{ $review->created_at->format('d M Y') }}
                                        {{-- doctor_reviews.appointment_id is a required unique FK
                                             created only by the verified-patient review flow. --}}
                                        <span class="mc-verified-badge">{{ __('messages.trust.verified_patient') }}</span>
                                    </div>
                                    @if($review->comment)
                                        <p class="mb-0 small">{{ $review->comment }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted">{{ __('messages.reviews.empty') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <div class="col-lg-12" id="book-appoiment">
                <div class="book-appoiment mt-5">
                    <div class="appointment-here-form">

                        <a id="appointment"></a>

                        <h3>{{ __('messages.appointment.title') }}</h3>
                          @if(session('success'))
                            <div class="alert alert-success" role="status" aria-live="polite">{{ session('success') }}</div>
                          @endif

                      <form action="{{ route('appointment.store') }}" method="POST">
                         @csrf

                          <input type="hidden" name="doctor_id" value="{{ $doctor->id }}"> 

                            <div class="row">

                                <div class="col-lg-6">
                                    <label for="doctor_name_display">{{ __('messages.appointment.doctor_name') }}</label>
                                    <div class="mb-3">
                                        <input type="text"
                                               class="form-control"
                                               id="doctor_name_display"
                                               readonly
                                               value="{{ $doctor->name }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label for="visit_type">{{ __('messages.appointment.visit_type') }}</label>
                                    <div class="mb-3">
                                        <select id="visit_type" class="form-control" name="visit_type" required>
                                            <option value="" disabled {{ old('visit_type') ? '' : 'selected' }}>{{ __('messages.appointment.visit_type') }}</option>
                                            <option value="1" {{ old('visit_type') == '1' ? 'selected' : '' }}>{{ __('messages.appointment.first_visit') }}</option>
                                            <option value="2" {{ old('visit_type') == '2' ? 'selected' : '' }}>{{ __('messages.appointment.second_visit') }}</option>
                                            <option value="3" {{ old('visit_type') == '3' ? 'selected' : '' }}>{{ __('messages.appointment.report_review') }}</option>
                                        </select>
                                        @error('visit_type') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                @if(\App\Support\Module::enabled('zoom'))
                                <div class="col-lg-6">
                                    <label for="consultation_mode">{{ __('messages.appointment.mode') }}</label>
                                    <div class="mb-3">
                                        <select name="consultation_mode" id="consultation_mode" class="form-control">
                                            <option value="in_person" {{ old('consultation_mode', 'in_person') === 'in_person' ? 'selected' : '' }}>{{ __('messages.appointment.in_person') }}</option>
                                            <option value="telehealth" {{ old('consultation_mode') === 'telehealth' ? 'selected' : '' }}>{{ __('messages.appointment.telehealth') }}</option>
                                        </select>
                                        @error('consultation_mode') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                                @endif

                                <div class="col-lg-6">
                                    <label for="patient_name">{{ __('messages.appointment.name') }}</label>
                                    <div class="mb-3">
                                        <input type="text"
                                               class="form-control"
                                               id="patient_name"
                                               name="patient_name"
                                               maxlength="255"
                                               autocomplete="name"
                                               required
                                               value="{{ old('patient_name') }}"
                                               placeholder="{{ __('messages.appointment.name_placeholder') }}">
                                        @error('patient_name') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label for="patient_age">{{ __('messages.appointment.age') }}</label>
                                    <div class="mb-3">
                                        <input type="number"
                                               class="form-control"
                                               id="patient_age"
                                               name="age"
                                               min="0"
                                               max="120"
                                               inputmode="numeric"
                                               required
                                               value="{{ old('age') }}"
                                               placeholder="{{ __('messages.appointment.age') }}">
                                        @error('age') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label for="patient_gender">{{ __('messages.appointment.gender') }}</label>
                                    <div class="mb-3">
                                        <select class="form-control" name="gender" id="patient_gender" required>
                                            <option value="" disabled {{ old('gender') ? '' : 'selected' }}>{{ __('messages.appointment.gender') }}</option>
                                            <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>{{ __('messages.appointment.male') }}</option>
                                            <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>{{ __('messages.appointment.female') }}</option>
                                            <option value="3" {{ old('gender') == '3' ? 'selected' : '' }}>{{ __('messages.appointment.other') }}</option>
                                        </select>
                                        @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label for="patient_phone">{{ __('messages.appointment.phone') }}</label>
                                    <div class="mb-3">
                                        <input type="tel"
                                               inputmode="tel"
                                               pattern="[0-9+\-\s]+"
                                               class="form-control"
                                               id="patient_phone"
                                               name="phone"
                                               maxlength="20"
                                               autocomplete="tel"
                                               required
                                               value="{{ old('phone') }}"
                                               placeholder="{{ __('messages.appointment.phone') }}">
                                        @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label for="patient_email">{{ __('messages.appointment.email_cancel') }}</label>
                                    <div class="mb-3">
                                        <input type="email"
                                               class="form-control"
                                               name="email"
                                               id="patient_email"
                                               maxlength="255"
                                               autocomplete="email"
                                               data-cancel-placeholder="{{ __('messages.appointment.email_cancel') }}"
                                               data-telehealth-placeholder="{{ __('messages.appointment.email_telehealth') }}"
                                               @if(old('consultation_mode') === 'telehealth') required @endif
                                               value="{{ old('email') }}"
                                               placeholder="{{ __('messages.appointment.email_cancel') }}">
                                         @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label for="appointment_date">{{ __('messages.appointment.date') }}</label>
                                    <div class="mb-3">
                                        <input type="date"
                                            name="appointment_date"
                                            class="form-control"
                                            id="appointment_date"
                                            min="{{ date('Y-m-d') }}"
                                            required
                                            value="{{ old('appointment_date') }}">
                                        @error('appointment_date') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label for="time_slot">{{ __('messages.appointment.time_slot_label') }}</label>
                                    <div class="mb-3">
                                        <select name="time_slot_id" id="time_slot" class="form-control" required>
                                            <option value="" disabled selected>{{ __('messages.appointment.time_slot') }}</option>
                                        </select>
                                        @error('time_slot_id') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                            </div>

                            <div class="col-12">
                                <button type="submit" class="default-btn">
                                    {{ __('messages.appointment.submit') }}
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@if(isset($relatedServices) && $relatedServices->isNotEmpty())
<section class="service-section department-services doctor-related-services">
    <div class="container">
        <div class="title-head">
            <h2 class="title">{{ __('messages.service_detail.related_services') }}</h2>
        </div>
    </div>
    @include('directory.components-service-card', ['serviceItems' => $relatedServices])
</section>
@endif

@php
    // Physician: every field comes from the stored doctor row; props stay
    // off the schema when the source column is empty (no invented data).
    $physician = ['@type' => 'Physician', 'name' => $doctor->name, 'url' => route('doctor.show', $doctor->id)];
    if (trim((string) $doctor->department) !== '') {
        $physician['medicalSpecialty'] = $doctor->department;
    }
    if (trim((string) $doctor->image) !== '') {
        $physician['image'] = asset('storage/'.$doctor->image);
    }
    if (trim((string) $doctor->phone) !== '') {
        $physician['telephone'] = $doctor->phone;
    }
    if (($ratingSummary['count'] ?? 0) > 0) {
        $physician['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => $ratingSummary['avg'],
            'reviewCount' => (int) $ratingSummary['count'],
        ];
    }
    $jsonld = ['@context' => 'https://schema.org', '@graph' => [$physician]];
@endphp
@include('frontend.components.seo-jsonld', ['jsonld' => $jsonld])

<script>
    var getSlotsUrl = "{{ route('get.slots') }}";
    (function () {
        var consultationMode = document.getElementById('consultation_mode');
        var email = document.getElementById('patient_email');
        var updateEmailRequirement = function () {
            if (!email) return;
            email.required = !!consultationMode && consultationMode.value === 'telehealth';
            email.placeholder = email.required
                ? email.dataset.telehealthPlaceholder
                : email.dataset.cancelPlaceholder;
        };
        if (consultationMode) {
            consultationMode.addEventListener('change', updateEmailRequirement);
        }
        updateEmailRequirement();
    })();
</script>
@endsection