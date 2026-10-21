@extends('frontend.layouts.front-app')

@section('meta_title', $seo->meta_title ?? 'Medicare')
@section('meta_description', $seo->meta_description ?? '')
@section('meta_keywords', $seo->meta_keywords ?? '')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => $pageTitle ?? $service->title,
])

<!--========== Service Detail ==========-->
<section class="service-section service-details">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="detail-wrap text-center">
                    @if(!empty($service->icon))
                        <img class="detail-icon" src="{{ asset('storage/'.$service->icon) }}" alt="{{ $service->title }}">
                    @endif

                    <h2 class="title">{{ $service->title }}</h2>

                    <div class="detail-body text-start">
                        @foreach(preg_split('/\r\n|\r|\n/', $service->description ?? '') as $para)
                            @if(trim($para) !== '')
                                <p>{{ trim($para) }}</p>
                            @endif
                        @endforeach
                    </div>

                    <a class="detail-btn" href="{{ route('appointment') }}">{{ __('messages.public.book_appointment') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>

@if($departmentDoctors->isNotEmpty())
<!--========== Doctors in this Department ==========-->
<section class="service-section hospital-doctors">
    <div class="container">
        <div class="title-head">
            <h2 class="title">{{ __('messages.service_detail.doctors_heading') }}</h2>
        </div>

        <div class="row gy-4">
            @foreach($departmentDoctors as $doctor)
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
    </div>
</section>
@endif

@if(isset($others) && $others->isNotEmpty())
<!--========== Other Services ==========-->
<section class="service-section other-services">
    <div class="container">
        <div class="title-head">
            <span class="subtitle">More Care</span>
            <h3 class="title">Other Services</h3>
        </div>
        @include('directory.components-service-card', ['serviceItems' => $others])
    </div>
</section>
@endif

@php
    // MedicalService: only real stored fields; provider from real settings.
    $mcSettings = App\Models\GeneralSetting::first();
    $medicalService = [];
    if (trim((string) $service->title) !== '') {
        $medicalService = ['@type' => 'MedicalService', 'name' => $service->title, 'url' => route('service.show', $service->slug)];
        if (trim((string) $service->description) !== '') {
            $medicalService['description'] = Str::limit(strip_tags((string) $service->description), 300);
        }
        if ($mcSettings && trim((string) $mcSettings->site_name) !== '') {
            $medicalService['provider'] = ['@type' => 'Organization', 'name' => $mcSettings->site_name];
        }
    }
    $jsonld = $medicalService ? ['@context' => 'https://schema.org', '@graph' => [$medicalService]] : [];
@endphp
@include('frontend.components.seo-jsonld', ['jsonld' => $jsonld])

@endsection
