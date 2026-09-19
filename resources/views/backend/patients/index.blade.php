@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Records</p>
        <h1 class="mc-title">Pati<em>ents</em></h1>
        <p class="mc-sub">Registered patients and how active their care is.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.patients.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add patient</a>
        <a href="{{ route('admin.exports.patients') }}" class="mc-btn ghost"><i class="bi bi-download"></i> Export CSV</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-ecg"><span>Live register</span><span>{{ $patients->total() }} records</span></div>

<div class="mc-bar">
    <form action="{{ route('admin.patients.index') }}" method="GET" class="mc-search flex-1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search patient…" value="{{ request('search') }}" autocomplete="off">
    </form>
</div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Phone</th>
                    <th>Gender</th>
                    <th>Date of birth</th>
                    <th>Registered</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($patients as $key => $patient)
                @php
                    $initials = implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $patient->name)), 0, 2));
                    $av = ['t', 'a', 'b', 'r', ''][$key % 5];
                @endphp
                <tr>
                    <td class="mc-idx">{{ $patients->firstItem() + $key }}</td>
                    <td>
                        <div class="mc-who">
                            <span class="mc-av {{ $av }}">{{ strtoupper($initials) }}</span>
                            <span><b>{{ $patient->name }}</b><span class="mc-sub2">{{ $patient->email }}</span></span>
                        </div>
                    </td>
                    <td class="mc-num">{{ $patient->phone ?? 'N/A' }}</td>
                    <td>{{ $patient->gender ? ucfirst($patient->gender) : 'N/A' }}</td>
                    <td class="mc-num">{{ $patient->date_of_birth ?? 'N/A' }}</td>
                    <td class="mc-num">{{ $patient->created_at->format('Y-m-d') }}</td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.patients.show', $patient->id) }}" class="mc-btn sm">View</a>
                            <a href="{{ route('admin.patients.edit', $patient->id) }}" class="mc-btn sm dark">Edit</a>
                            <form action="{{ route('admin.patients.destroy', $patient->id) }}" method="POST" >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost" onclick="return confirm('Are you sure you want to delete this patient?')">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7"><div class="mc-empty"><b>Nothing on this chart</b>No patients found.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $patients->firstItem() ?? 0 }}–{{ $patients->lastItem() ?? 0 }} of {{ $patients->total() }}</span>
        {{ $patients->links() }}
    </div>
</div>

@endsection
