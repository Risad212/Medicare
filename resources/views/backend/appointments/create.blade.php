@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Front desk</p>
        <h1 class="mc-title">Book appoint<em>ment</em></h1>
        <p class="mc-sub">Patient, doctor and slot. Double-booked slots are refused automatically.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.appointments.store') }}" method="POST">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">AP</div>
            <div class="k">New record</div>
            <h2>Unsaved booking</h2>
            <p>Draft — nothing is booked until you save.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Schedule</li>
                <li><span class="n">2</span>Patient</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Schedule</h3><p>Doctor, day and time.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Doctor <i class="req">*</i></label>
                        <select name="doctor_id" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('doctor_id') border-[#dc2626] @enderror">
                            <option value="">Select Doctor</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                        @error('doctor_id') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Time Slot <i class="req">*</i></label>
                        <select name="time_slot_id" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('time_slot_id') border-[#dc2626] @enderror">
                            <option value="">Select Time Slot</option>
                            @foreach($timeSlots as $slot)
                                <option value="{{ $slot->id }}" {{ old('time_slot_id') == $slot->id ? 'selected' : '' }}>{{ $slot->time }}</option>
                            @endforeach
                        </select>
                        @error('time_slot_id') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Appointment Date <i class="req">*</i></label>
                        <input type="date" name="date" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('date') border-[#dc2626] @enderror" value="{{ old('date', date('Y-m-d')) }}">
                        @error('date') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Visit Type <i class="req">*</i></label>
                        <select name="visit_type" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('visit_type') border-[#dc2626] @enderror">
                            <option value="1" {{ old('visit_type') == 1 ? 'selected' : '' }}>First Visit</option>
                            <option value="2" {{ old('visit_type') == 2 ? 'selected' : '' }}>Second Visit</option>
                            <option value="3" {{ old('visit_type') == 3 ? 'selected' : '' }}>Report Review</option>
                        </select>
                        @error('visit_type') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Patient</h3><p>Who is coming in.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Patient Name <i class="req">*</i></label>
                        <input type="text" name="name" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('name') border-[#dc2626] @enderror" value="{{ old('name') }}">
                        @error('name') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Phone <i class="req">*</i></label>
                        <input type="text" name="phone" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('phone') border-[#dc2626] @enderror" value="{{ old('phone') }}">
                        @error('phone') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Age</label>
                        <input type="number" name="age" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('age') border-[#dc2626] @enderror" value="{{ old('age') }}">
                        @error('age') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Gender <i class="req">*</i></label>
                        <select name="gender" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('gender') border-[#dc2626] @enderror">
                            <option value="1" {{ old('gender') == 1 ? 'selected' : '' }}>Male</option>
                            <option value="2" {{ old('gender') == 2 ? 'selected' : '' }}>Female</option>
                            <option value="3" {{ old('gender') == 3 ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>
                    <div class="mc-f full">
                        <label>Email</label>
                        <input type="email" name="email" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal @error('email') border-[#dc2626] @enderror" value="{{ old('email') }}">
                        @error('email') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn">Create appointment</button>
                <a href="{{ route('admin.appointments.index') }}" class="mc-btn ghost">Back</a>
            </div>
        </div>
    </div>
</form>

@endsection