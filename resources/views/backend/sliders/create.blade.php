@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Frontend</p>
        <h1 class="mc-title">New <em>slide</em></h1>
        <p class="mc-sub">A homepage hero banner with image and CTA.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">SL</div>
            <div class="k">New record</div>
            <h2>Unsaved slide</h2>
            <p>Upload an image, add copy, set the button text.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Content</li>
                <li><span class="n">2</span>Image</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Content</h3><p>Text and button label.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Title <i class="req">*</i></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Trusted Healthcare" value="{{ old('title') }}">
                        @error('title') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f full">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="What the slide is about…">{{ old('description') }}</textarea>
                    </div>
                    <div class="mc-f full">
                        <label>Button Text</label>
                        <input type="text" name="button_text" class="form-control" placeholder="e.g. Learn More" value="{{ old('button_text') }}">
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Image</h3><p>Background image for the slide.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Background Image</label>
                        <input type="file" name="bg_image" class="form-control">
                        <span class="mc-hint">JPG · PNG · WEBP · max 2 MB</span>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save slide</button>
                <a href="{{ route('admin.sliders.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
