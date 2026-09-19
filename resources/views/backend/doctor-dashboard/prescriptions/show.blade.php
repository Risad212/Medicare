@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Prescription</p>
        <h1 class="mc-title">Prescription <em>#{{ $prescription->id }}</em></h1>
        <p class="mc-sub">Patient, diagnosis and the prescribed medicines.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('doctor.prescriptions.index') }}" class="mc-btn ghost">Back to list</a>
        <a href="{{ route('doctor.prescriptions.pdf', $prescription) }}" class="mc-btn ghost"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <a href="{{ route('doctor.prescriptions.edit', $prescription) }}" class="mc-btn"><i class="bi bi-pencil"></i> Edit</a>
        <form action="{{ route('doctor.prescriptions.destroy', $prescription) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Delete this prescription? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="mc-btn ghost danger-ghost"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-2">
    <div class="mc-card">
        <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Patient Information</h5></div>
        <table class="w-full text-[14px]">
            <tbody>
                <tr class="border-b border-line-2">
                    <th class="w-1/4 whitespace-nowrap px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Name</th>
                    <td class="px-4.5 py-2.5 font-semibold">{{ $prescription->patient_name }}</td>
                </tr>
                <tr class="border-b border-line-2">
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Age / Gender</th>
                    <td class="px-4.5 py-2.5">{{ $prescription->age ? $prescription->age . ' yrs' : 'N/A' }} / {{ $prescription->gender_label }}</td>
                </tr>
                <tr class="border-b border-line-2">
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Phone</th>
                    <td class="px-4.5 py-2.5">{{ $prescription->phone ?? 'N/A' }}</td>
                </tr>
                <tr class="border-b border-line-2">
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Email</th>
                    <td class="px-4.5 py-2.5">{{ $prescription->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Account</th>
                    <td class="px-4.5 py-2.5">{{ $prescription->patient->name ?? 'Walk-in (no account)' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mc-card">
        <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Visit Information</h5></div>
        <table class="w-full text-[14px]">
            <tbody>
                <tr class="border-b border-line-2">
                    <th class="w-1/4 whitespace-nowrap px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Prescribed</th>
                    <td class="px-4.5 py-2.5">{{ $prescription->created_at->format('d M Y h:i A') }}</td>
                </tr>
                <tr class="border-b border-line-2">
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Doctor</th>
                    <td class="px-4.5 py-2.5">Dr. {{ $prescription->doctor->name ?? 'N/A' }} ({{ $prescription->doctor->specialist ?? 'N/A' }})</td>
                </tr>
                <tr class="border-b border-line-2">
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Appointment</th>
                    <td class="px-4.5 py-2.5">{{ $prescription->appointment ? '#' . $prescription->appointment->id . ' - ' . $prescription->appointment->appointment_date : 'Walk-in' }}</td>
                </tr>
                <tr>
                    <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Follow-up</th>
                    <td class="px-4.5 py-2.5">
                        @if($prescription->follow_up_date)
                            <span class="mc-pill p-pending">{{ $prescription->follow_up_date->format('d M Y') }}</span>
                        @else
                            <span class="text-mut">Not scheduled</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="mc-card">
    <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Clinical Notes</h5></div>
    <div class="grid grid-cols-1 gap-3 px-4.5 py-4 md:grid-cols-2">
        <div>
            <h6>Symptoms</h6>
            <p class="text-mut">{{ $prescription->symptoms ?: 'Not recorded' }}</p>
        </div>
        <div>
            <h6>Diagnosis</h6>
            <p>{{ $prescription->diagnosis }}</p>
        </div>
        @if($prescription->advice)
            <div class="md:col-span-2">
                <h6>Advice / Notes</h6>
                <p class="text-mut">{{ $prescription->advice }}</p>
            </div>
        @endif
    </div>
</div>

<div class="mc-card">
    <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Prescribed Medicines ({{ $prescription->items->count() }})</h5></div>
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Dosage</th>
                    <th>Frequency</th>
                    <th>Duration</th>
                    <th>Quantity</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($prescription->items as $index => $item)
                    <tr>
                        <td><b>{{ $index + 1 }}. {{ $item->medicine_name }}</b></td>
                        <td>{{ $item->dosage ?? '—' }}</td>
                        <td>{{ $item->frequency ?? '—' }}</td>
                        <td>{{ $item->duration ?? '—' }}</td>
                        <td>{{ $item->quantity ?? '—' }}</td>
                        <td class="text-mut">{{ $item->instructions ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><div class="mc-empty"><b>No medicines</b>This prescription has no medicine rows.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection