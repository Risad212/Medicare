@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Settings</p>
        <h1 class="mc-title">Service <em>Settings</em></h1>
        <p class="mc-sub">Service page content and SEO.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.services.index') }}" class="mc-btn ghost"><i class="bi bi-grid"></i> Manage service cards</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

@include('backend.components.seo-settings', [
    'title' => 'Service',
    'page' => 'service',
])

<form action="{{ route('settings.service.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Emergency Section --}}
    <section class="mc-sec mb-4">
        <div class="mc-sec-hd"><span class="no">01</span><h3>Emergency Section</h3><p>Heading, copy, contact details, and image.</p></div>
        <div class="mc-sec-bd">
            <div class="mc-f">
                <label>Subtitle</label>
                <input type="text" name="emergency_subtitle" class="form-control" value="{{ old('emergency_subtitle', $serviceSetting->emergency_subtitle ?? '') }}" placeholder="Emergency Treatment">
            </div>
            <div class="mc-f">
                <label>Title</label>
                <input type="text" name="emergency_title" class="form-control" value="{{ old('emergency_title', $serviceSetting->emergency_title ?? '') }}" placeholder="Emergency? For any Help Contact Us Now">
            </div>
            <div class="mc-f full">
                <label>Description</label>
                <textarea name="emergency_description" class="form-control" rows="4">{{ old('emergency_description', $serviceSetting->emergency_description ?? '') }}</textarea>
            </div>
            <div class="mc-f">
                <label>Phone</label>
                <input type="text" name="emergency_phone" class="form-control" value="{{ old('emergency_phone', $serviceSetting->emergency_phone ?? '') }}" placeholder="+821-456-789">
            </div>
            <div class="mc-f">
                <label>Email</label>
                <input type="email" name="emergency_email" class="form-control" value="{{ old('emergency_email', $serviceSetting->emergency_email ?? '') }}" placeholder="hello@info.com">
            </div>
            <div class="mc-f full">
                <label>Image</label>
                @if(!empty($serviceSetting->emergency_image))
                    <img src="{{ asset('storage/' . $serviceSetting->emergency_image) }}" class="mc-imgprev mb-2" alt="">
                @endif
                <input type="file" name="emergency_image" class="form-control">
                @error('emergency_image') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
            </div>
        </div>
    </section>

    {{-- Prevention Section --}}
    <section class="mc-sec mb-4">
        <div class="mc-sec-hd"><span class="no">02</span><h3>Prevention Section</h3><p>Heading and the eight prevention items.</p></div>
        <div class="mc-sec-bd">
            <div class="mc-f">
                <label>Subtitle</label>
                <input type="text" name="prevention_subtitle" class="form-control" value="{{ old('prevention_subtitle', $serviceSetting->prevention_subtitle ?? '') }}" placeholder="Prevention">
            </div>
            <div class="mc-f">
                <label>Title</label>
                <input type="text" name="prevention_title" class="form-control" value="{{ old('prevention_title', $serviceSetting->prevention_title ?? '') }}" placeholder="How To Protect Yourself">
            </div>

            @for($i = 1; $i <= 8; $i++)
                <div class="mc-f">
                    <label>Item {{ $i }} — Title</label>
                    <input type="text" name="prevention_{{ $i }}_title" class="form-control" value="{{ old('prevention_'.$i.'_title', $serviceSetting->{'prevention_'.$i.'_title'} ?? '') }}">
                </div>
                <div class="mc-f">
                    <label>Item {{ $i }} — Description</label>
                    <textarea name="prevention_{{ $i }}_desc" class="form-control" rows="2">{{ old('prevention_'.$i.'_desc', $serviceSetting->{'prevention_'.$i.'_desc'} ?? '') }}</textarea>
                </div>
            @endfor
        </div>
    </section>

    <div class="mc-formacts">
        <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save service settings</button>
    </div>
</form>

@endsection
