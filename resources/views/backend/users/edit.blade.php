@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Access control</p>
        <h1 class="mc-title">Access for <em>{{ $user->name }}</em></h1>
        <p class="mc-sub">{{ $user->email }} · joined {{ $user->created_at?->format('M j, Y') ?? '—' }}</p>
    </div>
</div>

@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<form action="{{ route('admin.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mc-sec">
        <div class="mc-sec-hd"><span class="no">01</span><h3>Staff role</h3><p>drives login landing + panel gates</p></div>
        <div class="mc-sec-bd">
            <div class="mc-f full">
                <label>Primary role <i class="req">*</i></label>
                <select name="staff_role" required>
                    @foreach($staffRoles as $value)
                        <option value="{{ $value }}" {{ old('staff_role', $user->role) === $value ? 'selected' : '' }}>{{ ucfirst($value) }}</option>
                    @endforeach
                </select>
                <span class="mc-hint">Admins land on the admin panel, doctors on the doctor panel, patients on their profile. Switching a doctor away from “doctor” removes doctor-panel access.</span>
                @error('staff_role')<span class="mc-hint text-red">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>

    <div class="mc-formacts">
        <button type="submit" class="mc-btn"><i class="bi bi-check-lg"></i> Save access</button>
        <a href="{{ route('admin.users.index') }}" class="mc-btn ghost">Cancel</a>
    </div>
</form>

@endsection
