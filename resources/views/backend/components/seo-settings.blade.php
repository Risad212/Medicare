<section class="mc-sec mb-4">
    <div class="mc-sec-hd"><span class="no">SEO</span><h3>{{ $title }} SEO Settings</h3><p>Search engine metadata.</p></div>

    <form action="{{ route('admin.seo-settings.update') }}" method="POST">
        @csrf

        <input type="hidden" name="page" value="{{ $page }}">

        <div class="mc-sec-bd">

            @if(session('seo_success'))
                <div class="col-span-2 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('seo_success') }}</div>
            @endif

            @if(session('seo_error'))
                <div class="col-span-2 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('seo_error') }}</div>
            @endif

            <div class="mc-f full">
                <label>Meta Title</label>
                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $seo->meta_title ?? '') }}">
            </div>

            <div class="mc-f full">
                <label>Meta Description</label>
                <textarea name="meta_description" rows="4" class="form-control">{{ old('meta_description', $seo->meta_description ?? '') }}</textarea>
            </div>

            <div class="mc-f full">
                <label>Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control" value="{{ old('meta_keywords', $seo->meta_keywords ?? '') }}" placeholder="hospital, doctor, healthcare">
                <span class="mc-hint">Separate keywords with commas (,).</span>
            </div>

        </div>

        <div class="border-t border-line-2 px-4.5 py-3">
            <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save {{ $title }} SEO settings</button>
        </div>
    </form>
</section>
