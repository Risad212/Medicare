@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Staff</p>
        <h1 class="mc-title">Avail<em>ability</em></h1>
        <p class="mc-sub">{{ $doctor->name }} — weekly schedule, off days and slot control.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.doctors.index') }}" class="mc-btn ghost">Back to list</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 list-disc pl-4">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($openByWeekday->isEmpty())
    <div class="mb-4 rounded-lg bg-blue-bg px-4 py-3 text-sm text-blue-t">
        This doctor has no weekly schedule yet, so every slot is currently open for booking.
        Tick the slots below to set when this doctor actually sees patients.
    </div>
@endif

<div class="mc-card mb-4">
    <div class="border-b border-line-2 px-4.5 py-3">
        <h5 class="text-[15px] font-bold">Weekly Schedule</h5>
        <p class="mt-1 text-xs text-mut">
            Uncheck a slot to make it unavailable. Days with no slots selected become fully closed for that doctor.
        </p>
    </div>

    <form action="{{ route('admin.doctors.availability.update', $doctor->id) }}" method="POST">
        @csrf
        <div class="overflow-x-auto">
            <table class="mc-tbl text-left">
                <thead>
                    <tr>
                        <th>Day</th>
                        @foreach($slots as $slot)
                            <th>{{ $slot->time }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($weekdays as $day => $label)
                        <tr>
                            <td class="font-bold">{{ $label }}</td>
                            @foreach($slots as $slot)
                                <td>
                                    <input type="checkbox"
                                           class="h-[17px] w-[17px] accent-teal"
                                           name="schedules[{{ $day }}][]"
                                           value="{{ $slot->id }}"
                                           @checked(in_array($slot->id, $openByWeekday->get($day, []), true))>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4.5 py-3">
            <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save Schedule</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="mc-card">
        <div class="border-b border-line-2 px-4.5 py-3">
            <h5 class="text-[15px] font-bold">Off Days</h5>
            <p class="mt-1 text-xs text-mut">No appointments can be booked on these days regardless of the weekly schedule.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="mc-tbl">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reason</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offDays as $offDay)
                        <tr>
                            <td class="mc-num">{{ $offDay->date->format('d M Y') }}</td>
                            <td>{{ $offDay->reason ?? 'N/A' }}</td>
                            <td>
                                <div class="mc-acts">
                                    <form action="{{ route('admin.doctor-off-days.destroy', $offDay->id) }}" method="POST" onsubmit="return confirm('Remove this off-day?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mc-btn sm danger-ghost">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-3 text-center text-sm text-mut">No off days scheduled.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-line-2 px-4.5 py-3">
            <form action="{{ route('admin.doctors.off-days.store', $doctor->id) }}" method="POST" class="flex items-end gap-2">
                @csrf
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-bold tracking-wide text-ink-2">Date</label>
                    <input type="date" name="date" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" min="{{ now()->toDateString() }}" required>
                </div>
                <div class="flex-1">
                    <label class="mb-1 block text-xs font-bold tracking-wide text-ink-2">Reason</label>
                    <input type="text" name="reason" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" placeholder="Reason (optional)">
                </div>
                <button type="submit" class="mc-btn sm"><i class="bi bi-plus-lg"></i> Add</button>
            </form>
        </div>
    </div>
</div>

@endsection
