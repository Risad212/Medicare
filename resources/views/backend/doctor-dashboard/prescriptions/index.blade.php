@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Prescription</p>
        <h1 class="mc-title">My <em>prescriptions</em></h1>
        <p class="mc-sub">Prescriptions written for your patients.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('doctor.prescriptions.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> New Prescription</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Diagnosis</th>
                    <th>Medicines</th>
                    <th>Follow-up</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($prescriptions as $prescription)
                <tr>
                    <td class="mc-idx">{{ $prescription->id }}</td>
                    <td class="mc-num">{{ $prescription->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="mc-who">
                            <span>
                                <b>{{ $prescription->patient_name }}</b>
                                @if($prescription->phone)<span class="mc-sub2">{{ $prescription->phone }}</span>@endif
                                @if($prescription->appointment)
                                    <span class="mc-sub2">#Appt {{ $prescription->appointment_id }}</span>
                                @endif
                            </span>
                        </div>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($prescription->diagnosis, 45) }}</td>
                    <td>
                        <div class="flex flex-wrap gap-1">
                            @foreach($prescription->items->take(2) as $item)
                                <span class="mc-pill p-info">{{ $item->medicine_name }}</span>
                            @endforeach
                            @if($prescription->items->count() > 2)
                                <span class="mc-pill">+{{ $prescription->items->count() - 2 }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        @if($prescription->follow_up_date)
                            <span class="mc-pill p-pending">{{ $prescription->follow_up_date->format('d M Y') }}</span>
                        @else
                            <span class="text-mut">—</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('doctor.prescriptions.show', $prescription) }}" class="mc-btn sm"><i class="bi bi-eye"></i> View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"><div class="mc-empty"><b>No prescriptions yet</b>Write your first prescription to get started.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        {{ $prescriptions->links() }}
    </div>
</div>

@endsection