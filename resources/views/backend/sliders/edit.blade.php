@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Edit Slider</h3>
            </div>

            <div class="tile-body">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form action="{{ route('admin.sliders.update', $slider->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-2">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $slider->title) }}">
                        @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-2">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $slider->description) }}</textarea>
                    </div>

                    <div class="form-group mb-2">
                        <label>Button Text</label>
                        <input type="text" name="button_text" class="form-control" value="{{ old('button_text', $slider->button_text) }}">
                    </div>

                    <div class="form-group mb-2">
                        <label>Background Image</label><br>
                        @if($slider->bg_image)
                            <img src="{{ asset('storage/' . $slider->bg_image) }}" width="200" class="mb-2 d-block">
                        @endif
                        <input type="file" name="bg_image" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Slider
                    </button>
                    <a href="{{ route('admin.sliders.index') }}" class="btn btn-secondary">Cancel</a>

                </form>

            </div>
        </div>
    </div>
</div>

@endsection