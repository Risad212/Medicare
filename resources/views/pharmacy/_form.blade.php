@php($medicine ??= null)
<form action="{{ $formAction }}" method="POST">
    @csrf
    @if(($method ?? null) === 'PUT')
        @method('PUT')
    @endif

    <div class="mc-card">
        <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Medicine details</h5></div>
        <div class="grid grid-cols-1 gap-3 px-4.5 py-4 md:grid-cols-2">
            <div class="mc-f">
                <label>Brand name <i class="req">*</i></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $medicine?->name) }}" placeholder="e.g. Napa 500" required>
                @error('name')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Generic name</label>
                <input type="text" name="generic_name" class="form-control" value="{{ old('generic_name', $medicine?->generic_name) }}" placeholder="e.g. Paracetamol">
            </div>
            <div class="mc-f">
                <label>Unit <i class="req">*</i></label>
                <input type="text" name="unit" class="form-control" value="{{ old('unit', $medicine?->unit ?? 'tablet') }}" placeholder="tablet, bottle, vial" required>
            </div>
            <div class="mc-f">
                <label>Expiry date</label>
                <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', $medicine?->expiry_date?->format('Y-m-d')) }}">
            </div>
            <div class="mc-f">
                <label>Stock quantity <i class="req">*</i></label>
                <input type="number" name="stock_quantity" class="form-control" min="0" max="1000000" value="{{ old('stock_quantity', $medicine?->stock_quantity ?? 0) }}" required>
                @error('stock_quantity')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Unit price <i class="req">*</i></label>
                <input type="number" name="unit_price" class="form-control" min="0" step="0.01" value="{{ old('unit_price', $medicine?->unit_price ?? 0) }}" required>
            </div>
            <div class="mc-f">
                <label>Low-stock alert at <i class="req">*</i></label>
                <input type="number" name="low_stock_threshold" class="form-control" min="0" value="{{ old('low_stock_threshold', $medicine?->low_stock_threshold ?? 10) }}" required>
                <small class="mc-hint">Flagged as low stock at or below this quantity.</small>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-line-2 px-4.5 py-4">
            <button type="submit" class="mc-btn">{{ $medicine ? 'Save changes' : 'Add medicine' }}</button>
        </div>
    </div>
</form>
