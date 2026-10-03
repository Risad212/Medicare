@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Immunization</p>
        <h1 class="mc-title">Vaccination <em>#{{ $vaccination->id }}</em></h1>
        <p class="mc-sub">{{ $vaccination->vaccine_name }} · Dose {{ $vaccination->dose_number }}</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('doctor.vaccinations.index') }}" class="mc-btn ghost">Back to list</a>
        <a href="{{ route('doctor.vaccinations.edit', $vaccination) }}" class="mc-btn"><i class="bi bi-pencil"></i> Edit</a>
        <form action="{{ route('doctor.vaccinations.destroy', $vaccination) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this vaccination record?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="mc-btn ghost danger-ghost"><i class="bi bi-trash"></i> Delete</button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

@if($vaccination->is_overdue)
    <div class="mb-3 rounded-lg px-4 py-3 text-sm" role="alert" style="background:#fefce8;border:1px solid #fde68a;color:#92400e;">
        <strong>Overdue:</strong> this dose was due on {{ $vaccination->next_due_date->format('d M Y') }} and is still scheduled.
    </div>
@endif

<div class="mc-card">
    <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Record</h5></div>
    <table class="w-full text-[14px]">
        <tbody>
            <tr class="border-b border-line-2">
                <th class="w-1/4 whitespace-nowrap px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Subject</th>
                <td class="px-4.5 py-2.5 font-semibold">{{ $vaccination->subject_name }}</td>
            </tr>
            <tr class="border-b border-line-2">
                <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Status</th>
                <td class="px-4.5 py-2.5">{{ $vaccination->status_label }}</td>
            </tr>
            <tr class="border-b border-line-2">
                <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Date given</th>
                <td class="px-4.5 py-2.5">{{ $vaccination->date_given?->format('d M Y') ?? '—' }}</td>
            </tr>
            <tr class="border-b border-line-2">
                <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Next due</th>
                <td class="px-4.5 py-2.5">{{ $vaccination->next_due_date?->format('d M Y') ?? '—' }}</td>
            </tr>
            <tr class="border-b border-line-2">
                <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Administered by</th>
                <td class="px-4.5 py-2.5">{{ $vaccination->administered_by ?? '—' }}</td>
            </tr>
            <tr class="border-b border-line-2">
                <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Notes</th>
                <td class="px-4.5 py-2.5">{{ $vaccination->notes ?? '—' }}</td>
            </tr>
            <tr>
                <th class="px-4.5 py-2.5 text-left align-top text-[11px] font-bold uppercase tracking-wider text-mut">Recorded by</th>
                <td class="px-4.5 py-2.5">{{ $vaccination->creator?->name ?? '—' }}</td>
            </tr>
        </tbody>
    </table>
</div>

@endsection
