@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Scheduling</p>
        <h1 class="mc-title">New time <em>slot</em></h1>
        <p class="mc-sub">Add a new time slot for appointments.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-card">
    <div class="border-b border-line-2 px-4.5 py-3">
        <h5 class="text-[15px] font-bold">Add New Time Slot</h5>
    </div>

    <div class="p-4.5">
        <form action="{{ route('admin.time-slots.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Time</label>
                <input type="text" name="time" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal"
                    placeholder="e.g. 09:00 AM" value="{{ old('time') }}">
                @error('time') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="mc-check">
                    <input type="checkbox" name="status" value="1" checked>
                    <span>Active</span>
                </label>
            </div>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save Time Slot</button>
                <a href="{{ route('admin.time-slots.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
