@extends('frontend.layouts.front-app')

@section('meta_title', $name.' — MediCare Hospital')
@section('meta_description', $metaDescription)
@section('meta_keywords', '')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => $name,
])

<!--========== Department Doctors ==========-->
<section class="service-section hospital-doctors department-page">
    <div class="container">
        <div class="title-head">
            <h2 class="title">{{ __('messages.service_detail.doctors_heading') }}</h2>
        </div>

        @if($doctors->isNotEmpty())
        <div class="row gy-4">
            @foreach($doctors as $doctor)
                <div class="col-lg-4 col-md-6">
                    <div class="doctor-card mc-dept-doctor">
                        <div class="card-img">
                            <img class="img-fluid" src="{{ asset('storage/'.($doctor->image ?? '')) }}" alt="{{ $doctor->name ?? 'Doctor' }}">
                        </div>
                        <div class="card-content">
                            <h3 class="title" data-notranslate>{{ $doctor->name }}</h3>
                            @if(trim((string) $doctor->specialist) !== '')
                                <span class="speacility" data-notranslate>{{ $doctor->specialist }}</span>
                            @endif
                            <div class="mc-dept-links">
                                <a href="{{ route('doctor.show', $doctor->id) }}">{{ __('messages.service_detail.view_profile') }}</a>
                                <a href="{{ route('appointment', ['doctor_id' => $doctor->id]) }}">{{ __('messages.public.book_appointment') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <p>{{ __('messages.public.no_doctors') }}</p>
        </div>
        @endif
    </div>
</section>

@if($services->isNotEmpty())
<!--========== Department Services ==========-->
<section class="service-section department-services">
    <div class="container">
        <div class="title-head">
            <h2 class="title">{{ __('messages.service_detail.related_services') }}</h2>
        </div>
    </div>
    @include('directory.components-service-card', ['serviceItems' => $services])
</section>
@endif

@endsection
