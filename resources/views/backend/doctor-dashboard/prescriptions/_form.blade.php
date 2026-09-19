@php
    $prescription ??= null;
    $savedItems = $prescription
        ? $prescription->items->map(fn ($item) => [
            'medicine_name' => $item->medicine_name,
            'dosage' => $item->dosage ?? '',
            'frequency' => $item->frequency ?? '',
            'duration' => $item->duration ?? '',
            'quantity' => $item->quantity ?? '',
            'instructions' => $item->instructions ?? '',
        ])->all()
        : [];
    $rows = old('items', $savedItems ?: [[
        'medicine_name' => '',
        'dosage' => '',
        'frequency' => '',
        'duration' => '',
        'quantity' => '',
        'instructions' => '',
    ]]);
@endphp

<form action="{{ $formAction }}" method="POST">
    @csrf
    @if(($method ?? null) === 'PUT')
        @method('PUT')
    @endif

    <div class="mc-grid">
        <aside class="mc-side">
            <div class="mc-avbig">RX</div>
            <div class="k">{{ $prescription ? 'Edit record' : 'New record' }}</div>
            <h2>Prescription</h2>
            <p>Patient details, diagnosis and the medicines to dispense.</p>
            <ul class="mc-steps">
                <li><span class="n">1</span>Patient</li>
                <li><span class="n">2</span>Symptoms &amp; diagnosis</li>
                <li><span class="n">3</span>Medicines</li>
            </ul>
        </aside>

        <div>
            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">01</span><h3>Patient</h3><p>Pick a linked appointment or enter walk-in details.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Linked Appointment (optional)</label>
                        <select name="appointment_id" id="appointment_select" class="form-select">
                            <option value="">-- Walk-in / New patient --</option>
                            @foreach($appointments as $appointment)
                                <option value="{{ $appointment->id }}"
                                        data-name="{{ $appointment->patient_name }}"
                                        data-age="{{ $appointment->age }}"
                                        data-gender="{{ $appointment->gender }}"
                                        data-phone="{{ $appointment->phone }}"
                                        data-email="{{ $appointment->email }}"
                                        @selected(old('appointment_id', $prescription?->appointment_id) == $appointment->id)>
                                    #{{ $appointment->id }} - {{ $appointment->patient_name }} ({{ $appointment->appointment_date }})
                                </option>
                            @endforeach
                        </select>
                        <small class="mc-hint">Picking an appointment fills the patient details and links the record to that visit.</small>
                    </div>

                    <div class="mc-f">
                        <label>Patient Name <i class="req">*</i></label>
                        <input type="text" name="patient_name" id="patient_name" class="form-control"
                               value="{{ old('patient_name', $prescription?->patient_name) }}" placeholder="Patient full name">
                    </div>

                    <div class="mc-f">
                        <label>Age</label>
                        <input type="number" name="age" id="patient_age" class="form-control" min="0" max="130"
                               value="{{ old('age', $prescription?->age) }}" placeholder="Age">
                    </div>

                    <div class="mc-f">
                        <label>Gender</label>
                        <select name="gender" id="patient_gender" class="form-select">
                            <option value="">Select</option>
                            <option value="1" @selected(old('gender', $prescription?->gender) == 1)>Male</option>
                            <option value="2" @selected(old('gender', $prescription?->gender) == 2)>Female</option>
                            <option value="3" @selected(old('gender', $prescription?->gender) == 3)>Other</option>
                        </select>
                    </div>

                    <div class="mc-f">
                        <label>Phone</label>
                        <input type="text" name="phone" id="patient_phone" class="form-control"
                               value="{{ old('phone', $prescription?->phone) }}" placeholder="Contact number">
                    </div>

                    <div class="mc-f">
                        <label>Email</label>
                        <input type="email" name="email" id="patient_email" class="form-control"
                               value="{{ old('email', $prescription?->email) }}" placeholder="Email address">
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">02</span><h3>Symptoms &amp; Diagnosis</h3><p>Clinical notes for this visit.</p></div>
                <div class="mc-sec-bd">
                    <div class="mc-f">
                        <label>Symptoms</label>
                        <textarea name="symptoms" class="form-control" rows="2" placeholder="Chief complaints...">{{ old('symptoms', $prescription?->symptoms) }}</textarea>
                    </div>
                    <div class="mc-f full">
                        <label>Diagnosis <i class="req">*</i></label>
                        <textarea name="diagnosis" class="form-control" rows="3" placeholder="Diagnosis / findings..." required>{{ old('diagnosis', $prescription?->diagnosis) }}</textarea>
                    </div>
                    <div class="mc-f full">
                        <label>Advice / Notes</label>
                        <textarea name="advice" class="form-control" rows="2" placeholder="Diet, rest, follow-up instructions...">{{ old('advice', $prescription?->advice) }}</textarea>
                    </div>
                    <div class="mc-f">
                        <label>Follow-up Date</label>
                        <input type="date" name="follow_up_date" class="form-control"
                               value="{{ old('follow_up_date', $prescription?->follow_up_date) }}">
                    </div>
                </div>
            </section>

            <section class="mc-sec">
                <div class="mc-sec-hd"><span class="no">03</span><h3>Medicines <i class="req">*</i></h3><p>Add at least one medicine to prescribe.</p></div>

                {{-- Hidden template used by the "Add medicine" button --}}
                <template id="item-row-template">
                    <tr class="item-row">
                        <td class="text-center">
                            <input type="text" name="items[__i__][medicine_name]" class="form-control" placeholder="Medicine name" required>
                        </td>
                        <td>
                            <input type="text" name="items[__i__][dosage]" class="form-control" placeholder="e.g. 500mg">
                        </td>
                        <td>
                            <input type="text" name="items[__i__][frequency]" class="form-control" placeholder="e.g. 1-0-1">
                        </td>
                        <td>
                            <input type="text" name="items[__i__][duration]" class="form-control" placeholder="e.g. 7 days">
                        </td>
                        <td>
                            <input type="text" name="items[__i__][quantity]" class="form-control" placeholder="e.g. 10">
                        </td>
                        <td>
                            <input type="text" name="items[__i__][instructions]" class="form-control" placeholder="e.g. after meals">
                        </td>
                        <td class="text-center">
                            <button type="button" class="mc-btn sm danger-ghost remove-item" title="Remove row">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>

                <div class="overflow-x-auto">
                    <table class="mc-tbl" id="items_table">
                        <thead>
                            <tr>
                                <th style="width:20%">Medicine <i class="req">*</i></th>
                                <th>Dosage</th>
                                <th>Frequency</th>
                                <th>Duration</th>
                                <th>Quantity</th>
                                <th>Instructions</th>
                                <th style="width:52px"></th>
                            </tr>
                        </thead>
                        <tbody id="items_tbody">
                            @foreach($rows as $index => $item)
                                <tr class="item-row">
                                    <td>
                                        <input type="text" name="items[{{ $index }}][medicine_name]" class="form-control"
                                               placeholder="Medicine name" value="{{ $item['medicine_name'] }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][dosage]" class="form-control"
                                               placeholder="e.g. 500mg" value="{{ $item['dosage'] }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][frequency]" class="form-control"
                                               placeholder="e.g. 1-0-1" value="{{ $item['frequency'] }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][duration]" class="form-control"
                                               placeholder="e.g. 7 days" value="{{ $item['duration'] }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][quantity]" class="form-control"
                                               placeholder="e.g. 10" value="{{ $item['quantity'] }}">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][instructions]" class="form-control"
                                               placeholder="e.g. after meals" value="{{ $item['instructions'] }}">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="mc-btn sm danger-ghost remove-item" title="Remove row">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-2 px-4.5 py-4">
                    <button type="button" id="add_item_btn" class="mc-btn sm ghost">
                        <i class="bi bi-plus-lg"></i> Add Medicine
                    </button>
                    <button type="submit" class="mc-btn">
                        <i class="bi bi-capsule"></i> {{ $prescription ? 'Save Changes' : 'Save Prescription' }}
                    </button>
                </div>
            </section>
        </div>
    </div>
</form>

<script>
    (function () {
        var select = document.getElementById('appointment_select');
        var cache = {};

        function fill(fields) {
            ['name', 'age', 'gender', 'phone', 'email'].forEach(function (key) {
                var el = document.getElementById('patient_' + key);
                if (el && fields[key] !== undefined && fields[key] !== null && fields[key] !== '') {
                    el.value = fields[key];
                }
            });
        }

        if (select) {
            // Capture the current option's payload before any change.
            Array.from(select.options).forEach(function (option) {
                if (option.dataset.name) {
                    cache[option.value] = {
                        name: option.dataset.name,
                        age: option.dataset.age,
                        gender: option.dataset.gender,
                        phone: option.dataset.phone,
                        email: option.dataset.email,
                    };
                }
            });

            if (select.value && cache[select.value]) {
                fill(cache[select.value]);
            }

            select.addEventListener('change', function () {
                if (cache[this.value]) {
                    fill(cache[this.value]);
                }
            });
        }

        var tbody = document.getElementById('items_tbody');
        var template = document.getElementById('item-row-template');
        var addBtn = document.getElementById('add_item_btn');

        function reIndex() {
            Array.from(tbody.querySelectorAll('tr.item-row')).forEach(function (row, i) {
                Array.from(row.querySelectorAll('input')).forEach(function (input) {
                    input.name = input.name.replace(/items\[\d*\]/ , 'items[' + i + ']');
                });
            });
        }

        if (addBtn && template) {
            addBtn.addEventListener('click', function () {
                var html = template.innerHTML.replace(/__i__/g, 'NEW');
                var wrap = document.createElement('tbody');
                wrap.innerHTML = html;
                var row = wrap.firstElementChild;
                tbody.appendChild(row);
                reIndex();
            });
        }

        tbody && tbody.addEventListener('click', function (event) {
            var btn = event.target.closest('.remove-item');
            if (!btn) return;
            var row = btn.closest('tr');
            // Keep at least one row on the form.
            if (tbody.querySelectorAll('tr.item-row').length <= 1) {
                Array.from(row.querySelectorAll('input')).forEach(function (input) { input.value = ''; });
                return;
            }
            if (confirm('Remove this medicine row?')) {
                row.remove();
                reIndex();
            }
        });
    })();
</script>