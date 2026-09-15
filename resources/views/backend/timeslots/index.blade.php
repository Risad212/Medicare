@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Scheduling</p>
        <h1 class="mc-title">Time <em>slots</em></h1>
        <p class="mc-sub">All available time slots for appointments.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.time-slots.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add New</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($timeSlots as $slot)
                    <tr>
                        <td class="mc-idx">{{ $loop->iteration }}</td>
                        <td class="mc-num">{{ $slot->time }}</td>
                        <td>
                            @if($slot->status)
                                <span class="mc-pill p-active"><i></i>Active</span>
                            @else
                                <span class="mc-pill p-inactive"><i></i>Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="mc-acts">
                                <a href="{{ route('admin.time-slots.edit', $slot->id) }}" class="mc-btn sm"><i class="bi bi-pencil"></i> Edit</a>
                                <form action="{{ route('admin.time-slots.destroy', $slot->id) }}" method="POST"  onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4"><div class="mc-empty"><b>Nothing on this chart</b>No time slots found.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
