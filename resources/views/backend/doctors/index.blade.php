@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Staff</p>
        <h1 class="mc-title">Doc<em>tors</em></h1>
        <p class="mc-sub">Who is on the roster, where they sit, and whether they take bookings.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.doctors.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add doctor</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-ecg"><span>Live register</span><span>{{ $doctors->total() }} records</span></div>

<div class="mc-bar">
    <form action="{{ route('admin.doctors.index') }}" method="GET" class="mc-search flex-1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search doctor…" value="{{ request('search') }}" autocomplete="off">
    </form>
</div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Doctor</th>
                    <th>Department</th>
                    <th>Specialist</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($doctors as $doctor)
                @php
                    $initials = implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $doctor->name)), 0, 2));
                    $av = ['t', 'a', 'b', 'r', ''][$loop->index % 5];
                @endphp
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="mc-who">
                            @if($doctor->image)
                                <img class="mc-av" src="{{ asset('storage/'.$doctor->image) }}" alt="{{ $doctor->name }}">
                            @else
                                <span class="mc-av {{ $av }}">{{ strtoupper($initials) }}</span>
                            @endif
                            <span><b>{{ $doctor->name }}</b></span>
                        </div>
                    </td>
                    <td>{{ $doctor->department ?? 'General' }}</td>
                    <td>{{ $doctor->specialist ?? '–' }}</td>
                    <td class="mc-num">{{ $doctor->phone ?? '–' }}</td>
                    <td>
                        @if($doctor->status == 1)
                            <span class="mc-pill p-active"><i></i>Active</span>
                        @else
                            <span class="mc-pill p-inactive"><i></i>Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.doctors.availability', $doctor->id) }}" class="mc-btn sm"><i class="bi bi-calendar2-week"></i> Availability</a>
                            <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.doctors.destroy', $doctor->id) }}" method="POST"  onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"><div class="mc-empty"><b>Nothing on this chart</b>No doctors found. Add the first one to open bookings.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $doctors->firstItem() ?? 0 }}–{{ $doctors->lastItem() ?? 0 }} of {{ $doctors->total() }}</span>
        {{ $doctors->links() }}
    </div>
</div>

@endsection
