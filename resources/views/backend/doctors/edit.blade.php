@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Staff record</p>
        <h1 class="mc-title">Doctor <em>profile</em></h1>
        <p class="mc-sub">Identity, credentials and booking status. Changes apply on save — nothing goes live early.</p>
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

<form action="{{ route('admin.doctors.update', $doctor->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            @if($doctor->image)
                <div class="mc-avbig"><img src="{{ asset('storage/' . $doctor->image) }}" alt="{{ $doctor->name }}"></div>
            @else
                <div class="mc-avbig">{{ strtoupper(implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $doctor->name)), 0, 2))) }}</div>
            @endif
            <div class="k">Currently editing</div>
            <h2>{{ $doctor->name }}</h2>
            <p>{{ $doctor->department ?? 'General' }}{{ $doctor->specialist ? ' · ' . $doctor->specialist : '' }} · {{ $doctor->status == 1 ? 'Active' : 'Inactive' }}</p>
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
                        <label>Doctor Name</label>
                        <input type="text" name="name" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('name', $doctor->name) }}">
                    </div>
                    <div class="mc-f">
                        <label>Phone</label>
                        <input type="text" name="phone" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('phone', $doctor->phone) }}">
                    </div>
                    <div class="mc-f">
                        <label>Department</label>
                        <select name="department" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->name }}" {{ $doctor->department == $department->name ? 'selected' : '' }}>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mc-f">
                        <label>Specialist</label>
                        <input type="text" name="specialist" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('specialist', $doctor->specialist) }}">
                    </div>
                    <div class="mc-f">
                        <label>Degree</label>
                        <input type="text" name="degree" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('degree', $doctor->degree) }}">
                    </div>
                    <div class="mc-f">
                        <label>Availability</label>
                        <input type="text" name="availability" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('availability', $doctor->availability) }}">
                    </div>
                    <div class="mc-f full">
                        <label>Services</label>
                        <textarea name="services" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" rows="3">{{ old('services', $doctor->services) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Credentials</h3><p>Photo and booking status.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Doctor Image</label>
                        @if($doctor->image)
                            <img src="{{ asset('storage/' . $doctor->image) }}" class="mc-imgprev mb-2" alt="">
                        @endif
                        <input type="file" name="image" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                        <span class="mc-hint">JPG · PNG · WEBP · max 2 MB</span>
                    </div>
                    <div class="mc-f">
                        <label>Status</label>
                        <select name="status" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            <option value="1" {{ $doctor->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $doctor->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">03</span><h3>Login account</h3><p>How this doctor signs in.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Email <i class="req">*</i></label>
                        <input type="email" name="email" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('email') border-[#dc2626] @enderror" value="{{ old('email', $doctor->user->email ?? '') }}">
                        @error('email') <small class="text-xs text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Password <span class="mc-hint">(leave blank to keep current)</span></label>
                        <input type="password" name="password" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" placeholder="Leave blank to keep current">
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update doctor</button>
                <a href="{{ route('admin.doctors.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection