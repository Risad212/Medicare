@extends('backend.layouts.app')

@section('content')
<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · System</p>
        <h1 class="mc-title">Data<em>base Backups</em></h1>
        <p class="mc-sub">Full database + uploaded files, taken daily at 02:00. Old backups are cleaned up automatically.</p>
    </div>
    <div class="mc-head-acts">
        <form action="{{ route('admin.backups.run') }}" method="POST" class="d-inline" onsubmit="return confirm('Run a full backup now? This may take a minute.');">
            @csrf
            <button type="submit" class="mc-btn"><i class="bi bi-database-add"></i> Run Backup Now</button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-card">
    <div class="border-b border-line px-4.5 py-3.5"><h5 class="mb-0">Backup history ({{ count($backups) }})</h5></div>
    <table class="mc-tbl">
        <thead>
            <tr>
                <th>File</th>
                <th>Size</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($backups as $backup)
                <tr>
                    <td><b>{{ $backup['name'] }}</b></td>
                    <td class="mc-num">{{ number_format($backup['size'] / 1048576, 2) }} MB</td>
                    <td>{{ \Carbon\Carbon::createFromTimestamp($backup['created_at'])->format('d M Y h:i A') }}</td>
                    <td><a href="{{ route('admin.backups.download', $backup['name']) }}" class="mc-btn sm"><i class="bi bi-download"></i> Download</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4"><div class="mc-empty"><b>No backups yet</b>Run your first backup with the button above — the daily schedule takes over from there.</div></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
