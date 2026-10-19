@extends('backend.layouts.app')

@push('styles')
<style>
  /* Flat cards on the dashboard — no Welly gold underline */
  .welly-stat::after { display: none; }
</style>
@endpush

@section('content')
@php
  $dashRole = auth()->user()->role ?? '';
  $dashAdmin = $dashRole === 'admin';
  $dashFrontDesk = in_array($dashRole, ['admin', 'receptionist'], true);
  $dashLab = in_array($dashRole, ['admin', 'lab-technician'], true) && \App\Support\Module::enabled('lab');
  $dashPharm = in_array($dashRole, ['admin', 'pharmacist'], true);

  $statusTotal = max(1, $pendingAppointments + $confirmedAppointments + $completedAppointments + $cancelledAppointments);
  $pctPending = round($pendingAppointments / $statusTotal * 100);
  $pctConfirmed = round($confirmedAppointments / $statusTotal * 100);
  $pctCompleted = round($completedAppointments / $statusTotal * 100);
  $pctCancelled = round($cancelledAppointments / $statusTotal * 100);

  $hasTrendData = collect($weekTrend)->sum() > 0;
@endphp

<div class="space-y-3">

  {{-- ===== Page header ===== --}}
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h1 class="welly-title">Dashboard</h1>
      <p class="welly-subtitle">{{ now()->format('l, F j, Y') }} · {{ $todayAppointments }} appointment{{ $todayAppointments === 1 ? '' : 's' }} today</p>
    </div>
    <div class="flex flex-wrap gap-2">
      @if($dashFrontDesk)
      <a href="{{ route('admin.appointments.create') }}" class="mc-btn"><i class="bi bi-plus-lg"></i> Book appointment</a>
      @endif
      @if($dashAdmin)
      <a href="{{ route('admin.doctors.create') }}" class="mc-btn ghost"><i class="bi bi-person-plus"></i> Add doctor</a>
      @endif
    </div>
  </div>

  {{-- ===== Everything in one 4-column grid ===== --}}
  <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

    {{-- ---- KPI cards ---- --}}
    @if($dashFrontDesk)
    <div class="welly-stat flex items-center gap-3 p-3.5">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wash text-[18px] text-teal"><i class="bi bi-calendar2-check"></i></span>
      <div class="min-w-0">
        <p class="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Today's Appts</p>
        <p class="m-0 text-[26px] font-extrabold leading-tight tabular-nums tracking-tight text-ink">{{ $todayAppointments }}</p>
      </div>
    </div>
    <div class="welly-stat flex items-center gap-3 p-3.5">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wash text-[18px] text-teal"><i class="bi bi-people"></i></span>
      <div class="min-w-0">
        <p class="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Total Patients</p>
        <p class="m-0 text-[26px] font-extrabold leading-tight tabular-nums tracking-tight text-ink">{{ number_format($totalPatients) }}</p>
      </div>
    </div>
    <div class="welly-stat flex items-center gap-3 p-3.5">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wash text-[18px] text-teal"><i class="bi bi-person-badge"></i></span>
      <div class="min-w-0">
        <p class="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Total Doctors</p>
        <p class="m-0 text-[26px] font-extrabold leading-tight tabular-nums tracking-tight text-ink">{{ $totalDoctors }}</p>
      </div>
    </div>
    @endif
    @if($dashAdmin)
    <div class="welly-stat flex items-center gap-3 p-3.5">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wash text-[18px] text-teal"><i class="bi bi-cash-stack"></i></span>
      <div class="min-w-0">
        <p class="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Earnings This Month</p>
        <p class="m-0 text-[26px] font-extrabold leading-tight tabular-nums tracking-tight text-ink">${{ number_format($hospitalEarning, 0) }}</p>
      </div>
    </div>
    @endif
    @if($dashLab && !$dashFrontDesk)
    <div class="welly-stat flex items-center gap-3 p-3.5">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wash text-[18px] text-teal"><i class="bi bi-clipboard2-pulse"></i></span>
      <div class="min-w-0">
        <p class="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Pending Lab Orders</p>
        <p class="m-0 text-[26px] font-extrabold leading-tight tabular-nums tracking-tight text-ink">{{ $labOrdersPending }}</p>
      </div>
    </div>
    @endif
    @if($dashPharm && !$dashFrontDesk)
    <div class="welly-stat flex items-center gap-3 p-3.5">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wash text-[18px] text-teal"><i class="bi bi-capsule"></i></span>
      <div class="min-w-0">
        <p class="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Recent Prescriptions</p>
        <p class="m-0 text-[26px] font-extrabold leading-tight tabular-nums tracking-tight text-ink">{{ $recentPrescriptions->count() }}</p>
      </div>
    </div>
    @endif

    {{-- ---- Appointment status: stacked bar + legend ---- --}}
    @if($dashFrontDesk)
    <div class="welly-stat p-4 sm:col-span-2">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="flex h-6 w-6 items-center justify-center rounded-md bg-wash text-[13px] text-teal"><i class="bi bi-activity"></i></span>
          <p class="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Appointment Status</p>
        </div>
        <a href="{{ route('admin.appointments.index') }}" class="text-[13px] font-bold text-teal-dk no-underline hover:underline">{{ number_format($totalAppointments) }} total →</a>
      </div>
      <div class="mt-3 flex h-2.5 w-full overflow-hidden rounded-full bg-line-2">
        @if($totalAppointments > 0)
          @if($pendingAppointments > 0)<i class="block bg-amber-dot" style="width:{{ $pctPending }}%"></i>@endif
          @if($confirmedAppointments > 0)<i class="block bg-teal" style="width:{{ $pctConfirmed }}%"></i>@endif
          @if($completedAppointments > 0)<i class="block bg-bright" style="width:{{ $pctCompleted }}%"></i>@endif
          @if($cancelledAppointments > 0)<i class="block bg-faint" style="width:{{ $pctCancelled }}%"></i>@endif
        @endif
      </div>
      <div class="mt-3 grid grid-cols-4 gap-2">
        <div>
          <div class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-amber-dot"></i><span class="text-[13px] text-mut">Pending</span></div>
          <p class="m-0 mt-1 text-[20px] font-extrabold tabular-nums text-ink">{{ $pendingAppointments }}<span class="ml-1 align-middle text-[12px] font-bold text-faint">{{ $pctPending }}%</span></p>
        </div>
        <div>
          <div class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-teal"></i><span class="text-[13px] text-mut">Confirmed</span></div>
          <p class="m-0 mt-1 text-[20px] font-extrabold tabular-nums text-ink">{{ $confirmedAppointments }}<span class="ml-1 align-middle text-[12px] font-bold text-faint">{{ $pctConfirmed }}%</span></p>
        </div>
        <div>
          <div class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-bright"></i><span class="text-[13px] text-mut">Completed</span></div>
          <p class="m-0 mt-1 text-[20px] font-extrabold tabular-nums text-ink">{{ $completedAppointments }}<span class="ml-1 align-middle text-[12px] font-bold text-faint">{{ $pctCompleted }}%</span></p>
        </div>
        <div>
          <div class="flex items-center gap-1.5"><i class="h-2 w-2 rounded-full bg-faint"></i><span class="text-[13px] text-mut">Cancelled</span></div>
          <p class="m-0 mt-1 text-[20px] font-extrabold tabular-nums text-ink">{{ $cancelledAppointments }}<span class="ml-1 align-middle text-[12px] font-bold text-faint">{{ $pctCancelled }}%</span></p>
        </div>
      </div>
    </div>
    @endif

    {{-- ---- Revenue ---- --}}
    @if($dashAdmin)
    <div class="welly-stat p-4 sm:col-span-2">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="flex h-6 w-6 items-center justify-center rounded-md bg-wash text-[13px] text-teal"><i class="bi bi-wallet2"></i></span>
          <p class="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Revenue Overview</p>
        </div>
        <a href="{{ route('admin.invoices.index') }}" class="text-[13px] font-bold text-teal-dk no-underline hover:underline">Invoices →</a>
      </div>
      <div class="mt-1 divide-y divide-line-2">
        <div class="flex items-center justify-between py-1.5">
          <span class="text-[13.5px] text-mut">Collected this month</span>
          <b class="text-[15px] font-extrabold tabular-nums text-ink">${{ number_format($revenueThisMonth, 2) }}</b>
        </div>
        <div class="flex items-center justify-between py-1.5">
          <span class="text-[13.5px] text-mut">Collected all time</span>
          <b class="text-[15px] font-extrabold tabular-nums text-ink">${{ number_format($revenueAllTime, 2) }}</b>
        </div>
        <div class="flex items-center justify-between py-1.5">
          <span class="text-[13.5px] text-mut">Outstanding balance</span>
          <b class="text-[15px] font-extrabold tabular-nums text-red">${{ number_format($invoiceOutstanding, 2) }}</b>
        </div>
        <div class="flex items-center justify-between py-1.5">
          <span class="text-[13.5px] text-mut">Unpaid invoices</span>
          <b class="text-[15px] font-extrabold tabular-nums text-ink">{{ number_format($invoicesPendingCount) }}</b>
        </div>
      </div>
    </div>
    @endif

    {{-- ---- Lab summary (single-row strip) ---- --}}
    @if($dashLab)
    <div @class(['welly-stat', 'col-span-2 p-3.5', 'sm:col-span-2' => $dashPharm, 'sm:col-span-4' => !$dashPharm])>
      <div class="flex items-center gap-2.5">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wash text-[18px] text-teal"><i class="bi bi-clipboard2-pulse"></i></span>
        <span class="shrink-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Lab Orders</span>
        <span class="mx-1 hidden h-4 w-px bg-line sm:block"></span>
        <p class="m-0 min-w-0 truncate text-[13.5px] text-mut">
          <b class="text-[18px] font-extrabold tabular-nums text-ink">{{ $labOrdersPending }}</b> pending
          <span class="mx-1 text-faint">·</span>
          <b class="text-[15px] font-bold tabular-nums text-ink-2">{{ number_format($labOrdersThisMonth) }}</b> this month
        </p>
        <a href="{{ route('admin.lab-orders.index') }}" class="ml-auto shrink-0 text-[11px] font-bold text-teal-dk no-underline hover:underline">All →</a>
      </div>
    </div>
    @endif

    {{-- ---- Pharmacy summary (single-row strip) ---- --}}
    @if($dashPharm)
    <div @class(['welly-stat', 'col-span-2 p-3.5', 'sm:col-span-2' => $dashLab, 'sm:col-span-4' => !$dashLab])>
      <div class="flex items-center gap-2.5">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-wash text-[18px] text-teal"><i class="bi bi-capsule"></i></span>
        <span class="shrink-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Pharmacy</span>
        <span class="mx-1 hidden h-4 w-px bg-line sm:block"></span>
        <p class="m-0 min-w-0 truncate text-[13.5px] text-mut">
          <b class="text-[18px] font-extrabold tabular-nums text-ink">{{ $recentPrescriptions->count() }}</b> recent Rx
          <span class="mx-1 text-faint">·</span>
          latest across doctors
        </p>
        <a href="{{ route('admin.prescriptions.index') }}" class="ml-auto shrink-0 text-[11px] font-bold text-teal-dk no-underline hover:underline">All →</a>
      </div>
    </div>
    @endif

    {{-- ---- Today's appointments (full width) ---- --}}
    @if($dashFrontDesk)
    <div class="welly-stat col-span-2 p-4 sm:col-span-2">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="flex h-6 w-6 items-center justify-center rounded-md bg-wash text-[13px] text-teal"><i class="bi bi-calendar2-week"></i></span>
          <p class="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Today's Appointments</p>
        </div>
        <a href="{{ route('admin.appointments.index') }}" class="text-[13px] font-bold text-teal-dk no-underline hover:underline">View all →</a>
      </div>
      <div class="mt-1.5 divide-y divide-line-2">
        @forelse($recentAppointments->take(5) as $a)
        <div class="flex items-center gap-3 py-2">
          <span class="mc-av">{{ $a->patient_name ? strtoupper(mb_substr($a->patient_name, 0, 1)) : '?' }}</span>
          <div class="min-w-0 flex-1">
            <p class="m-0 truncate text-[14.5px] font-bold text-ink">{{ $a->patient_name }}</p>
            <p class="m-0 truncate text-[13px] text-mut">{{ $a->doctor->name ?? '—' }} · {{ $a->doctor->department ?? 'General' }}</p>
          </div>
          <span class="hidden w-20 shrink-0 text-right text-[13.5px] font-semibold tabular-nums text-ink-2 sm:block">{{ $a->timeSlot->time ?? '—' }}</span>
          @if($a->status == 0)<span class="mc-pill p-pending !px-3 !py-1 !text-[12px]"><i></i>Pending</span>
          @elseif($a->status == 1)<span class="mc-pill p-confirmed !px-3 !py-1 !text-[12px]"><i></i>Confirmed</span>
          @elseif($a->status == 2)<span class="mc-pill p-completed !px-3 !py-1 !text-[12px]"><i></i>Completed</span>
          @else<span class="mc-pill p-cancelled !px-3 !py-1 !text-[12px]"><i></i>Cancelled</span>@endif
        </div>
        @empty
          <div class="flex flex-col items-center justify-center gap-1 py-4">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-line-2 text-[16px] text-faint"><i class="bi bi-calendar2-x"></i></span>
            <p class="m-0 mt-1 text-[13.5px] font-bold text-ink-2">No appointments today</p>
            <p class="m-0 text-[12.5px] text-mut">New bookings will show up here as they come in.</p>
          </div>
        @endforelse
      </div>
    </div>
    @endif

    {{-- ---- Bottom: 7-day trend chart (full width) ---- --}}
    <div class="welly-stat col-span-2 p-4 sm:col-span-2">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="flex h-6 w-6 items-center justify-center rounded-md bg-wash text-[13px] text-teal"><i class="bi bi-graph-up-arrow"></i></span>
          <p class="m-0 text-[11px] font-bold uppercase tracking-[0.08em] text-mut">Appointment Trend · Last 7 Days</p>
        </div>
        <span class="rounded-full bg-line-2 px-2.5 py-0.5 text-[12px] font-bold tabular-nums text-ink-2">
          @if($hasTrendData)
            {{ collect($weekTrend)->sum() }} total
          @else
            No activity
          @endif
        </span>
      </div>
      <div class="relative mt-2 {{ $hasTrendData ? 'h-[170px]' : 'h-[104px]' }}">
        @if($hasTrendData)
          <canvas id="trendChart"></canvas>
        @else
          <div class="absolute inset-0 flex flex-col items-center justify-center gap-1">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-line-2 text-[16px] text-faint"><i class="bi bi-bar-chart-line"></i></span>
            <p class="m-0 mt-1 text-[13.5px] font-bold text-ink-2">No activity yet</p>
            <p class="m-0 text-[12.5px] text-mut">The trend appears once appointments start coming in.</p>
          </div>
        @endif
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  var ctx = document.getElementById('trendChart');
  if (!ctx) return;

  var values = @json($weekTrend);
  var labels = [];
  for (var i = values.length - 1; i >= 0; i--) {
    var d = new Date();
    d.setDate(d.getDate() - i);
    labels.unshift(d.toLocaleDateString(undefined, { weekday: 'short' }));
  }
  var peak = Math.max.apply(null, values.concat([1]));

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        data: values,
        borderColor: '#0b8f74',
        backgroundColor: function (context) {
          var chart = context.chart;
          var area = chart.chartArea;
          if (!area) return 'rgba(11,143,116,0.08)';
          var g = chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
          g.addColorStop(0, 'rgba(11,143,116,0.18)');
          g.addColorStop(1, 'rgba(11,143,116,0)');
          return g;
        },
        borderWidth: 2,
        fill: true,
        tension: 0.35,
        pointRadius: 3,
        pointHoverRadius: 5,
        pointBackgroundColor: '#ffffff',
        pointBorderColor: '#0b8f74',
        pointBorderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#16202b',
          titleColor: '#a1a1aa',
          bodyColor: '#ffffff',
          padding: 10,
          cornerRadius: 8,
          displayColors: false,
          titleFont: { size: 11, weight: '600' },
          bodyFont: { size: 13, weight: '700' },
          callbacks: {
            label: function (c) {
              return c.parsed.y + ' appointment' + (c.parsed.y === 1 ? '' : 's');
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          border: { display: false },
          ticks: { color: '#a1a1aa', font: { size: 11, weight: '600' }, maxRotation: 0 }
        },
        y: {
          beginAtZero: true,
          suggestedMax: Math.ceil(peak * 1.3),
          grid: { color: '#f1f2f6' },
          border: { display: false, dash: [4, 4] },
          ticks: { color: '#a1a1aa', font: { size: 11 }, precision: 0, stepSize: 1, padding: 6 },
          afterBuildTicks: function (axis) {
            axis.ticks = axis.ticks.filter(function (t) { return t.value % 1 === 0; });
          }
        }
      },
      interaction: { intersect: false, mode: 'index' },
      layout: { padding: { top: 6, right: 6, bottom: 0, left: 0 } }
    }
  });
})();
</script>
@endsection