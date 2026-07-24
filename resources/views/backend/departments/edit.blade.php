@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Edit Department</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="tile-body">
                <form action="{{ route('admin.departments.update', $department->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $department->name) }}">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $department->description) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Status</label><br>
                        <input type="checkbox" name="status" value="1" {{ $department->status ? 'checked' : '' }}> Active
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Department
                    </button>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">Cancel</a>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection