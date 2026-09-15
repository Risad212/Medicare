<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_no }}</title>
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
            display: flex;
            justify-content: space-between;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            color: #0b8f74;
        }
        .header .subtitle { color: #6b7280; font-size: 12px; margin-top: 4px; }
        .bill-title { text-align: right; }
        .bill-title h2 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 2px;
            color: #374151;
            text-transform: uppercase;
        }
        .meta {
            width: 100%;
            margin-bottom: 24px;
            border-collapse: collapse;
        }
        .meta td { padding: 4px 0; vertical-align: top; }
        .meta td:first-child { width: 130px; color: #6b7280; }
        .box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 12px;
        }
        .box h4 { margin: 0 0 6px; color: #374151; }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items th, .items td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }
        .items th { background: #0b8f74; color: #fff; font-weight: 600; }
        .items td.num, .items th.num { text-align: right; }
        .items tr:nth-child(even) { background: #f3f4f6; }
        table.totals {
            width: 320px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals td { padding: 5px 8px; }
        .totals td:last-child { text-align: right; }
        .totals tr.total-row td {
            border-top: 2px solid #0b8f74;
            font-size: 15px;
            font-weight: 700;
            color: #0b8f74;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.paid { background: #d1fae5; color: #065f46; }
        .badge.void { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 32px;
            color: #9ca3af;
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h1>{{ $setting->site_name ?? 'MediCare' }}</h1>
            <div class="subtitle">
                {{ $setting->address ?? '' }}
                @if(($setting->phone ?? ''))<br>Phone: {{ $setting->phone }}@endif
                @if(($setting->email ?? ''))<br>Email: {{ $setting->email }}@endif
            </div>
        </div>
        <div class="bill-title">
            <h2>Invoice</h2>
            <div class="subtitle">{{ $invoice->invoice_no }}</div>
        </div>
    </div>

    <table class="meta">
        <tr>
            <td>Invoice No</td>
            <td><strong>{{ $invoice->invoice_no }}</strong></td>
            <td>Date</td>
            <td>{{ $invoice->created_at->format('d M Y') }}</td>
        </tr>
        <tr>
            <td>Bill To</td>
            <td><strong>{{ $invoice->patient_name }}</strong></td>
            <td>Status</td>
            <td>
                @if($invoice->isPaid())
                    <span class="badge paid">Paid</span>
                @elseif($invoice->isVoid())
                    <span class="badge void">Void</span>
                @else
                    <span class="badge pending">Pending</span>
                @endif
            </td>
        </tr>
        @if($invoice->phone || $invoice->email)
            <tr>
                <td>Contact</td>
                <td>{{ $invoice->phone ?? '' }} {{ $invoice->email ? ($invoice->phone ? ' · ' : '').$invoice->email : '' }}</td>
                <td></td>
                <td></td>
            </tr>
        @endif
        @if($invoice->order?->doctor)
            <tr>
                <td>Requested By</td>
                <td>Dr. {{ $invoice->order->doctor->name }}</td>
                <td>Lab Order</td>
                <td>#{{ $invoice->order->id }}</td>
            </tr>
        @endif
        @if($invoice->paid_at)
            <tr>
                <td>Paid On</td>
                <td>{{ $invoice->paid_at->format('d M Y h:i A') }}</td>
                <td></td>
                <td></td>
            </tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:5%;">#</th>
                <th>Description</th>
                <th class="num" style="width:10%;">Qty</th>
                <th class="num" style="width:15%;">Unit Price</th>
                <th class="num" style="width:15%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="num">${{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td>${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td>${{ number_format($invoice->tax, 2) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td>-${{ number_format($invoice->discount, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>Total</td>
            <td>${{ number_format($invoice->total, 2) }}</td>
        </tr>
    </table>

    @if($invoice->notes)
        <div style="margin-top:24px;">
            <strong>Notes:</strong><br>
            {{ $invoice->notes }}
        </div>
    @endif

    <div class="footer">
        Thank you for choosing {{ $setting->site_name ?? 'MediCare' }}.
    </div>

</body>
</html>