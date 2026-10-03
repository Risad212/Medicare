@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Publishing</p>
        <h1 class="mc-title">New <em>post</em></h1>
        <p class="mc-sub">Patient education. Drafts stay private until published.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">BL</div>
            <div class="k">New record</div>
            <h2>Untitled post</h2>
            <p>Draft · not published</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Content</li>
                <li><span class="n">2</span>Filing</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Content</h3><p>The article itself.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Title <i class="req">*</i></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. 5 Early Signs of Heart Disease" value="{{ old('title') }}">
                        @error('title') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f full">
                        <label>Excerpt (Short Description)</label>
                        <textarea name="excerpt" class="form-control" rows="2" placeholder="One sentence for listings…">{{ old('excerpt') }}</textarea>
                    </div>
                    <div class="mc-f full">
                        <label>Content</label>
                        <textarea name="content" id="content" class="form-control" rows="6" placeholder="Write for patients, not doctors…">{{ old('content') }}</textarea>
                    </div>
                    <div class="mc-f full">
                        <label>Image</label>
                        <input type="file" name="image" class="form-control">
                        <span class="mc-hint">JPG · PNG · WEBP · max 2 MB</span>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Filing</h3><p>How it is found — and whether it is live.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Category</label>
                        <select name="category" class="form-select">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}" {{ old('category', $blog->category ?? '') == $category->name ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mc-f">
                        <label>Tag</label>
                        <select name="tags" class="form-select">
                            <option value="">Select Tag</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->name }}" {{ old('tags', $blog->tags ?? '') == $tag->name ? 'selected' : '' }}>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mc-f">
                        <label>Status</label>
                        <label class="mc-check"><input type="checkbox" name="status" value="1" checked> Published</label>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save blog post</button>
                <a href="{{ route('admin.blogs.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
