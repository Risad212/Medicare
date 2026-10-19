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
                    @endif
                </div>
            </div>

            <div class="col-lg-8">
                <div class="doctor-info">

                    <h4 class="doctor-name" data-notranslate>{{ $doctor->name }}</h4>

                    <h6 class="small">
                        <strong>Degree:</strong> <span data-notranslate>{{ $doctor->degree }}</span>
                    </h6>

                    <h5 class="small">
                        <strong>Department:</strong> <span data-notranslate>{{ $doctor->department }}</span>
                    </h5>

                    <h5 class="small mb-3">
                        <strong>Specialist:</strong> <span data-notranslate>{{ $doctor->specialist }}</span>
                    </h5>

                    <div class="mb-2">
                        <strong>Services:</strong>
                        <span data-notranslate>{{ $doctor->services }}</span>
                    </div>

                    <h5 class="doctor-availability">
                        <strong>Availability:</strong> <span data-notranslate>{{ $doctor->availability }}</span>
                    </h5>

                    <h5 class="doctor-availability">
                        <strong>Phone:</strong> <span data-notranslate>{{ $doctor->phone }}</span>
                    </h5>

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
                                    <div class="small text-muted mb-2">{{ $review->created_at->format('d M Y') }}</div>
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

                        <h3>Make an Appointment</h3>
                          @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                          @endif

                      <form action="{{ route('appointment.store') }}" method="POST">
                         @csrf

                          <input type="hidden" name="doctor_id" value="{{ $doctor->id }}"> 

                            <div class="row">

                                <div class="col-lg-6">
                                    <label>Doctor Name</label>
                                    <div class="mb-3">
                                        <input type="text"
                                               class="form-control"
                                               readonly
                                               value="{{ $doctor->name }}">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Visit Type</label>
                                    <div class="mb-3">
                                        <select id="visit_type" class="form-control" name="visit_type">
                                            <option value="1" {{ old('visit_type') == '1' ? 'selected' : '' }}>First Visit</option>
                                            <option value="2" {{ old('visit_type') == '2' ? 'selected' : '' }}>Second Visit</option>
                                            <option value="3" {{ old('visit_type') == '3' ? 'selected' : '' }}>Report Review</option>
                                        </select>
                                        @error('visit_type') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                @if(\App\Support\Module::enabled('zoom'))
                                <div class="col-lg-6">
                                    <label>{{ __('messages.appointment.mode') }}</label>
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
                                    <label>Patient Name</label>
                                    <div class="mb-3">
                                        <input type="text"
                                               class="form-control"
                                               name="patient_name"
                                               value="{{ old('patient_name') }}"
                                               placeholder="Enter patient name">
                                        @error('patient_name') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Age</label>
                                    <div class="mb-3">
                                        <input type="number"
                                               class="form-control"
                                               name="age"
                                               value="{{ old('age') }}"
                                               placeholder="Your age">
                                        @error('age') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Gender</label>
                                    <div class="mb-3">
                                        <select class="form-control" name="gender">
                                            <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select Gender</option>
                                            <option value="1" {{ old('gender') == '1' ? 'selected' : '' }}>Male</option>
                                            <option value="2" {{ old('gender') == '2' ? 'selected' : '' }}>Female</option>
                                            <option value="3" {{ old('gender') == '3' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Phone</label>
                                    <div class="mb-3">
                                        <input type="tel"
                                               inputmode="tel"
                                               pattern="[0-9+\-\s]+"
                                               class="form-control"
                                               name="phone"
                                               value="{{ old('phone') }}"
                                               placeholder="Your phone number">
                                        @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Email</label>
                                    <div class="mb-3">
                                        <input type="email"
                                               class="form-control"
                                               name="email"
                                               id="patient_email"
                                               data-cancel-placeholder="{{ __('messages.appointment.email_cancel') }}"
                                               data-telehealth-placeholder="{{ __('messages.appointment.email_telehealth') }}"
                                               @if(old('consultation_mode') === 'telehealth') required @endif
                                               value="{{ old('email') }}"
                                               placeholder="Your email (for cancellation link)">
                                         @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Appointment Date</label>
                                    <div class="mb-3">
                                        <input type="date"
                                            name="appointment_date"
                                            class="form-control"
                                            id="appointment_date"
                                            min="{{ date('Y-m-d') }}"
                                            value="{{ old('appointment_date') }}">
                                        @error('appointment_date') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label>Time Slot</label>
                                    <div class="mb-3">
                                        <select name="time_slot_id" id="time_slot" class="form-control">
                                            <option value="">Select Time Slot</option>
                                        </select>
                                        @error('time_slot_id') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>
                                </div>

                            </div>

                            <div class="col-12">
                                <button type="submit" class="default-btn">
                                    Send Request
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
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