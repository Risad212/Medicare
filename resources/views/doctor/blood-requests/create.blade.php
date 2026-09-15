@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">New blood <em>request</em></h1>
        <p class="mc-sub">Raise a need for one of your patients — admin approves and reserves.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

@if($patients->isEmpty())
    <div class="mc-card p-3">
        <div class="mc-empty"><b>No patients</b>You can only raise requests for patients with an appointment under your care.</div>
        <a href="{{ route('doctor.blood-requests.index') }}" class="mc-btn mt-2">Back to requests</a>
    </div>
@else
<form action="{{ route('doctor.blood-requests.store') }}" method="POST">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">RQ</div>
            <div class="k">New record</div>
            <h2>Unsaved request</h2>
            <p>Submits as <b>Pending</b> for admin approval.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Patient &amp; group</li>
                <li><span class="n">2</span>Urgency</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Patient &amp; group</h3><p>Who needs blood and which type.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Patient <i class="req">*</i></label>
                        <select name="patient_id" class="form-select">
                            <option value="">Select patient…</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>{{ $patient->name }}</option>
                            @endforeach
                        </select>
                        @error('patient_id') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Blood group <i class="req">*</i></label>
                        <select name="blood_group_id" class="form-select">
                            <option value="">Select group…</option>
                            @foreach($bloodGroups as $group)
                                <option value="{{ $group->id }}" {{ old('blood_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('blood_group_id') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Quantity (ml) <i class="req">*</i></label>
                        <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 450) }}" min="1">
                        @error('quantity') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Required date <i class="req">*</i></label>
                        <input type="date" name="required_date" class="form-control" value="{{ old('required_date', now()->format('Y-m-d')) }}">
                        @error('required_date') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Department</label>
                        <input type="text" name="department" class="form-control" value="{{ old('department') }}" placeholder="e.g. Surgery">
                    </div>
                    <div class="mc-f full">
                        <label>Reason</label>
                        <textarea name="reason" class="form-control" rows="3">{{ old('reason') }}</textarea>
                    </div>
                    <div class="mc-f full">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Urgency</h3><p>Drives dashboard attention.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Urgency <i class="req">*</i></label>
                        <select name="urgency" class="form-select">
                            <option value="normal" {{ old('urgency', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="urgent" {{ old('urgency') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="emergency" {{ old('urgency') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                        </select>
                        @error('urgency') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Submit request</button>
                <a href="{{ route('doctor.blood-requests.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endif

@endsection