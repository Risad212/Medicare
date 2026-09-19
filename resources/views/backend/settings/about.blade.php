@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Settings</p>
        <h1 class="mc-title">About <em>Settings</em></h1>
        <p class="mc-sub">About page content and SEO.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

@include('backend.components.seo-settings', [
    'title' => 'About',
    'page' => 'about',
])

<form action="{{ route('settings.about.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Intro Section --}}
    <section class="mc-sec mb-4">
        <div class="mc-sec-hd"><span class="no">01</span><h3>Intro Section</h3><p>Heading, copy, and images.</p></div>
        <div class="mc-sec-bd">
            <div class="mc-f">
                <label>Subtitle</label>
                <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $about->subtitle ?? '') }}" placeholder="about us">
            </div>
            <div class="mc-f">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $about->title ?? '') }}" placeholder="Compassionate Care Exceptional Expertise">
            </div>
            <div class="mc-f full">
                <label>Tagline</label>
                <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $about->tagline ?? '') }}">
            </div>
            <div class="mc-f full">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $about->description ?? '') }}</textarea>
            </div>
            <div class="mc-f">
                <label>Button Text</label>
                <input type="text" name="button_text" class="form-control" value="{{ old('button_text', $about->button_text ?? '') }}" placeholder="more info">
            </div>
            <div class="mc-f">
                <label>Button URL</label>
                <input type="text" name="button_url" class="form-control" value="{{ old('button_url', $about->button_url ?? '') }}" placeholder="#">
            </div>
            <div class="mc-f">
                <label>Image One</label>
                @if(!empty($about->image_one))
                    <img src="{{ asset('storage/' . $about->image_one) }}" class="mc-imgprev mb-2" alt="">
                @endif
                <input type="file" name="image_one" class="form-control">
            </div>
            <div class="mc-f">
                <label>Image Two</label>
                @if(!empty($about->image_two))
                    <img src="{{ asset('storage/' . $about->image_two) }}" class="mc-imgprev mb-2" alt="">
                @endif
                <input type="file" name="image_two" class="form-control">
            </div>
        </div>
    </section>

    {{-- Highlights Section --}}
    <section class="mc-sec mb-4">
        <div class="mc-sec-hd"><span class="no">02</span><h3>Highlights</h3><p>Mission, planning, and vision blocks.</p></div>
        <div class="mc-sec-bd">
            <div class="mc-f">
                <label>Mission — Title</label>
                <input type="text" name="mission_title" class="form-control" value="{{ old('mission_title', $about->mission_title ?? '') }}" placeholder="Our Mission">
            </div>
            <div class="mc-f full">
                <label>Mission — Description</label>
                <textarea name="mission_description" class="form-control" rows="3">{{ old('mission_description', $about->mission_description ?? '') }}</textarea>
            </div>
            <div class="mc-f">
                <label>Planning — Title</label>
                <input type="text" name="planning_title" class="form-control" value="{{ old('planning_title', $about->planning_title ?? '') }}" placeholder="Our Planning">
            </div>
            <div class="mc-f full">
                <label>Planning — Description</label>
                <textarea name="planning_description" class="form-control" rows="3">{{ old('planning_description', $about->planning_description ?? '') }}</textarea>
            </div>
            <div class="mc-f">
                <label>Vision — Title</label>
                <input type="text" name="vision_title" class="form-control" value="{{ old('vision_title', $about->vision_title ?? '') }}" placeholder="Our Vision">
            </div>
            <div class="mc-f full">
                <label>Vision — Description</label>
                <textarea name="vision_description" class="form-control" rows="3">{{ old('vision_description', $about->vision_description ?? '') }}</textarea>
            </div>
        </div>
    </section>

    <div class="mc-formacts">
        <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save about settings</button>
    </div>
</form>

@endsection
