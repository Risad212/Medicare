@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Issue blood — <em>#{{ $request->id }}</em></h1>
        <p class="mc-sub">Choose a reserved bag and record the handover details.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<div class="mc-bar">
    <span class="text-mut">Patient: <b>{{ $request->patient->name }}</b> · Group: <b>{{ $request->bloodGroup->name }}</b> · Required: {{ $request->quantity }} ml · Issued: {{ $request->issuedQuantity() }} ml · Remaining: {{ max(0, $request->quantity - $request->issuedQuantity()) }} ml</span>
</div>

<form action="{{ route('admin.blood-issues.store', $request->id) }}" method="POST">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">IS</div>
            <div class="k">New record</div>
            <h2>Handover</h2>
            <p>Only bags reserved for this request can be selected.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Select bag</li>
                <li><span class="n">2</span>Receiver</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Unit</h3><p>The reserved bag being handed over.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f full">
                        <label>Bag <i class="req">*</i></label>
                        <select name="donation_id" class="form-select">
                            <option value="">Select bag…</option>
                            @foreach($reservedBags as $bag)
                                <option value="{{ $bag->id }}" {{ old('donation_id') == $bag->id ? 'selected' : '' }}>
                                    {{ $bag->bag_number ?: '#' . $bag->id }} — {{ $bag->quantity }} ml — expires {{ $bag->expiry_date->format('Y-m-d') }}
                                </option>
                            @endforeach
                        </select>
                        @error('donation_id') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                    <div class="mc-f">
                        <label>Issue date <i class="req">*</i></label>
                        <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', now()->format('Y-m-d')) }}">
                        @error('issue_date') <small class="text-red-t">{{ $message }}</small> @enderror
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Receiver</h3><p>Who physically receives the unit.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Receiver name</label>
                        <input type="text" name="receiver_name" class="form-control" value="{{ old('receiver_name', $request->patient->name) }}">
                    </div>
                    <div class="mc-f">
                        <label>Receiver phone</label>
                        <input type="text" name="receiver_phone" class="form-control" value="{{ old('receiver_phone') }}">
                    </div>
                    <div class="mc-f full">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </section>

            <div class="mc-formacts">
                <button type="submit" class="mc-btn"><i class="bi bi-droplet-half"></i> Issue blood</button>
                <a href="{{ route('admin.blood-requests.show', $request->id) }}" class="mc-btn ghost">Cancel</a>
            </div>
        </div>
    </div>
</form>

@endsection