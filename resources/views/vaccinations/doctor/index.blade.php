@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Immunization</p>
        <h1 class="mc-title">Vacci<em>nations</em></h1>
        <p class="mc-sub">Track given doses and upcoming schedules.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('doctor.vaccinations.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add record</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-ecg"><span>Immunization register</span><span>{{ $vaccinations->total() }} records</span></div>

<div class="mc-bar">
    <form action="{{ route('doctor.vaccinations.index') }}" method="GET" class="mc-search flex-1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search vaccine, child, patient…" value="{{ request('search') }}" autocomplete="off">
    </form>
</div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Vaccine</th>
                    <th>Dose</th>
                    <th>Status</th>
                    <th>Next due</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($vaccinations as $key => $vaccination)
                <tr>
                    <td class="mc-idx">{{ $vaccinations->firstItem() + $key }}</td>
                    <td><b>{{ $vaccination->subject_name }}</b></td>
                    <td>{{ $vaccination->vaccine_name }}</td>
                    <td class="mc-num">{{ $vaccination->dose_number }}</td>
                    <td>
                        @if($vaccination->status == 1)<span class="mc-pill p-confirmed">Completed</span>
                        @elseif($vaccination->status == 2)<span class="mc-pill p-cancelled">Missed</span>
                        @elseif($vaccination->is_overdue)<span class="mc-pill p-pending">Overdue</span>
                        @else<span class="mc-pill p-progress">Scheduled</span>@endif
                    </td>
                    <td class="mc-num">{{ $vaccination->next_due_date?->format('Y-m-d') ?? '—' }}</td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('doctor.vaccinations.show', $vaccination->id) }}" class="mc-btn sm">View</a>
                            <a href="{{ route('doctor.vaccinations.edit', $vaccination->id) }}" class="mc-btn sm dark">Edit</a>
                            <form action="{{ route('doctor.vaccinations.destroy', $vaccination->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost" onclick="return confirm('Delete this vaccination record?')">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"><div class="mc-empty"><b>No records</b>No vaccination records found.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($vaccinations->hasPages())
        <div class="px-4.5 py-4">{{ $vaccinations->links() }}</div>
    @endif
</div>

@endsection
