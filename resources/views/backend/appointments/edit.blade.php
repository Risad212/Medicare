@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Front desk</p>
        <h1 class="mc-title">Edit appoint<em>ment</em></h1>
        <p class="mc-sub">{{ $appointment->patient_name }} · {{ $appointment->doctor->name ?? 'N/A' }} · {{ $appointment->appointment_date->format('d M Y') }}</p>
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

<form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">{{ strtoupper(implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $appointment->patient_name)), 0, 2))) }}</div>
            <div class="k">Currently editing</div>
            <h2>{{ $appointment->patient_name }}</h2>
            <p>{{ $appointment->timeSlot->time ?? 'N/A' }} · {{ $appointment->visit_type_label }}</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Schedule</li>
                <li><span class="n">2</span>Patient</li>
                <li><span class="n">3</span>State</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Schedule</h3><p>Doctor, day and type.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Doctor</label>
                        <select name="doctor_id" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ $appointment->doctor_id == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mc-f">
                        <label>Visit Type</label>
                        <select name="visit_type" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            <option value="1" {{ $appointment->visit_type == 1 ? 'selected' : '' }}>First Visit</option>
                            <option value="2" {{ $appointment->visit_type == 2 ? 'selected' : '' }}>Second Visit</option>
                            <option value="3" {{ $appointment->visit_type == 3 ? 'selected' : '' }}>Report Review</option>
                        </select>
                    </div>
                    <div class="mc-f">
                        <label>Appointment Date</label>
                        <input type="date" name="date" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ $appointment->appointment_date->format('Y-m-d') }}">
                    </div>
                    <div class="mc-f">
                        <label>Time Slot</label>
                        <select name="time_slot_id" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            @foreach($slots as $slot)
                                <option value="{{ $slot->id }}" {{ $appointment->time_slot_id == $slot->id ? 'selected' : '' }}>{{ $slot->time }}</option>
                            @endforeach
                        </select>
                        <span class="mc-hint">Changing the slot re-checks availability and double-booking.</span>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Patient</h3><p>Who is coming in.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Patient Name</label>
                        <input type="text" name="name" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ $appointment->patient_name }}">
                    </div>
                    <div class="mc-f">
                        <label>Phone</label>
                        <input type="text" name="phone" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ $appointment->phone }}">
                    </div>
                    <div class="mc-f">
                        <label>Age</label>
                        <input type="number" name="age" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ $appointment->age }}">
                    </div>
                    <div class="mc-f">
                        <label>Gender</label>
                        <select name="gender" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            <option value="1" {{ $appointment->gender == 1 ? 'selected' : '' }}>Male</option>
                            <option value="2" {{ $appointment->gender == 2 ? 'selected' : '' }}>Female</option>
                            <option value="3" {{ $appointment->gender == 3 ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="mc-f full">
                        <label>Email</label>
                        <input type="email" name="email" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" value="{{ $appointment->email }}">
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">03</span><h3>State</h3><p>Where this booking stands.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Status</label>
                        <select name="status" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            <option value="0" {{ $appointment->status == 0 ? 'selected' : '' }}>Pending</option>
                            <option value="1" {{ $appointment->status == 1 ? 'selected' : '' }}>Approved</option>
                            <option value="2" {{ $appointment->status == 2 ? 'selected' : '' }}>Completed</option>
                            <option value="3" {{ $appointment->status == 3 ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn">Update appointment</button>
                <a href="{{ route('admin.appointments.index') }}" class="mc-btn ghost">Back</a>
            </div>
        </div>
    </div>
</form>

@endsection