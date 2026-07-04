@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">

        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Add New Blog Post</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="tile-body">
                <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group mb-2">
                        <label class="mb-2">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                        @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Excerpt (Short Description)</label>
                        <textarea name="excerpt" class="form-control" rows="3">{{ old('excerpt') }}</textarea>
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Content</label>
                        <textarea name="content" id="content" class="form-control">{{ old('content') }}</textarea>
                    </div>

                    <div class="form-group mb-2">
                    <label>Category</label>
                    <select name="category" class="form-control">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->name }}"
                                {{ old('category', $blog->category ?? '') == $category->name ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                 

                    <div class="form-group mb-2">
                        <label class="mb-2">Image</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Status</label><br>
                        <input type="checkbox" name="status" value="1" checked> Published
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Blog Post
                    </button>
                    <a href="{{ route('admin.blogs.index') }}" class="btn btn-secondary">Cancel</a>

                </form>
            </div>
        </div>

    </div>
</div>

@endsection