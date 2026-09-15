@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · System</p>
        <h1 class="mc-title">Activity <em>logs</em></h1>
        <p class="mc-sub">Audit trail of all actions performed in the admin panel.</p>
    </div>
</div>

<div class="mc-bar">
    <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="flex flex-1 items-center gap-2.5">
        <select name="action" class="rounded-lg border border-line bg-white px-3 py-2.5 text-[14px] text-ink outline-none focus:border-teal" onchange="this.form.submit()">
            <option value="">All actions</option>
            @foreach($availableActions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
            @endforeach
        </select>
        <div class="mc-search flex-1">
            <i class="bi bi-search text-faint"></i>
            <input type="text" name="search" placeholder="Search by user..." value="{{ request('search') }}" autocomplete="off">
        </div>
        @if(request('search') || request('action'))
            <a href="{{ route('admin.activity-logs.index') }}" class="mc-btn sm ghost">Clear</a>
        @endif
    </form>
</div>

<div class="mc-card">
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Record</th>
                    <th>Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="mc-idx">{{ $log->id }}</td>
                        <td class="mc-num text-[13px]">{{ $log->created_at->format('d M Y h:i A') }}</td>
                        <td>{{ $log->user->name ?? 'System' }}</td>
                        <td>
                            @if(str_ends_with($log->action, '.created'))
                                <span class="mc-pill p-active"><i></i>Created</span>
                            @elseif(str_ends_with($log->action, '.updated'))
                                <span class="mc-pill p-completed"><i></i>Updated</span>
                            @elseif(str_ends_with($log->action, '.deleted'))
                                <span class="mc-pill p-cancelled"><i></i>Deleted</span>
                            @else
                                <span class="mc-pill p-info"><i></i>{{ Str::of($log->action)->afterLast('.') }}</span>
                            @endif
                            <div class="text-xs text-mut">{{ $log->action }}</div>
                        </td>
                        <td class="text-[13px]">
                            {{ Str::title(str_replace('_', ' ', $log->model_type ? class_basename($log->model_type) : 'N/A')) }}
                            @if($log->model_id)
                                <span class="mc-pill p-low ml-1">#{{ $log->model_id }}</span>
                            @endif
                        </td>
                        <td class="text-[13px]">
                            @php
                                $keys = array_slice(array_keys($log->payload ?? []), 0, 4, true);
                            @endphp
                            @foreach($keys as $key)
                                <span class="mc-pill p-low mr-1">{{ $key }}</span>
                            @endforeach
                            @if(count($log->payload ?? []) > count($keys))
                                <span class="text-mut">+{{ count($log->payload) - count($keys) }} more</span>
                            @endif
                        </td>
                        <td class="mc-num text-[13px]">{{ $log->ip_address ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"><div class="mc-empty"><b>Nothing on this chart</b>No activity logged yet.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mc-pg">
        <span>Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }}</span>
        {{ $logs->links() }}
    </div>
</div>

@endsection
