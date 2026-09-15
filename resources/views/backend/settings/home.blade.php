@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Settings</p>
        <h1 class="mc-title">Home <em>Settings</em></h1>
        <p class="mc-sub">Homepage sections: about, counters, and SEO.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

@include('backend.components.seo-settings', [
    'title' => 'Home',
    'page' => 'home',
])

<form action="{{ route('settings.home.update') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- About Section --}}
    <section class="mc-sec mb-4">
        <div class="mc-sec-hd"><span class="no">01</span><h3>About Section</h3><p>Intro copy and supporting images.</p></div>
        <div class="mc-sec-bd">
            <div class="mc-f">
                <label>Title</label>
                <input type="text" name="about_title" class="form-control" value="{{ old('about_title', $homeSetting->about_title ?? '') }}">
            </div>
            <div class="mc-f">
                <label>Button Text</label>
                <input type="text" name="about_button_text" class="form-control" value="{{ old('about_button_text', $homeSetting->about_button_text ?? '') }}">
            </div>
            <div class="mc-f full">
                <label>Description</label>
                <textarea name="about_description" class="form-control" rows="4">{{ old('about_description', $homeSetting->about_description ?? '') }}</textarea>
            </div>
            <div class="mc-f">
                <label>Left Top (370×270 px)</label>
                @if(!empty($homeSetting->about_image_one))
                    <img src="{{ asset('storage/' . $homeSetting->about_image_one) }}" class="mc-imgprev mb-2" alt="">
                @endif
                <input type="file" name="about_image_one" class="form-control">
            </div>
            <div class="mc-f">
                <label>Left Bottom (370×270 px)</label>
                @if(!empty($homeSetting->about_image_two))
                    <img src="{{ asset('storage/' . $homeSetting->about_image_two) }}" class="mc-imgprev mb-2" alt="">
                @endif
                <input type="file" name="about_image_two" class="form-control">
            </div>
            <div class="mc-f">
                <label>Right (501×750 px)</label>
                @if(!empty($homeSetting->about_image_three))
                    <img src="{{ asset('storage/' . $homeSetting->about_image_three) }}" class="mc-imgprev mb-2" alt="">
                @endif
                <input type="file" name="about_image_three" class="form-control">
            </div>
        </div>
    </section>

    {{-- Counter Up Section --}}
    <section class="mc-sec mb-4">
        <div class="mc-sec-hd"><span class="no">02</span><h3>Counter Up Section</h3><p>Stats shown on the homepage.</p></div>
        <div class="mc-sec-bd">
            <div class="mc-f">
                <label>Counter 1 — Label</label>
                <input type="text" name="counter_one_text" class="form-control" value="{{ old('counter_one_text', $homeSetting->counter_one_text ?? '') }}">
            </div>
            <div class="mc-f">
                <label>Counter 1 — Number</label>
                <input type="number" name="counter_one_number" class="form-control" value="{{ old('counter_one_number', $homeSetting->counter_one_number ?? '') }}">
            </div>
            <div class="mc-f">
                <label>Counter 2 — Label</label>
                <input type="text" name="counter_two_text" class="form-control" value="{{ old('counter_two_text', $homeSetting->counter_two_text ?? '') }}">
            </div>
            <div class="mc-f">
                <label>Counter 2 — Number</label>
                <input type="number" name="counter_two_number" class="form-control" value="{{ old('counter_two_number', $homeSetting->counter_two_number ?? '') }}">
            </div>
            <div class="mc-f">
                <label>Counter 3 — Label</label>
                <input type="text" name="counter_three_text" class="form-control" value="{{ old('counter_three_text', $homeSetting->counter_three_text ?? '') }}">
            </div>
            <div class="mc-f">
                <label>Counter 3 — Number</label>
                <input type="number" name="counter_three_number" class="form-control" value="{{ old('counter_three_number', $homeSetting->counter_three_number ?? '') }}">
            </div>
            <div class="mc-f">
                <label>Counter 4 — Label</label>
                <input type="text" name="counter_four_text" class="form-control" value="{{ old('counter_four_text', $homeSetting->counter_four_text ?? '') }}">
            </div>
            <div class="mc-f">
                <label>Counter 4 — Number</label>
                <input type="number" name="counter_four_number" class="form-control" value="{{ old('counter_four_number', $homeSetting->counter_four_number ?? '') }}">
            </div>
        </div>
    </section>

    <div class="mc-formacts">
        <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save home settings</button>
    </div>
</form>

@endsection
