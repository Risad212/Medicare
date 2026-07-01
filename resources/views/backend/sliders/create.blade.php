@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Add New Slider</h3>
            </div>

            <div class="tile-body">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group mb-2">
                        <label class="mb-2">Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                        @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Button Text</label>
                        <input type="text" name="button_text" class="form-control" value="{{ old('button_text') }}">
                    </div>

                    <div class="form-group mb-2">
                        <label class="mb-2">Background Image</label>
                        <input type="file" name="bg_image" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Slider
                    </button>
                    <a href="{{ route('admin.sliders.index') }}" class="btn btn-secondary">Cancel</a>

                </form>

            </div>
        </div>
    </div>
</div>

@endsection