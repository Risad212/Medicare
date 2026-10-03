@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Access control</p>
        <h1 class="mc-title">Staff <em>&amp; users</em></h1>
        <p class="mc-sub">Hospital staff only — patients live under the Patients module.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<div class="mc-bar">
    <form action="{{ route('admin.users.index') }}" method="GET" class="mc-search flex-1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search name or email…" value="{{ request('search') }}" autocomplete="off">
    </form>
</div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Staff role</th>
                    <th>Joined</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($users as $key => $user)
                <tr>
                    <td class="mc-idx">{{ $users->firstItem() + $key }}</td>
                    <td>
                        <div class="mc-who">
                            <span class="mc-av t">{{ strtoupper(mb_substr($user->name ?? '?', 0, 1)) }}</span>
                            <span><b>{{ $user->name }}</b><span class="mc-sub2 block">{{ $user->email }}</span></span>
                        </div>
                    </td>
                    <td>
                        @if($user->role === 'admin')<span class="mc-pill p-completed"><i></i>admin</span>
                        @elseif($user->role === 'doctor')<span class="mc-pill p-info"><i></i>doctor</span>
                        @elseif($user->role === 'patient')<span class="mc-pill p-cancelled"><i></i>patient</span>
                        @else<span class="mc-pill p-pending"><i></i>{{ $user->role }}</span>@endif
                    </td>
                    <td class="mc-num">{{ $user->created_at?->format('M j, Y') ?? '—' }}</td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="mc-btn sm"><i class="bi bi-pencil"></i> Access</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-6 text-center text-mut">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</span>
        {{ $users->links() }}
    </div>
</div>

@endsection
