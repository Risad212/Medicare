@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Access control</p>
        <h1 class="mc-title">Roles <em>&amp; permissions</em></h1>
        <p class="mc-sub">Grant a module once to a role — every staffer holding it inherits access instantly.</p>
    </div>
    <div class="mc-head-acts">
        <a href="{{ route('admin.roles.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> New role</a>
        <a href="{{ route('admin.users.index') }}" class="mc-btn ghost"><i class="bi bi-people"></i> Staff &amp; users</a>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
@endif

<div class="mc-bar">
    <form action="{{ route('admin.roles.index') }}" method="GET" class="mc-search flex-1">
        <i class="bi bi-search text-faint"></i>
        <input type="text" name="search" placeholder="Search roles…" value="{{ request('search') }}" autocomplete="off">
    </form>
</div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Role</th>
                    <th>Slug</th>
                    <th>Staff</th>
                    <th>Permissions</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($roles as $key => $role)
                <tr>
                    <td class="mc-idx">{{ $roles->firstItem() + $key }}</td>
                    <td>
                        <div class="mc-who">
                            <span class="mc-av t">{{ strtoupper(mb_substr($role->name, 0, 1)) }}</span>
                            <span><b>{{ $role->name }}</b><span class="mc-sub2 block">{{ $role->description ?? '—' }}</span></span>
                        </div>
                    </td>
                    <td class="mc-num">{{ $role->slug }}</td>
                    <td class="mc-num">{{ $role->users_count }}</td>
                    <td class="mc-num">{{ $role->permissions_count }}</td>
                    <td>
                        <div class="mc-acts">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="mc-btn sm"><i class="bi bi-pencil"></i> Edit</a>
                            @if(! in_array($role->slug, ['admin', 'doctor', 'patient'], true))
                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this role? Staff holding it lose its grants.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-6 text-center text-mut">No roles found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $roles->firstItem() ?? 0 }}–{{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }}</span>
        {{ $roles->links() }}
    </div>
</div>

@endsection
