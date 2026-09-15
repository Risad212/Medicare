@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Diagnostics</p>
        <h1 class="mc-title">New Lab <em>request</em></h1>
        <p class="mc-sub">Order lab work for a patient and pick the required tests.</p>
    </div>
</div>

@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 mt-0 list-inside list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('doctor.lab-orders.store') }}" method="POST">
    @csrf

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">LB</div>
            <div class="k">New record</div>
            <h2>Unsaved request</h2>
            <p>Patient details and the set of tests to run.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Patient</li>
                <li><span class="n">2</span>Order &amp; tests</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Patient</h3><p>Pick an existing patient or enter new details.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Patient (registered)</label>
                        <select name="user_id" id="patient_select" class="form-select">
                            <option value="">-- Walk-in / New patient --</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}"
                                        data-name="{{ $patient->name }}"
                                        data-email="{{ $patient->email }}"
                                        data-phone="{{ $patient->phone }}"
                                        @selected(old('user_id') == $patient->id)>
                                    {{ $patient->name }} ({{ $patient->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="mc-hint">Pick an existing patient to auto-fill the details below.</small>
                    </div>

                    <div class="mc-f">
                        <label>Linked Appointment (optional)</label>
                        <select name="appointment_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($appointments as $appointment)
                                <option value="{{ $appointment->id }}"
                                        @selected(old('appointment_id') == $appointment->id)>
                                    #{{ $appointment->id }} - {{ $appointment->patient_name }} ({{ $appointment->appointment_date }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mc-f">
                        <label>Patient Name <i class="req">*</i></label>
                        <input type="text" name="patient_name" id="patient_name" class="form-control" value="{{ old('patient_name') }}" placeholder="Patient full name" required>
                    </div>

                    <div class="mc-f">
                        <label>Phone</label>
                        <input type="text" name="phone" id="patient_phone" class="form-control" value="{{ old('phone') }}" placeholder="Contact number">
                    </div>

                    <div class="mc-f">
                        <label>Email</label>
                        <input type="email" name="email" id="patient_email" class="form-control" value="{{ old('email') }}" placeholder="Email address">
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Order</h3><p>Priority and a clinical note for the laboratory.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Priority</label>
                        <select name="priority" class="form-select">
                            <option value="normal" @selected(old('priority') === 'normal')>Normal</option>
                            <option value="urgent" @selected(old('priority') === 'urgent')>Urgent</option>
                        </select>
                    </div>
                    <div class="mc-f">
                        <label>Doctor Notes</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Clinical note for the laboratory...">{{ old('note') }}</textarea>
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">03</span><h3>Select Tests <i class="req">*</i></h3><p>Running total updates below.</p></div>
                <div class="grid grid-cols-1 gap-3 p-4.5 md:grid-cols-2 lg:grid-cols-3">
                    @forelse($tests as $test)
                        <label class="flex cursor-pointer items-start gap-2.5 rounded-lg border border-line bg-white px-3 py-2.5" for="test_{{ $test->id }}">
                            <input
                                type="checkbox"
                                class="test-checkbox mt-0.5 accent-teal"
                                name="lab_test_ids[]"
                                value="{{ $test->id }}"
                                data-price="{{ $test->price }}"
                                id="test_{{ $test->id }}"
                                @checked(in_array($test->id, old('lab_test_ids', [])))>
                            <span>
                                <span class="font-semibold">{{ $test->name }} <span class="text-mut">(${{ number_format($test->price, 2) }})</span></span>
                                @if($test->normal_range)
                                    <span class="block text-xs text-mut">{{ $test->normal_range }}{{ $test->unit ? ' ' . $test->unit : '' }}</span>
                                @endif
                            </span>
                        </label>
                    @empty
                        <div class="col-span-full rounded-lg bg-amber-bg px-4 py-3 text-sm text-amber-t">
                            No active lab tests configured. Please ask the administrator to add some first.
                        </div>
                    @endforelse
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-2 px-4.5 py-4">
                    <p class="mb-0">Estimated Total: <strong id="order_total">$0.00</strong></p>
                    <button type="submit" class="mc-btn"><i class="bi bi-clipboard2-pulse"></i> Create Lab Request</button>
                </div>
            </section>
        </div>
    </div>
</form>

<script>
    (function () {
        var patientSelect = document.getElementById('patient_select');
        if (patientSelect) {
            patientSelect.addEventListener('change', function () {
                var option = this.options[this.selectedIndex];
                if (option.value) {
                    document.getElementById('patient_name').value = option.dataset.name || '';
                    document.getElementById('patient_phone').value = option.dataset.phone || '';
                    document.getElementById('patient_email').value = option.dataset.email || '';
                }
            });
        }

        var checkboxes = document.querySelectorAll('.test-checkbox');
        var totalEl = document.getElementById('order_total');

        function recalc() {
            var total = 0;
            checkboxes.forEach(function (cb) {
                if (cb.checked) {
                    total += parseFloat(cb.dataset.price || 0);
                }
            });
            totalEl.textContent = '$' + total.toFixed(2);
        }

        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', recalc);
        });
        recalc();
    })();
</script>

@endsection