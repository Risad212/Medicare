<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Prescription #{{ $prescription->id }}</title>
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
        .doc-title { text-align: right; }
        .doc-title h2 {
            margin: 0;
            font-size: 26px;
            letter-spacing: 4px;
            color: #374151;
            text-transform: uppercase;
        }
        .meta {
            width: 100%;
            margin-bottom: 24px;
            border-collapse: collapse;
        }
        .meta td { padding: 4px 0; vertical-align: top; }
        .meta td:first-child { width: 140px; color: #6b7280; }
        .box {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px;
            margin-bottom: 12px;
        }
        .box h4 { margin: 0 0 6px; color: #0b8f74; }
        .box p { margin: 0; }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items th, .items td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .items th { background: #0b8f74; color: #fff; font-weight: 600; }
        .items tr:nth-child(even) td { background: #f8fafc; }
        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: flex-end;
        }
        .signature .inner {
            text-align: center;
            width: 260px;
            border-top: 1px solid #9ca3af;
            padding-top: 8px;
        }
        .footer {
            margin-top: 32px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            font-size: 11px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>{{ $setting->site_name ?? 'MediCare' }}</h1>
            @if($setting && $setting->address)
                <div class="subtitle">{{ $setting->address }}</div>
            @endif
            @if($setting && $setting->phone)
                <div class="subtitle">Phone: {{ $setting->phone }} @if($setting->email) | Email: {{ $setting->email }} @endif</div>
            @endif
        </div>
        <div class="doc-title">
            <h2>Prescription</h2>
            <div class="subtitle">No. #{{ $prescription->id }}</div>
        </div>
    </div>

    <table class="meta">
        <tr>
            <td>Patient</td>
            <td><strong>{{ $prescription->patient_name }}</strong></td>
        </tr>
        <tr>
            <td>Age / Gender</td>
            <td>
                {{ $prescription->age ? $prescription->age . ' yrs' : 'N/A' }}
                / {{ $prescription->gender_label }}
            </td>
        </tr>
        <tr>
            <td>Phone</td>
            <td>{{ $prescription->phone ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td>Date</td>
            <td>{{ $prescription->created_at->format('d F Y h:i A') }}</td>
        </tr>
        <tr>
            <td>Doctor</td>
            <td>Dr. {{ $prescription->doctor->name ?? 'N/A' }} @if($prescription->doctor->specialist) ({{ $prescription->doctor->specialist }}) @endif</td>
        </tr>
        @if($prescription->appointment)
            <tr>
                <td>Appointment</td>
                <td>#{{ $prescription->appointment->id }} - {{ $prescription->appointment->appointment_date }}</td>
            </tr>
        @endif
    </table>

    @if($prescription->symptoms)
        <div class="box">
            <h4>Symptoms</h4>
            <p>{{ $prescription->symptoms }}</p>
        </div>
    @endif

    <div class="box">
        <h4>Diagnosis</h4>
        <p>{{ $prescription->diagnosis }}</p>
    </div>

    @if($prescription->advice)
        <div class="box">
            <h4>Advice &amp; Notes</h4>
            <p>{{ $prescription->advice }}</p>
        </div>
    @endif

    <h3 style="color:#0b8f74; margin-bottom:8px;">Medicines</h3>
    <table class="items">
        <thead>
            <tr>
                <th style="width:26%">Medicine</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Duration</th>
                <th>Quantity</th>
                <th>Instructions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($prescription->items as $index => $item)
                <tr>
                    <td><strong>{{ $index + 1 }}. {{ $item->medicine_name }}</strong></td>
                    <td>{{ $item->dosage ?? '—' }}</td>
                    <td>{{ $item->frequency ?? '—' }}</td>
                    <td>{{ $item->duration ?? '—' }}</td>
                    <td>{{ $item->quantity ?? '—' }}</td>
                    <td>{{ $item->instructions ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No medicines recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($prescription->follow_up_date)
        <p style="color:#374151;">
            <strong>Follow-up:</strong>
            {{ $prescription->follow_up_date->format('d F Y') }}
        </p>
    @endif

    <div class="signature">
        <div class="inner">
            Dr. {{ $prescription->doctor->name ?? 'MediCare' }}
            @if($prescription->doctor->specialist)
                <br><span style="color:#6b7280;">{{ $prescription->doctor->specialist }}</span>
            @endif
        </div>
    </div>

    <div class="footer">
        This prescription is generated electronically by {{ $setting->site_name ?? config('app.name') }}.
    </div>
</body>
</html>