@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Blood Bank</p>
        <h1 class="mc-title">Blood <em>reports</em></h1>
        <p class="mc-sub">Donation, request and issue statistics — exportable to CSV.</p>
    </div>
</div>

<div class="mc-card p-3 mb-4">
    <form action="{{ route('admin.bloodbank.reports') }}" method="GET" class="flex flex-wrap items-end gap-2.5">
        <div>
            <label class="mb-1 block text-xs font-medium text-mut">From</label>
            <input type="date" name="from" class="rounded-lg border border-line bg-white px-3 py-2 text-[14px] text-ink outline-none focus:border-teal" value="{{ $from->format('Y-m-d') }}">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-mut">To</label>
            <input type="date" name="to" class="rounded-lg border border-line bg-white px-3 py-2 text-[14px] text-ink outline-none focus:border-teal" value="{{ $to->format('Y-m-d') }}">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-mut invisible" aria-hidden="true">Filter</label>
            <button class="mc-btn sm min-h-[39px]"><i class="bi bi-funnel"></i> Filter</button>
        </div>
        <div class="ml-auto flex gap-2.5">
            <a href="{{ route('admin.bloodbank.reports.donations', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="mc-btn sm ghost"><i class="bi bi-download"></i> Donations CSV</a>
            <a href="{{ route('admin.bloodbank.reports.requests', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="mc-btn sm ghost"><i class="bi bi-download"></i> Requests CSV</a>
            <a href="{{ route('admin.bloodbank.reports.issues', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="mc-btn sm ghost"><i class="bi bi-download"></i> Issues CSV</a>
        </div>
    </form>
</div>

<div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    <div class="mc-card stat p-4">
        <div class="ic teal"><i class="bi bi-droplet"></i></div>
        <div><b>{{ number_format($stats['period_donations']) }} ml</b><span>Donated</span></div>
    </div>
    <div class="mc-card stat p-4">
        <div class="ic rose"><i class="bi bi-eyedropper"></i></div>
        <div><b>{{ number_format($stats['period_issued']) }} ml</b><span>Issued</span></div>
    </div>
    <div class="mc-card stat p-4">
        <div class="ic amber"><i class="bi bi-clipboard-plus"></i></div>
        <div><b>{{ number_format($stats['period_requests_created']) }}</b><span>Requests created</span></div>
    </div>
    <div class="mc-card stat p-4">
        <div class="ic green"><i class="bi bi-check2-circle"></i></div>
        <div><b>{{ number_format($stats['period_requests_fulfilled']) }}</b><span>Requests fulfilled</span></div>
    </div>
</div>

<div class="mc-card mb-4">
    <div class="border-b border-line-2 p-3"><h5 class="mb-0"><i class="bi bi-bar-chart mr-1"></i> Last 6 months</h5></div>
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Donated (ml)</th>
                    <th>Issued (ml)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthly as $month)
                    <tr>
                        <td><b>{{ $month['label'] }}</b></td>
                        <td class="mc-num">{{ number_format($month['donations']) }}</td>
                        <td class="mc-num">{{ number_format($month['issued']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mc-card mb-4">
    <div class="border-b border-line-2 p-3"><h5 class="mb-0"><i class="bi bi-droplet-half mr-1"></i> Group-wise inventory</h5></div>
    <div class="overflow-x-auto">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>Group</th>
                    <th>Currently available (ml)</th>
                    <th>Donations</th>
                    <th>Issues</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupStats as $group)
                    <tr>
                        <td><span class="mc-av r sm">{{ $group->name }}</span></td>
                        <td class="mc-num">{{ number_format($group->available_quantity) }}</td>
                        <td class="mc-num">{{ number_format($group->donation_count) }}</td>
                        <td class="mc-num">{{ number_format($group->issued_count) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="mc-empty"><b>No data</b>No blood groups recorded.</div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="mc-card">
        <div class="border-b border-line-2 p-3"><h6 class="mb-0">Issued by group ({{ $from->format('Y-m-d') }} – {{ $to->format('Y-m-d') }})</h6></div>
        <div class="overflow-x-auto">
            <table class="mc-tbl">
                <thead><tr><th>Group</th><th>Issued (ml)</th></tr></thead>
                <tbody>
                    @forelse($issuedByGroup as $name => $qty)
                        <tr><td><span class="mc-av r sm">{{ $name }}</span></td><td class="mc-num">{{ number_format($qty) }}</td></tr>
                    @empty
                        <tr><td colspan="2"><div class="mc-empty"><b>Nothing issued</b>No blood issued in this period.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mc-card">
        <div class="border-b border-line-2 p-3"><h6 class="mb-0">Donation status breakdown (period)</h6></div>
        <div class="overflow-x-auto">
            <table class="mc-tbl">
                <thead><tr><th>Group</th><th>Status</th><th>Total (ml)</th></tr></thead>
                <tbody>
                    @forelse($donationStats as $row)
                        <tr>
                            <td>{{ $row->blood_group_id ? (optional($groupStats->firstWhere('id', $row->blood_group_id))->name ?? 'Unknown') : 'Unknown' }}</td>
                            <td><span class="mc-pill {{ $row->status === 'available' ? 'p-active' : '' }}">{{ ucfirst($row->status) }}</span></td>
                            <td class="mc-num">{{ number_format($row->total_quantity) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="mc-empty"><b>No donations</b>Nothing collected in this period.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection