@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · In-patient</p>
        <h1 class="mc-title">Ro<em>oms</em></h1>
        <p class="mc-sub">Rooms within each ward.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.beds.index') }}" class="mc-btn ghost">Bed dashboard</a>
        <a href="{{ route('admin.rooms.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add room</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 mt-0 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Room</th>
                    <th>Ward</th>
                    <th>Type</th>
                    <th>Beds</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rooms as $key => $room)
                <tr>
                    <td class="mc-idx">{{ $rooms->firstItem() + $key }}</td>
                    <td><b>{{ $room->room_number }}</b></td>
                    <td>{{ $room->ward->name ?? '—' }}</td>
                    <td>{{ $room->room_type }}</td>
                    <td class="mc-num">{{ $room->beds_count }}</td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.rooms.show', $room->id) }}" class="mc-btn sm">View</a>
                            <a href="{{ route('admin.rooms.edit', $room->id) }}" class="mc-btn sm dark">Edit</a>
                            <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost" onclick="return confirm('Delete this room?')">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6"><div class="mc-empty"><b>No rooms</b>Create the first room.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($rooms->hasPages())
        <div class="px-4.5 py-4">{{ $rooms->links() }}</div>
    @endif
</div>

@endsection
