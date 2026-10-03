@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · In-patient</p>
        <h1 class="mc-title">Wa<em>rds</em></h1>
        <p class="mc-sub">Hospital wards and their bed capacity.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.beds.index') }}" class="mc-btn ghost">Bed dashboard</a>
        <a href="{{ route('admin.wards.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Add ward</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 mt-0 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ward</th>
                    <th>Rooms</th>
                    <th>Beds</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($wards as $key => $ward)
                <tr>
                    <td class="mc-idx">{{ $wards->firstItem() + $key }}</td>
                    <td><b>{{ $ward->name }}</b><div class="mc-sub2">{{ \Illuminate\Support\Str::limit($ward->description, 80) }}</div></td>
                    <td class="mc-num">{{ $ward->rooms_count }}</td>
                    <td class="mc-num">{{ $ward->beds_count }}</td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.wards.show', $ward->id) }}" class="mc-btn sm">View</a>
                            <a href="{{ route('admin.wards.edit', $ward->id) }}" class="mc-btn sm dark">Edit</a>
                            <form action="{{ route('admin.wards.destroy', $ward->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost" onclick="return confirm('Delete this ward?')">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5"><div class="mc-empty"><b>No wards</b>Create the first ward to start tracking beds.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($wards->hasPages())
        <div class="px-4.5 py-4">{{ $wards->links() }}</div>
    @endif
</div>

@endsection
