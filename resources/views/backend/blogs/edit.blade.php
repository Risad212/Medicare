@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Edit Blog Post</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="tile-body">
                <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-2">
                        <label class="mb-2">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $blog->title) }}">
                        @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Category</label>
                        <select name="category" class="form-control">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->name }}"
                                    {{ old('category', $blog->category) == $category->name ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Tags <small class="text-muted">(comma separated)</small></label>
                        <input type="text" name="tags" class="form-control"
                            value="{{ old('tags', $blog->tags) }}"
                            placeholder="e.g. heart, surgery, tips">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Excerpt</label>
                        <textarea name="excerpt" class="form-control" rows="3">{{ old('excerpt', $blog->excerpt) }}</textarea>
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Content</label>
                        <textarea name="content" id="content" class="form-control">{{ old('content', $blog->content) }}</textarea>
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Image</label><br>
                        @if($blog->image)
                            <img src="{{ asset('storage/' . $blog->image) }}" height="80" class="mb-2 d-block">
                        @endif
                        <input type="file" name="image" class="form-control">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Status</label><br>
                        <input type="checkbox" name="status" value="1" {{ $blog->status ? 'checked' : '' }}> Published
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Blog Post
                    </button>
                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">Cancel</a>

                </form>
            </div>
        </div>

    </div>
</div>

@endsection