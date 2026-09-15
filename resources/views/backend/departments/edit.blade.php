@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Structure</p>
        <h1 class="mc-title">Edit depart<em>ment</em></h1>
        <p class="mc-sub">Rename it, reword it, or take it offline.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.departments.update', $department->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">{{ strtoupper(implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $department->name)), 0, 2))) }}</div>
            <div class="k">Currently editing</div>
            <h2>{{ $department->name }}</h2>
            <p>{{ $department->status ? 'Active on the site' : 'Hidden from the site' }}</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Profile</li>
                <li><span class="n">2</span>Visibility</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Profile</h3><p>Name and description.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Name <i class="req">*</i></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $department->name) }}">
                        @error('name') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f full">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $department->description) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Visibility</h3><p>Whether it appears on the site.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Status</label>
                        <label class="mc-check"><input type="checkbox" name="status" value="1" {{ $department->status ? 'checked' : '' }}> Active</label>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update department</button>
                <a href="{{ route('admin.departments.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
