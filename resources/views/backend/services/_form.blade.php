@php
    $editing = isset($service);
    $action = $editing ? route('admin.services.update', $service->id) : route('admin.services.store');
    $currentIcon = $editing ? $service->icon : null;
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div class="mc-grid">
        <aside class="mc-side">
            @if($currentIcon)
                <img src="{{ asset('storage/'.$currentIcon) }}" alt="" class="mc-avbig" style="object-fit:contain;background:#fff;padding:8px">
            @else
                <div class="mc-avbig">SV</div>
            @endif
            <div class="k">{{ $editing ? 'Editing' : 'New record' }}</div>
            <h2>{{ $editing ? $service->title : 'Unsaved service' }}</h2>
            <p>Shown as a card on the home and services pages.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Profile</li>
                <li><span class="n">2</span>Media</li>
                <li><span class="n">3</span>Link</li>
                <li><span class="n">4</span>Visibility</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Profile</h3><p>Title and description.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Title <i class="req">*</i></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Heart transplants" value="{{ old('title', $editing ? $service->title : '') }}">
                        @error('title') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f full">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="What this service covers…">{{ old('description', $editing ? $service->description : '') }}</textarea>
                        @error('description') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Media</h3><p>Optional icon image.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Icon image</label>
                        <input type="file" name="icon" class="form-control" accept="image/*">
                        <small class="text-gray-400">PNG, JPG, WEBP or SVG, up to 2 MB.</small>
                        @error('icon') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">03</span><h3>Link</h3><p>Call-to-action button on the card.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Button text</label>
                        <input type="text" name="button_text" class="form-control" placeholder="Read more" value="{{ old('button_text', $editing ? $service->button_text : 'Read more') }}">
                        @error('button_text') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Button URL</label>
                        <input type="text" name="button_url" class="form-control" placeholder="https://… or /contact" value="{{ old('button_url', $editing ? $service->button_url : '') }}">
                        @error('button_url') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">04</span><h3>Visibility</h3><p>Ordering and whether it appears on the site.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Order</label>
                        <input type="number" name="order" class="form-control" min="0" value="{{ old('order', $editing ? $service->order : 0) }}">
                        @error('order') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Status</label>
                        <label class="mc-check"><input type="checkbox" name="status" value="1" @checked(old('status', $editing ? $service->status : 1))> Active</label>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> {{ $editing ? 'Update service' : 'Save service' }}</button>
                <a href="{{ route('admin.services.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>
