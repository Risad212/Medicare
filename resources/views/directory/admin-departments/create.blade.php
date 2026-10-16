@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Structure</p>
        <h1 class="mc-title">New depart<em>ment</em></h1>
        <p class="mc-sub">A clinical unit: name it, describe it, switch it on.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.departments.store') }}" method="POST">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">DP</div>
            <div class="k">New record</div>
            <h2>Unsaved unit</h2>
            <p>Fill the profile — it appears on the site once active.</p>
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
                        <input type="text" name="name" class="form-control" placeholder="e.g. Cardiology" value="{{ old('name') }}">
                        @error('name') <small class="text-red-t text-xs">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f full">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="What this unit covers…">{{ old('description') }}</textarea>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Visibility</h3><p>Whether it appears on the site.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Status</label>
                        <label class="mc-check"><input type="checkbox" name="status" value="1" checked> Active</label>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save department</button>
                <a href="{{ route('admin.departments.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
