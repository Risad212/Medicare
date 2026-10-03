@php($ward ??= null)
<form action="{{ $formAction }}" method="POST">
    @csrf
    @if(($method ?? null) === 'PUT')
        @method('PUT')
    @endif

    <div class="mc-card">
        <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Ward details</h5></div>
        <div class="grid grid-cols-1 gap-3 px-4.5 py-4">
            <div class="mc-f">
                <label>Name <i class="req">*</i></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $ward?->name) }}" placeholder="e.g. General Ward, ICU" required>
                @error('name')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Floor, capacity notes...">{{ old('description', $ward?->description) }}</textarea>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-line-2 px-4.5 py-4">
            <button type="submit" class="mc-btn">{{ $ward ? 'Save changes' : 'Create ward' }}</button>
        </div>
    </div>
</form>
