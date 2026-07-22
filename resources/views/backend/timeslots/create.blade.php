@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Add New Time Slot</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="tile-body">
                <form action="{{ route('admin.time-slots.store') }}" method="POST">
                    @csrf

                    <div class="form-group mb-2">
                        <label>Time</label>
                        <input type="text" name="time" class="form-control"
                            placeholder="e.g. 09:00 AM" value="{{ old('time') }}">
                        @error('time') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group mb-2">
                        <label>Status</label><br>
                        <input type="checkbox" name="status" value="1" checked> Active
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Time Slot
                    </button>
                    <a href="{{ route('admin.time-slots.index') }}" class="btn btn-secondary">Cancel</a>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection