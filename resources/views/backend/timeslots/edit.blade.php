@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Scheduling</p>
        <h1 class="mc-title">Edit time <em>slot</em></h1>
        <p class="mc-sub">Update this time slot's details.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-card">
    <div class="border-b border-line-2 px-4.5 py-3">
        <h5 class="text-[15px] font-bold">Edit Time Slot</h5>
    </div>

    <div class="p-4.5">
        <form action="{{ route('admin.time-slots.update', $timeSlot->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Time</label>
                <input type="text" name="time" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal"
                    value="{{ old('time', $timeSlot->time) }}">
                @error('time') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="mc-check">
                    <input type="checkbox" name="status" value="1"
                        {{ $timeSlot->status ? 'checked' : '' }}>
                    <span>Active</span>
                </label>
            </div>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update Time Slot</button>
                <a href="{{ route('admin.time-slots.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
