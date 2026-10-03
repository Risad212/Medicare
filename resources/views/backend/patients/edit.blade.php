@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Records</p>
        <h1 class="mc-title">Patient <em>record</em></h1>
        <p class="mc-sub">Contact and clinical basics. Linked visits stay untouched.</p>
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

<form action="{{ route('admin.patients.update', $patient->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">{{ strtoupper(implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $patient->name)), 0, 2))) }}</div>
            <div class="k">Currently editing</div>
            <h2>{{ $patient->name }}</h2>
            <p>{{ $patient->email ?? 'No email' }} · since {{ $patient->created_at->format('Y') }}</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Contact</li>
                <li><span class="n">2</span>Clinical</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Contact</h3><p>How to reach them.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Patient Name</label>
                        <input type="text" name="name" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('name', $patient->name) }}" required>
                    </div>
                    <div class="mc-f">
                        <label>Email</label>
                        <input type="email" name="email" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('email', $patient->email) }}">
                    </div>
                    <div class="mc-f">
                        <label>Phone</label>
                        <input type="text" name="phone" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('phone', $patient->phone) }}">
                    </div>
                    <div class="mc-f">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ old('date_of_birth', $patient->date_of_birth) }}">
                    </div>
                    <div class="mc-f full">
                        <label>Address</label>
                        <textarea name="address" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" rows="3">{{ old('address', $patient->address) }}</textarea>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Clinical</h3><p>Basics for the chart.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Gender</label>
                        <select name="gender" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="mc-f">
                        <label>Blood Group</label>
                        <select name="blood_group" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            <option value="">Select Blood Group</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group', $patient->blood_group) == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn">Update patient</button>
                <a href="{{ route('admin.patients.index') }}" class="mc-btn ghost">Back</a>
            </div>
        </div>
    </div>
</form>

@endsection