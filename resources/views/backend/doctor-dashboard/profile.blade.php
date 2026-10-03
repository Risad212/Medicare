@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Doctor</p>
        <h1 class="mc-title">My <em>profile</em></h1>
        <p class="mc-sub">Your public-facing doctor details.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('doctor.profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            @php
                $initials = implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', auth()->user()->name)), 0, 2));
            @endphp
            <div class="mc-avbig">{{ strtoupper($initials) }}</div>
            <div class="k">Profile</div>
            <h2>{{ auth()->user()->name }}</h2>
            <p>{{ $doctor->degree ?? '' }} {{ $doctor->specialist ?? '' }}</p>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Details</h3><p>Contact info and specialty.</p></div>
                <div class="mc-sec-bd grid-cols-1">
                    <div class="mc-f">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}">
                        @error('name') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>

                    <div class="mc-f">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $doctor->phone ?? '') }}">
                    </div>

                    <div class="mc-f">
                        <label>Degree</label>
                        <input type="text" name="degree" class="form-control" value="{{ old('degree', $doctor->degree ?? '') }}">
                    </div>

                    <div class="mc-f">
                        <label>Specialist</label>
                        <input type="text" name="specialist" class="form-control" value="{{ old('specialist', $doctor->specialist ?? '') }}">
                    </div>

                    <div class="mc-f">
                        <label>Profile Image</label>
                        <input type="file" name="image" class="form-control">
                        @if($doctor->image)
                            <img src="{{ asset('storage/'.$doctor->image) }}" width="100" class="mc-imgprev">
                        @endif
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update Profile</button>
            </div>
        </div>
    </div>
</form>

@endsection