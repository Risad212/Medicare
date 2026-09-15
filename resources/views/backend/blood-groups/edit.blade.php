@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Edit blood <em>group</em></h1>
        <p class="mc-sub">Correct the label or switch the group off.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.blood-groups.update', $bloodGroup->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">{{ $bloodGroup->name }}</div>
            <div class="k">Currently editing</div>
            <h2>Blood type {{ $bloodGroup->name }}</h2>
            <p>{{ $bloodGroup->status ? 'Active' : 'Disabled' }}</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Identity</li>
                <li><span class="n">2</span>Visibility</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Identity</h3><p>The ABO/Rh label, uppercase.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Name <i class="req">*</i></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $bloodGroup->name) }}" maxlength="10">
                        @error('name') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Visibility</h3><p>Disabled groups are kept for history but unavailable for new records.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Status</label>
                        <label class="mc-check"><input type="checkbox" name="status" value="1" {{ $bloodGroup->status ? 'checked' : '' }}> Active</label>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update blood group</button>
                <a href="{{ route('admin.blood-groups.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
