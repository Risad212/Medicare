@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Structure</p>
        <h1 class="mc-title">Depart<em>ments</em></h1>
        <p class="mc-sub">Clinical units, their load, and availability.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.departments.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add department</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-ecg"><span>Live register</span><span>{{ $departments->count() }} records</span></div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Department</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($departments as $department)
                @php
                    $initials = implode('', array_slice(array_map(fn($w) => mb_substr($w, 0, 1), explode(' ', $department->name)), 0, 2));
                    $av = ['t', 'a', 'b', 'r', ''][$loop->index % 5];
                @endphp
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div class="mc-who">
                            <span class="mc-av b">{{ strtoupper($initials) }}</span>
                            <span><b>{{ $department->name }}</b></span>
                        </div>
                    </td>
                    <td>{{ Str::limit($department->description, 60) ?: '–' }}</td>
                    <td>
                        @if($department->status)
                            <span class="mc-pill p-active"><i></i>Active</span>
                        @else
                            <span class="mc-pill p-inactive"><i></i>Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.departments.edit', $department->id) }}" class="mc-btn sm dark"><i class="bi bi-pencil"></i> Edit</a>
                            <form action="{{ route('admin.departments.destroy', $department->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5"><div class="mc-empty"><b>Nothing on this chart</b>No departments found. Add the first unit to structure the clinic.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
