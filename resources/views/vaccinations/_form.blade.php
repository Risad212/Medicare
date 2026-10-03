@php($vaccination ??= null)
<form action="{{ $formAction }}" method="POST">
    @csrf
    @if(($method ?? null) === 'PUT')
        @method('PUT')
    @endif

    <div class="mc-card">
        <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Patient</h5></div>
        <div class="grid grid-cols-1 gap-3 px-4.5 py-4 md:grid-cols-2">
            <div class="mc-f">
                <label>Patient account (login user)</label>
                <select name="user_id" class="form-select">
                    <option value="">-- Select account --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id', $vaccination?->user_id) == $user->id)>{{ $user->name }} (#{{ $user->id }})</option>
                    @endforeach
                </select>
                <small class="mc-hint">Pick the account holder. For a child, pick the parent's account and fill "Child name".</small>
                @error('user_id')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Clinic register patient</label>
                <select name="patient_id" class="form-select">
                    <option value="">-- Select register entry --</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" @selected(old('patient_id', $vaccination?->patient_id) == $patient->id)>{{ $patient->name }} (#{{ $patient->id }})</option>
                    @endforeach
                </select>
                <small class="mc-hint">Use for children without a login account.</small>
                @error('patient_id')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f md:col-span-2">
                <label>Child name (optional)</label>
                <input type="text" name="child_name" class="form-control" value="{{ old('child_name', $vaccination?->child_name) }}" placeholder="e.g. Aarav Rahman">
            </div>
        </div>
    </div>

    <div class="mc-card">
        <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Vaccine details</h5></div>
        <div class="grid grid-cols-1 gap-3 px-4.5 py-4 md:grid-cols-2">
            <div class="mc-f">
                <label>Vaccine name <i class="req">*</i></label>
                <input type="text" name="vaccine_name" class="form-control" value="{{ old('vaccine_name', $vaccination?->vaccine_name) }}" placeholder="e.g. BCG, Pentavalent" required>
                @error('vaccine_name')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Dose number <i class="req">*</i></label>
                <input type="number" name="dose_number" class="form-control" min="1" max="10" value="{{ old('dose_number', $vaccination?->dose_number ?? 1) }}" required>
                @error('dose_number')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Status <i class="req">*</i></label>
                <select name="status" class="form-select" required>
                    <option value="0" @selected(old('status', $vaccination?->status ?? 0) == 0)>Scheduled</option>
                    <option value="1" @selected(old('status', $vaccination?->status) == 1)>Completed</option>
                    <option value="2" @selected(old('status', $vaccination?->status) == 2)>Missed</option>
                </select>
                @error('status')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Administered by</label>
                <input type="text" name="administered_by" class="form-control" value="{{ old('administered_by', $vaccination?->administered_by) }}" placeholder="Doctor / clinic name">
            </div>
            <div class="mc-f">
                <label>Date given</label>
                <input type="date" name="date_given" class="form-control" value="{{ old('date_given', $vaccination?->date_given?->format('Y-m-d')) }}">
                <small class="mc-hint">Required when status is Completed.</small>
                @error('date_given')<div class="text-sm text-red-t">{{ $message }}</div>@enderror
            </div>
            <div class="mc-f">
                <label>Next due date</label>
                <input type="date" name="next_due_date" class="form-control" value="{{ old('next_due_date', $vaccination?->next_due_date?->format('Y-m-d')) }}">
            </div>
            <div class="mc-f md:col-span-2">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Batch no, reactions, reminders...">{{ old('notes', $vaccination?->notes) }}</textarea>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-line-2 px-4.5 py-4">
            <button type="submit" class="mc-btn">{{ $vaccination ? 'Save changes' : 'Save record' }}</button>
        </div>
    </div>
</form>
