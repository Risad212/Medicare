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

                    <a class="detail-btn" href="{{ route('appointment') }}">Book Appointment</a>
                </div>
            </div>
        </div>
    </div>
</section>

@if(isset($others) && $others->isNotEmpty())
<!--========== Other Services ==========-->
<section class="service-section other-services">
    <div class="container">
        <div class="title-head">
            <span class="subtitle">More Care</span>
            <h3 class="title">Other Services</h3>
        </div>
        @include('frontend.components.service', ['serviceItems' => $others])
    </div>
</section>
@endif

@endsection
