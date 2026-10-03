@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Staff record</p>
        <h1 class="mc-title">New <em>doctor</em></h1>
        <p class="mc-sub">Identity, credentials and booking status. The login account is created together with the profile.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 list-disc pl-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.doctors.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">DR</div>
            <div class="k">New record</div>
            <h2>Unsaved doctor</h2>
            <p>Profile + login go live together on save.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Identity</li>
                <li><span class="n">2</span>Credentials</li>
                <li><span class="n">3</span>Account</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Identity</h3><p>Who this doctor is on the roster.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Doctor Name <i class="req">*</i></label>
                        <input type="text" name="name" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" placeholder="Enter doctor name" value="{{ old('name') }}">
                    </div>
                    <div class="mc-f">
                        <label>Phone</label>
                        <input type="text" name="phone" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" placeholder="+880" value="{{ old('phone') }}">
                    </div>
                    <div class="mc-f">
                        <label>Department</label>
                        <select name="department" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->name }}" {{ old('department') == $department->name ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mc-f">
                        <label>Specialist</label>
                        <input type="text" name="specialist" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" placeholder="Heart specialist" value="{{ old('specialist') }}">
                    </div>
                    <div class="mc-f">
                        <label>Degree</label>
                        <input type="text" name="degree" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" placeholder="MBBS, FCPS" value="{{ old('degree') }}">
                    </div>
                    <div class="mc-f">
                        <label>Availability</label>
                        <input type="text" name="availability" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" placeholder="Everyday 9AM - 8PM" value="{{ old('availability') }}">
                    </div>
                    <div class="mc-f full">
                        <label>Services</label>
                        <textarea name="services" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" rows="3" placeholder="Consultation, Surgery, Treatment">{{ old('services') }}</textarea>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Credentials</h3><p>Photo and booking status.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Doctor Image</label>
                        <input type="file" name="image" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                        <span class="mc-hint">JPG · PNG · WEBP · max 2 MB</span>
                    </div>
                    <div class="mc-f">
                        <label>Status</label>
                        <select name="status" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">03</span><h3>Login account</h3><p>How this doctor signs in.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Email <i class="req">*</i></label>
                        <input type="email" name="email" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('email') border-[#dc2626] @enderror" placeholder="doctor@email.com" value="{{ old('email') }}">
                        @error('email') <small class="text-xs text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Password <i class="req">*</i></label>
                        <input type="password" name="password" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('password') border-[#dc2626] @enderror" placeholder="Min 8 characters">
                        @error('password') <small class="text-xs text-red-t">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save doctor</button>
                <a href="{{ route('admin.doctors.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection