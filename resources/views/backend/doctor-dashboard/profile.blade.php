@extends('backend.layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>My Profile</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('doctor.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}">
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $doctor->phone ?? '') }}">
                </div>

                <div class="mb-3">
                    <label>Degree</label>
                    <input type="text" name="degree" class="form-control" value="{{ old('degree', $doctor->degree ?? '') }}">
                </div>

                <div class="mb-3">
                    <label>Specialist</label>
                    <input type="text" name="specialist" class="form-control" value="{{ old('specialist', $doctor->specialist ?? '') }}">
                </div>

                <div class="mb-3">
                    <label>Profile Image</label>
                    <input type="file" name="image" class="form-control">
                    @if($doctor->image)
                        <img src="{{ asset('storage/'.$doctor->image) }}" width="100" class="mt-2">
                    @endif
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</div>

@endsection