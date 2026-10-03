<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Lab Report - Order #{{ $order->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #1f2937;
            margin: 0;
            padding: 32px;
        }
        .header {
            border-bottom: 3px solid #0b8f74;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #0b8f74;
        }
        .header .subtitle {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }
        .meta {
            width: 100%;
            margin-bottom: 24px;
            border-collapse: collapse;
        }
        .meta td { padding: 4px 0; vertical-align: top; }
        .meta td:first-child {
            width: 150px;
            color: #6b7280;
        }
        table.results {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .results th, .results td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        .results th {
            background: #0b8f74;
            color: #fff;
            font-weight: 600;
        }
        .results tr:nth-child(even) { background: #f3f4f6; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.completed { background: #d1fae5; color: #065f46; }
        .badge.in-progress { background: #cffafe; color: #155e75; }
        .badge.cancelled { background: #fee2e2; color: #991b1b; }
        .note {
            background: #f9fafb;
            border-left: 3px solid #0b8f74;
            padding: 12px;
            margin-bottom: 16px;
        }
        .footer {
            margin-top: 40px;
            color: #9ca3af;
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $setting->site_name ?? 'MediCare' }}</h1>
        <div class="subtitle">Diagnostic Laboratory &middot; Lab Report</div>
    </div>

    <table class="meta">
        <tr>
            <td>Report No</td>
            <td><strong>#{{ $order->id }}</strong></td>
            <td>Requested By</td>
            <td>Dr. {{ $order->doctor->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Patient</td>
            <td><strong>{{ $order->patient_name }}</strong></td>
            <td>Requested On</td>
            <td>{{ $order->created_at->format('d M Y') }}</td>
        </tr>
        <tr>
            <td>Phone</td>
            <td>{{ $order->phone ?? 'N/A' }}</td>
            <td>Status</td>
            <td>
                @if($order->status === 'completed')
                    <span class="badge completed">Completed</span>
                @elseif($order->status === 'in-progress')
                    <span class="badge in-progress">In Progress</span>
                @elseif($order->status === 'cancelled')
                    <span class="badge cancelled">Cancelled</span>
                @else
                    <span class="badge pending">Pending</span>
                @endif
            </td>
        </tr>
    </table>

    <h3 style="margin:0 0 12px;color:#374151;">Test Results</h3>
    <table class="results">
        <thead>
            <tr>
                <th style="width:35%;">Test</th>
                <th style="width:25%;">Normal Range</th>
                <th style="width:15%;">Unit</th>
                <th>Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->items as $item)
                <tr>
                    <td><strong>{{ $item->test->name ?? 'Removed test' }}</strong></td>
                    <td>{{ $item->test->normal_range ?? 'N/A' }}</td>
                    <td>{{ $item->test->unit ?? 'N/A' }}</td>
                    <td>{{ $item->result ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No tests on this order.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($order->note)
        <div class="note">
            <strong>Doctor's Note:</strong><br>
            {{ $order->note }}
        </div>
    @endif

    @if($order->reports->count())
        <h3 style="margin:0 0 12px;color:#374151;">Attached Reports</h3>
        @foreach($order->reports as $report)
            <div class="note">
                <strong>{{ $report->report_name }}</strong>
                @if($report->notes)
                    <br>{{ $report->notes }}
                @endif
                <div style="color:#9ca3af;font-size:11px;margin-top:4px;">
                    Uploaded {{ $report->created_at->format('d M Y') }}
                </div>
            </div>
        @endforeach
    @endif

    <div class="footer">
        Generated by {{ $setting->site_name ?? 'MediCare' }} on {{ \Carbon\Carbon::now()->format('d M Y g:i A') }} &middot; Document is informational and not a medical certificate.
    </div>

</body>
</html>