@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Publishing</p>
        <h1 class="mc-title">Upload <em>photo</em></h1>
        <p class="mc-sub">A new image for the public gallery.</p>
    </div>
</div>

<form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">GL</div>
            <div class="k">New record</div>
            <h2>Untitled photo</h2>
            <p>JPG, PNG or WEBP, up to 4MB.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Image</li>
                <li><span class="n">2</span>Details</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Image</h3><p>The photo file itself.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Photo <i class="req">*</i></label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        @error('image') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Details</h3><p>Optional caption and ordering.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Caption</label>
                        <input type="text" name="caption" class="form-control" maxlength="255" placeholder="e.g. Main entrance" value="{{ old('caption') }}">
                        @error('caption') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Display order</label>
                        <input type="number" name="sort_order" class="form-control" min="0" max="9999" placeholder="0" value="{{ old('sort_order', 0) }}">
                        @error('sort_order') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Status <i class="req">*</i></label>
                        <select name="status" class="form-control">
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Published</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Draft</option>
                        </select>
                        @error('status') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save photo</button>
                <a href="{{ route('admin.gallery.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
