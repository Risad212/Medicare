@extends('frontend.layouts.front-app')

@section('meta_title', $seo->meta_title ?? __('messages.public.gallery_title'))
@section('meta_description', $seo->meta_description ?? '')
@section('meta_keywords', $seo->meta_keywords ?? '')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => __('messages.public.gallery_title')
])

<section class="py-5">
    <div class="container">
        <h2 class="mb-2" style="font-weight:800;letter-spacing:-.02em">{{ __('messages.public.gallery_title') }}</h2>
        <p class="text-muted mb-4">{{ __('messages.public.gallery_intro') }}</p>

        @if($images->count())
            <div class="row g-3">
                @foreach($images as $image)
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="{{ asset('storage/' . $image->image) }}" class="d-block overflow-hidden rounded-3" style="aspect-ratio:4/3">
                        <img src="{{ asset('storage/' . $image->image) }}" alt="{{ $image->caption ?? __('messages.public.gallery_title') }}" class="w-100 h-100" style="object-fit:cover" loading="lazy">
                    </a>
                    @if($image->caption)
                        <div class="small text-muted mt-1">{{ $image->caption }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-light border">{{ __('messages.public.gallery_empty') }}</div>
        @endif
    </div>
</section>

@endsection
