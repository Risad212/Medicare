@extends('backend.layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="tile">
            <div class="tile-title-w-btn">
                <h3 class="title">Edit Time Slot</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="tile-body">
                <form action="{{ route('admin.time-slots.update', $timeSlot->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Time</label>
                        <input type="text" name="time" class="form-control"
                            value="{{ old('time', $timeSlot->time) }}">
                        @error('time') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label>Order</label>
                        <input type="number" name="order" class="form-control"
                            value="{{ old('order', $timeSlot->order) }}">
                    </div>

                    <div class="form-group">
                        <label>Status</label><br>
                        <input type="checkbox" name="status" value="1"
                            {{ $timeSlot->status ? 'checked' : '' }}> Active
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Time Slot
                    </button>
                    <a href="{{ route('admin.time-slots.index') }}" class="btn btn-secondary">Cancel</a>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection