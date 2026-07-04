@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Add New Tag</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="tile-body">
                <form action="{{ route('admin.tags.store') }}" method="POST">
                    @csrf

                    <div class="form-group mb-2">
                        <label clas="mb-2">Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Tag
                    </button>
                    <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection