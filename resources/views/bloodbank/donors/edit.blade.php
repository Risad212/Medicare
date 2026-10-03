@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Edit <em>donor</em></h1>
        <p class="mc-sub">Keep the profile current — phone, group, and last donation.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.blood-donors.update', $donor->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            @php
                $initials = implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $donor->name)), 0, 2));
            @endphp
            <div class="mc-avbig">{{ strtoupper($initials) }}</div>
            <div class="k">Currently editing</div>
            <h2>{{ $donor->name }}</h2>
            <p>{{ $donor->bloodGroup->name ?? 'No group' }} · {{ $donor->phone }}</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Identity</li>
                <li><span class="n">2</span>Details</li>
                <li><span class="n">3</span>Notes</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Identity</h3><p>Name, group and how to reach the donor.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Full name <i class="req">*</i></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $donor->name) }}">
                        @error('name') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Blood group <i class="req">*</i></label>
                        <select name="blood_group_id" class="form-select">
                            <option value="">Select group…</option>
                            @foreach($bloodGroups as $group)
                                <option value="{{ $group->id }}" {{ old('blood_group_id', $donor->blood_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('blood_group_id') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Phone <i class="req">*</i></label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $donor->phone) }}">
                        @error('phone') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $donor->email) }}">
                        @error('email') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Details</h3><p>Demographics and last donation, when known.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Date of birth</label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($donor->date_of_birth)->format('Y-m-d')) }}">
                        @error('date_of_birth') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Select…</option>
                            <option value="male" {{ old('gender', $donor->gender) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $donor->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender', $donor->gender) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f full">
                        <label>Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $donor->address) }}</textarea>
                    </div>
                    <div class="mc-f">
                        <label>Last donation date</label>
                        <input type="date" name="last_donation_date" class="form-control" value="{{ old('last_donation_date', optional($donor->last_donation_date)->format('Y-m-d')) }}">
                        <small class="text-mut">Used by the administrative eligibility check only.</small>
                        @error('last_donation_date') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Status</label>
                        <label class="mc-check"><input type="checkbox" name="status" value="1" {{ $donor->status ? 'checked' : '' }}> Active</label>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">03</span><h3>Notes</h3><p>Anything staff should remember.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $donor->notes) }}</textarea>
                        @error('notes') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update donor</button>
                <a href="{{ route('admin.blood-donors.show', $donor->id) }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
