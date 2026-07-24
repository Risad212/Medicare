@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Add New Department</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="tile-body">
                <form action="{{ route('admin.departments.store') }}" method="POST">
                    @csrf

                    <div class="form-group mb-2">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-2">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group mb-2">
                        <label>Status</label><br>
                        <input type="checkbox" name="status" value="1" checked> Active
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Department
                    </button>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">Cancel</a>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection