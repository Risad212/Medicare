@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Medical Records</p>
        <h1 class="mc-title">All <em>prescriptions</em></h1>
        <p class="mc-sub">Prescriptions written across all doctors.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-bar">
    <form action="{{ route('admin.prescriptions.index') }}" method="GET" class="mc-search">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by patient, phone or doctor..." value="{{ request('search') }}">
        <button type="submit" class="mc-btn sm"><i class="bi bi-search"></i> Search</button>
        @if(request('search'))
            <a href="{{ route('admin.prescriptions.index') }}" class="mc-btn sm ghost">Clear</a>
        @endif
    </form>
</div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Diagnosis</th>
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
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="mc-who">
                            <span>
                                <b>Dr. {{ $prescription->doctor->name ?? 'N/A' }}</b>
                                @if($prescription->doctor?->specialist)<span class="mc-sub2">{{ $prescription->doctor->specialist }}</span>@endif
                            </span>
                        </div>
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($prescription->diagnosis, 45) }}</td>
                    <td>
                        @if($prescription->follow_up_date)
                            <span class="mc-pill p-pending">{{ $prescription->follow_up_date->format('d M Y') }}</span>
                        @else
                            <span class="text-mut">—</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('admin.prescriptions.show', $prescription) }}" class="mc-btn sm"><i class="bi bi-eye"></i> View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"><div class="mc-empty"><b>No prescriptions found</b>Try adjusting your search.</div></td>
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