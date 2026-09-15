@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Records</p>
        <h1 class="mc-title">New pati<em>ent</em></h1>
        <p class="mc-sub">Register a patient and create their login in one step.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form action="{{ route('admin.patients.store') }}" method="POST">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">PT</div>
            <div class="k">New record</div>
            <h2>Unsaved patient</h2>
            <p>Record + login go live together on save.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Identity</li>
                <li><span class="n">2</span>Login</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Identity</h3><p>Who this patient is.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Patient Name <i class="req">*</i></label>
                        <input type="text" name="name" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('name') }}" required>
                    </div>
                    <div class="mc-f">
                        <label>Email <i class="req">*</i></label>
                        <input type="email" name="email" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('email') }}" required>
                    </div>
                    <div class="mc-f full">
                        <label>Phone</label>
                        <input type="text" name="phone" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('phone') }}">
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Login</h3><p>How this patient signs in.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Password <i class="req">*</i></label>
                        <input type="password" name="password" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" required>
                    </div>
                    <div class="mc-f">
                        <label>Confirm Password <i class="req">*</i></label>
                        <input type="password" name="password_confirmation" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" required>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn">Save patient</button>
                <a href="{{ route('admin.patients.index') }}" class="mc-btn ghost">Back</a>
            </div>
        </div>
    </div>
</form>

@endsection