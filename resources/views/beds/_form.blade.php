@php($bed ??= null)
<form action="{{ $formAction }}" method="POST">
    @csrf
    @if(($method ?? null) === 'PUT')
        @method('PUT')
    @endif

    <div class="mc-card">
        <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Bed details</h5></div>
        <div class="grid grid-cols-1 gap-3 px-4.5 py-4 md:grid-cols-2">
            <div class="mc-f">
                <label>Room <i class="req">*</i></label>
                <select name="room_id" class="form-select" required>
                    <option value="">-- Select room --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected(old('room_id', $bed?->room_id) == $room->id)>{{ $room->ward->name ?? '' }} · Room {{ $room->room_number }}</option>
                    @endforeach
                </select>
                @error('room_id')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Bed number <i class="req">*</i></label>
                <input type="text" name="bed_number" class="form-control" value="{{ old('bed_number', $bed?->bed_number) }}" placeholder="e.g. B-01" required>
                @error('bed_number')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Status <i class="req">*</i></label>
                <select name="status" class="form-select" required>
                    <option value="0" @selected(old('status', $bed?->status ?? 0) == 0)>Available</option>
                    <option value="2" @selected(old('status', $bed?->status) == 2)>Under maintenance</option>
                </select>
                <small class="mc-hint">Occupied is set only via patient assignment.</small>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-line-2 px-4.5 py-4">
            <button type="submit" class="mc-btn">{{ $bed ? 'Save changes' : 'Create bed' }}</button>
        </div>
    </div>
</form>
