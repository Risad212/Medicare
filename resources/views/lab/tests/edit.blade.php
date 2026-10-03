@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Laboratory</p>
        <h1 class="mc-title">Edit lab <em>test</em></h1>
        <p class="mc-sub">Update test details, pricing and reference ranges.</p>
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

<div class="mc-card">
    <div class="border-b border-line-2 px-4.5 py-3">
        <h5 class="text-[15px] font-bold">Edit Lab Test</h5>
    </div>

    <div class="p-4.5">
        <form action="{{ route('admin.lab-tests.update', $labTest->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Test Name <span class="text-[#dc2626]">*</span></label>
                        <input type="text" name="name" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal"
                            placeholder="e.g. Complete Blood Count (CBC)" value="{{ old('name', $labTest->name) }}">
                        @error('name') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Category</label>
                        <input type="text" name="category" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal"
                            placeholder="e.g. Hematology, Biochemistry, Urine" value="{{ old('category', $labTest->category) }}">
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Description</label>
                        <textarea name="description" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" rows="4"
                            placeholder="What this test measures...">{{ old('description', $labTest->description) }}</textarea>
                    </div>
                </div>

                <div>
                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Price (&#2547;/&#36;) <span class="text-[#dc2626]">*</span></label>
                        <input type="number" name="price" step="0.01" min="0" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal"
                            placeholder="e.g. 450.00" value="{{ old('price', $labTest->price) }}">
                        @error('price') <p class="mt-1 text-xs text-red-t">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Normal Range</label>
                        <input type="text" name="normal_range" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal"
                            placeholder="e.g. 13.0 - 17.0" value="{{ old('normal_range', $labTest->normal_range) }}">
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Unit</label>
                        <input type="text" name="unit" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal"
                            placeholder="e.g. g/dL, mg/dL, cells/&#956;L" value="{{ old('unit', $labTest->unit) }}">
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-xs font-bold tracking-wide text-ink-2">Status</label>
                        <select name="status" class="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal">
                            <option value="1" {{ old('status', $labTest->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $labTest->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update Lab Test</button>
                <a href="{{ route('admin.lab-tests.index') }}" class="mc-btn ghost">Back</a>
            </div>
        </form>
    </div>
</div>

@endsection
