@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Frontend</p>
        <h1 class="mc-title">Edit <em>slide</em></h1>
        <p class="mc-sub">Update copy, swap the image, or change the CTA.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.sliders.update', $slider->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            @if($slider->bg_image)
                <div class="mc-avbig"><img src="{{ asset('storage/' . $slider->bg_image) }}" alt=""></div>
            @else
                <div class="mc-avbig">SL</div>
            @endif
            <div class="k">Currently editing</div>
            <h2>{{ Str::limit($slider->title, 40) }}</h2>
            <p>{{ $slider->button_text ? 'CTA: ' . $slider->button_text : 'No CTA set' }}</p>
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
                        <input type="text" name="title" class="form-control" value="{{ old('title', $slider->title) }}">
                        @error('title') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f full">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $slider->description) }}</textarea>
                    </div>
                    <div class="mc-f full">
                        <label>Button Text</label>
                        <input type="text" name="button_text" class="form-control" value="{{ old('button_text', $slider->button_text) }}">
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Image</h3><p>Background image for the slide.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Background Image</label>
                        @if($slider->bg_image)
                            <img src="{{ asset('storage/' . $slider->bg_image) }}" class="mc-imgprev mb-2" alt="">
                        @endif
                        <input type="file" name="bg_image" class="form-control">
                        <span class="mc-hint">JPG · PNG · WEBP · max 2 MB</span>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update slide</button>
                <a href="{{ route('admin.sliders.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
