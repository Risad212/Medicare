@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Publishing</p>
        <h1 class="mc-title">Edit <em>photo</em></h1>
        <p class="mc-sub">Update caption, order or replace the image.</p>
    </div>
</div>

<form action="{{ route('admin.gallery.update', $gallery->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">GL</div>
            <div class="k">#{{ str_pad($gallery->id, 3, '0', STR_PAD_LEFT) }}</div>
            <h2>{{ $gallery->caption ?? 'Photo' }}</h2>
            <p>Leave the file input empty to keep the current image.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Image</li>
                <li><span class="n">2</span>Details</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Image</h3><p>Current photo and optional replacement.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Current photo</label>
                        <img src="{{ asset('storage/' . $gallery->image) }}" alt="" style="width:220px;height:150px;object-fit:cover;border-radius:10px;border:1px solid #e2e8f0">
                    </div>
                    <div class="mc-f full">
                        <label>Replace photo</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        @error('image') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Details</h3><p>Caption and ordering.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Caption</label>
                        <input type="text" name="caption" class="form-control" maxlength="255" value="{{ old('caption', $gallery->caption) }}">
                        @error('caption') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Display order</label>
                        <input type="number" name="sort_order" class="form-control" min="0" max="9999" value="{{ old('sort_order', $gallery->sort_order) }}">
                        @error('sort_order') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Status <i class="req">*</i></label>
                        <select name="status" class="form-control">
                            <option value="1" {{ old('status', $gallery->status) == '1' ? 'selected' : '' }}>Published</option>
                            <option value="0" {{ old('status', $gallery->status) == '0' ? 'selected' : '' }}>Draft</option>
                        </select>
                        @error('status') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update photo</button>
                <a href="{{ route('admin.gallery.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
