@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Edit <em>donation</em></h1>
        <p class="mc-sub">Correct unit details; status changes are best done from the list.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.blood-donations.update', $donation->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">{{ $donation->bloodGroup->name }}</div>
            <div class="k">Currently editing</div>
            <h2>Bag {{ $donation->bag_number ?: '#'.$donation->id }}</h2>
            <p>Status: {{ ucfirst($donation->status) }}</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Donor &amp; group</li>
                <li><span class="n">2</span>Unit details</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Donor &amp; group</h3><p>Who donated and what blood type.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Donor <i class="req">*</i></label>
                        <select name="donor_id" class="form-select">
                            @foreach($donors as $donor)
                                <option value="{{ $donor->id }}" {{ old('donor_id', $donation->donor_id) == $donor->id ? 'selected' : '' }}>{{ $donor->name }} — {{ $donor->bloodGroup->name }}</option>
                            @endforeach
                        </select>
                        @error('donor_id') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Blood group <i class="req">*</i></label>
                        <select name="blood_group_id" class="form-select">
                            @foreach($bloodGroups as $group)
                                <option value="{{ $group->id }}" {{ old('blood_group_id', $donation->blood_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                            @endforeach
                        </select>
                        @error('blood_group_id') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Unit details</h3><p>How much, when, and how long it lasts.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Donation date <i class="req">*</i></label>
                        <input type="date" name="donation_date" class="form-control" value="{{ old('donation_date', $donation->donation_date->format('Y-m-d')) }}">
                        @error('donation_date') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Quantity (ml) <i class="req">*</i></label>
                        <input type="number" name="quantity" class="form-control" value="{{ old('quantity', $donation->quantity) }}" min="1">
                        @error('quantity') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Bag number</label>
                        <input type="text" name="bag_number" class="form-control" value="{{ old('bag_number', $donation->bag_number) }}">
                    </div>
                    <div class="mc-f">
                        <label>Expiry date <i class="req">*</i></label>
                        <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', $donation->expiry_date->format('Y-m-d')) }}">
                        @error('expiry_date') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f full">
                        <label>Collection location</label>
                        <input type="text" name="collection_location" class="form-control" value="{{ old('collection_location', $donation->collection_location) }}">
                    </div>
                    <div class="mc-f full">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $donation->notes) }}</textarea>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Update donation</button>
                <a href="{{ route('admin.blood-donations.index') }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection
