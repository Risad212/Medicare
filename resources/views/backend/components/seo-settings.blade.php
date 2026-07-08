<div class="tile mb-4">
    <div class="tile-title-w-btn">
        <h3 class="title">{{ $title }} SEO Settings</h3>
    </div>

    <form action="{{ route('admin.seo-settings.update') }}" method="POST">
        @csrf

        <input type="hidden" name="page" value="{{ $page }}">

        <div class="tile-body">

            @if(session('seo_success'))
                <div class="alert alert-success">
                    {{ session('seo_success') }}
                </div>
            @endif

            @if(session('seo_error'))
                <div class="alert alert-danger">
                    {{ session('seo_error') }}
                </div>
            @endif

            <div class="form-group mb-3">
                <label class="mb-2">Meta Title</label>
                <input type="text" name="meta_title" class="form-control"
                       value="{{ old('meta_title', $seo->meta_title ?? '') }}">
            </div>

            <div class="form-group mb-3">
                <label class="mb-2">Meta Description</label>
                <textarea name="meta_description" rows="4" class="form-control">{{ old('meta_description', $seo->meta_description ?? '') }}</textarea>
            </div>

            <div class="form-group mb-3">
                <label class="mb-2">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control"
                       value="{{ old('meta_keywords', $seo->meta_keywords ?? '') }}"
                       placeholder="hospital, doctor, healthcare">
                <small class="text-muted">Separate keywords with commas (,).</small>
            </div>

        </div>

        <div class="tile-footer">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Save {{ $title }} SEO Settings
            </button>
        </div>
    </form>
</div>