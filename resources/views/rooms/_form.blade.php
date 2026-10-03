@php($room ??= null)
<form action="{{ $formAction }}" method="POST">
    @csrf
    @if(($method ?? null) === 'PUT')
        @method('PUT')
    @endif

    <div class="mc-card">
        <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Room details</h5></div>
        <div class="grid grid-cols-1 gap-3 px-4.5 py-4 md:grid-cols-2">
            <div class="mc-f">
                <label>Ward <i class="req">*</i></label>
                <select name="ward_id" class="form-select" required>
                    <option value="">-- Select ward --</option>
                    @foreach($wards as $ward)
                        <option value="{{ $ward->id }}" @selected(old('ward_id', $room?->ward_id) == $ward->id)>{{ $ward->name }}</option>
                    @endforeach
                </select>
                @error('ward_id')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Room number <i class="req">*</i></label>
                <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $room?->room_number) }}" placeholder="e.g. 101" required>
                @error('room_number')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Room type <i class="req">*</i></label>
                <select name="room_type" class="form-select" required>
                    @foreach(\App\Models\Room::TYPES as $type)
                        <option value="{{ $type }}" @selected(old('room_type', $room?->room_type ?? 'General') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-line-2 px-4.5 py-4">
            <button type="submit" class="mc-btn">{{ $room ? 'Save changes' : 'Create room' }}</button>
        </div>
    </div>
</form>
