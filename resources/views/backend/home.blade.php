@extends('backend.layouts.app')

@section('content')
@php
  use Illuminate\Support\Carbon;
  $dashRole = auth()->user()->role ?? '';
  $dashAdmin = $dashRole === 'admin';
  $dashFrontDesk = in_array($dashRole, ['admin', 'receptionist'], true);
  $dashLab = in_array($dashRole, ['admin', 'lab-technician'], true);
  $dashPharm = in_array($dashRole, ['admin', 'pharmacist'], true);
  $statusTotal = max(1, $pendingAppointments + $confirmedAppointments + $completedAppointments + $cancelledAppointments);
  $pctNew = round($pendingAppointments / $statusTotal * 100);
  $pctRec = round($completedAppointments / $statusTotal * 100);
  $pctTreat = round($confirmedAppointments / $statusTotal * 100);
  $maxAppt = max(1, max($monthlyAppointments));
  $maxLab = max(1, max($monthlyLabOrders));
  $maxDocLoad = max(1, $doctorLoad->max('appointment_count') ?? 0);

  $calStart = $calMonth->copy()->startOfWeek(Carbon::SUNDAY);
  $calCells = collect(range(0, 41))->map(fn ($i) => $calStart->copy()->addDays($i));
  $todayStr = now()->toDateString();
  $calPrev = $calMonth->copy()->subMonth()->format('Y-m');
  $calNext = $calMonth->copy()->addMonth()->format('Y-m');

  $pctNew = $rangeStats['weekly']['new'];
  $pctRec = $rangeStats['weekly']['recovered'];
  $pctTreat = $rangeStats['weekly']['treating'];
  $donut = "conic-gradient(from -90deg, #c2a15a 0 {$pctNew}%, #0b8f74 {$pctNew}% " . ($pctNew + $pctRec) . "%, #2f353f " . ($pctNew + $pctRec) . "% 100%)";
@endphp

<div class="space-y-4">

  {{-- ===== Welly page header ===== --}}
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <h1 class="welly-title">Dashboard</h1>
      <p class="welly-subtitle">Hospital Admin Dashboard Template · {{ $todayAppointments }} appointments today</p>
    </div>
    <div class="flex flex-wrap gap-2.5 pt-1">
      @if($dashFrontDesk)
      <a href="{{ route('admin.appointments.create') }}" class="mc-btn sm"><i class="bi bi-plus-lg"></i> Book appointment</a>
      @endif
      @if($dashAdmin)
      <a href="{{ route('admin.doctors.create') }}" class="welly-outline-btn"><i class="bi bi-person-plus"></i> Add doctor</a>
      @endif
    </div>
  </div>

  {{-- ===== 4 stat cards (gold baseline like screenshot) ===== --}}
  <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @if($dashFrontDesk)
    <div class="welly-stat">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="welly-stat-num">{{ $todayAppointments }}</p>
          <p class="welly-stat-label">Appointment</p>
        </div>
        <span class="welly-stat-icon"><i class="bi bi-calendar-date"></i></span>
      </div>
    </div>
    <div class="welly-stat">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="welly-stat-num">{{ number_format($totalPatients) }}</p>
          <p class="welly-stat-label">Total Patient</p>
        </div>
        <span class="welly-stat-icon"><i class="bi bi-heart"></i></span>
      </div>
    </div>
    <div class="welly-stat">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="welly-stat-num">{{ $totalDoctors }}</p>
          <p class="welly-stat-label">Total Doctor</p>
        </div>
        <span class="welly-stat-icon"><i class="bi bi-person-badge"></i></span>
      </div>
    </div>
    @endif
    @if($dashAdmin)
    <div class="welly-stat">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="welly-stat-num">${{ number_format($hospitalEarning, 0) }}</p>
          <p class="welly-stat-label">Hospital Earning</p>
        </div>
        <span class="welly-stat-icon"><i class="bi bi-coin"></i></span>
      </div>
    </div>
    @endif
    @if($dashLab)
    <div class="welly-stat">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="welly-stat-num">{{ $labOrdersPending }}</p>
          <p class="welly-stat-label">Pending Lab Orders</p>
        </div>
        <span class="welly-stat-icon"><i class="bi bi-clipboard2-pulse"></i></span>
      </div>
    </div>
    <div class="welly-stat">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="welly-stat-num">{{ $labOrdersThisMonth }}</p>
          <p class="welly-stat-label">Lab Orders This Month</p>
        </div>
        <span class="welly-stat-icon"><i class="bi bi-graph-up"></i></span>
      </div>
    </div>
    @endif
  </section>

  {{-- ===== Patient Percentage + Appointment Schedule ===== --}}
  @if($dashAdmin || $dashFrontDesk)
  <div class="grid grid-cols-1 items-start gap-4 xl:grid-cols-2">

    {{-- Patient Percentage --}}
    @if($dashAdmin)
    <section class="welly-card">
      <div class="welly-card-hd">
        <h2 class="welly-card-title">Patient Percentage</h2>
        <div class="welly-tabs" role="tablist">
          @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $key => $label)
            <button type="button" class="welly-tab {{ $key === 'weekly' ? 'on' : '' }}" data-welly-tab="{{ $key }}"
              data-total="{{ $rangeStats[$key]['total'] }}" data-new="{{ $rangeStats[$key]['new'] }}"
              data-recovered="{{ $rangeStats[$key]['recovered'] }}" data-treating="{{ $rangeStats[$key]['treating'] }}">{{ $label }}</button>
          @endforeach
        </div>
      </div>
      <div class="px-5 pb-5 pt-3">
        <div class="flex items-center justify-between gap-3 rounded-lg bg-line-2 px-4 py-3">
          <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-md bg-teal-dk text-[22px] text-white"><i class="bi bi-heart"></i></span>
            <div>
              <p class="m-0 text-[12px] text-mut">Total Patient</p>
              <p class="m-0 text-[19px] font-extrabold tabular-nums text-teal-dk">{{ number_format($totalPatients) }}</p>
            </div>
          </div>
          <div class="flex items-center">
            @foreach($topDoctors->take(5) as $doc)
              <span class="mc-av -ml-2 border-2 border-white {{ ['t','a','b','r',''][ (int) $doc->id % 5 ] }}" title="{{ $doc->name }}">{{ $doc->name ? strtoupper(mb_substr($doc->name, 0, 1)) : '?' }}</span>
            @endforeach
          </div>
        </div>

        <div class="flex justify-center py-5">
          <div id="welly-donut" class="relative h-[190px] w-[190px] rounded-full" style="background:{{ $donut }}">
            <div class="absolute inset-[26px] rounded-full bg-white"></div>
            <div class="absolute inset-[44px] rounded-full border-[10px] border-line-2 border-t-transparent"></div>
          </div>
        </div>

        <div>
          <div class="welly-legend"><span class="flex items-center gap-2.5"><i class="welly-bar bg-gold"></i><b id="welly-pct-new">{{ $pctNew }}%</b></span><span class="lbl">New Patient</span></div>
          <div class="welly-legend"><span class="flex items-center gap-2.5"><i class="welly-bar bg-teal"></i><b id="welly-pct-recovered">{{ $pctRec }}%</b></span><span class="lbl">Recovered</span></div>
          <div class="welly-legend"><span class="flex items-center gap-2.5"><i class="welly-bar bg-ink"></i><b id="welly-pct-treating">{{ $pctTreat }}%</b></span><span class="lbl">In Treatment</span></div>
        </div>
        <p class="mt-2 border-t border-line-2 pt-2 text-[12px] text-mut"><span id="welly-range-total">{{ $rangeStats['weekly']['total'] }} bookings in range</span> · {{ $totalAppointments }} total · {{ $pendingAppointments }} pending · {{ $cancelledAppointments }} cancelled</p>
      </div>
    </section>
    @endif

    {{-- Appointment Schedule --}}
    @if($dashFrontDesk)
    <section class="welly-card">
      <div class="welly-card-hd">
        <h2 class="welly-card-title">Appointment Schedule</h2>
        <a href="{{ route('admin.appointments.index') }}" class="welly-iconbtn !h-8 !w-8 !text-[16px]" aria-label="All appointments" title="All appointments"><i class="bi bi-three-dots-vertical"></i></a>
      </div>
      <div class="px-5 pb-5 pt-2">
        <div class="flex items-center justify-between py-2">
          <a href="{{ route('admin.home', ['cal' => $calPrev]) }}" class="welly-iconbtn !h-8 !w-8 !text-[15px]" aria-label="Previous month"><i class="bi bi-chevron-left"></i></a>
          <p class="m-0 text-[14px] font-extrabold text-ink">{{ $calMonth->format('F Y') }}
            @if(!$calMonth->isSameMonth(now()))
              <a href="{{ route('admin.home') }}" class="ml-1 text-[12px] font-bold text-teal-dk no-underline hover:underline">Today</a>
            @endif
          </p>
          <a href="{{ route('admin.home', ['cal' => $calNext]) }}" class="welly-iconbtn !h-8 !w-8 !text-[15px]" aria-label="Next month"><i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="welly-cal">
          @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $dow)
            <span class="dow">{{ $dow }}</span>
          @endforeach
          @foreach($calCells as $day)
            @php
              $ds = $day->toDateString();
              $cls = 'day';
              if ($day->month !== $calMonth->month) $cls .= ' muted';
              if ($ds === $todayStr) $cls .= ' today';
              $hasBooking = $calBookings->get($ds, 0) > 0;
            @endphp
            <span class="{{ $cls }} relative">{{ $day->day }}@if($hasBooking)<i class="absolute bottom-0.5 h-1 w-1 rounded-full {{ $ds === $todayStr ? 'bg-white' : 'bg-teal' }}"></i>@endif</span>
          @endforeach
        </div>

        <div class="mt-2">
          @forelse($upcomingSchedule as $dayLabel => $items)
            @foreach($items->take(2) as $a)
              <div class="welly-sched">
                <p class="welly-sched-day">{{ $dayLabel }}</p>
                <div class="welly-sched-meta"><i class="bi bi-clock"></i> {{ $a->timeSlot->time ?? '—' }}</div>
                <div class="flex items-center justify-between gap-2">
                  <span class="welly-sched-meta"><i class="bi bi-person"></i> {{ $a->doctor->name ?? '—' }} · {{ $a->patient_name }}</span>
                  <span class="flex items-center gap-2">
                    <form action="{{ route('admin.appointments.status', $a->id) }}" method="POST" class="m-0">
                      @csrf @method('PATCH')
                      <input type="hidden" name="status" value="1">
                      <button class="border-0 bg-transparent p-0 text-[16px] text-teal hover:text-teal-dk" title="Approve"><i class="bi bi-check-circle"></i></button>
                    </form>
                    <form action="{{ route('admin.appointments.status', $a->id) }}" method="POST" class="m-0" onsubmit="return confirm('Cancel this appointment?')">
                      @csrf @method('PATCH')
                      <input type="hidden" name="status" value="3">
                      <button class="border-0 bg-transparent p-0 text-[16px] text-red hover:text-red-t" title="Cancel"><i class="bi bi-x-circle"></i></button>
                    </form>
                  </span>
                </div>
              </div>
            @endforeach
          @empty
            <p class="py-4 text-center text-[13px] text-mut">No upcoming appointments scheduled.</p>
          @endforelse
        </div>
        <a href="{{ route('admin.appointments.index') }}" class="mt-1 block text-center text-[13px] font-bold text-teal-dk no-underline hover:underline">View all {{ $totalAppointments }} appointments →</a>
      </div>
    </section>
    @endif
  </div>
  @endif

  {{-- ===== Patient Overview ===== --}}
  @if($dashAdmin)
  <section class="welly-card">
    <div class="welly-card-hd">
      <div>
        <h2 class="welly-card-title">Patient Overview</h2>
        <p class="m-0 mt-0.5 text-[12px] text-mut">Monthly trends · Appointments vs lab orders · last 6 months</p>
      </div>
      <a href="{{ route('admin.lab-orders.index') }}" class="welly-iconbtn !h-8 !w-8 !text-[16px]" aria-label="Lab orders" title="Lab orders"><i class="bi bi-three-dots"></i></a>
    </div>
    <div class="flex items-end gap-2 px-5 pb-1 pt-3">
      @foreach($monthLabels as $i => $label)
        <div class="flex flex-1 flex-col items-center gap-1.5">
          <div class="flex items-end gap-1" style="height:110px">
            <i class="block w-3 rounded-sm bg-teal" style="height:{{ max(4, round($monthlyAppointments[$i] / $maxAppt * 108)) }}px" title="{{ $monthlyAppointments[$i] }} appointments"></i>
            <i class="block w-3 rounded-sm bg-gold" style="height:{{ max(4, round($monthlyLabOrders[$i] / $maxLab * 108)) }}px" title="{{ $monthlyLabOrders[$i] }} lab orders"></i>
          </div>
          <span class="text-[11px] text-mut">{{ $label }}</span>
        </div>
      @endforeach
    </div>
    <div class="flex gap-4 px-5 pb-4 pt-1 text-[12px] text-mut">
      <span><i class="mr-1.5 inline-block h-2.5 w-2.5 rounded-sm bg-teal align-[-1px]"></i>Appointments</span>
      <span><i class="mr-1.5 inline-block h-2.5 w-2.5 rounded-sm bg-gold align-[-1px]"></i>Lab orders</span>
      <span class="ml-auto">Revenue this month: <strong class="text-ink">${{ number_format($revenueThisMonth, 2) }}</strong> · Outstanding invoices: <strong class="text-ink">${{ number_format($invoiceOutstanding, 2) }}</strong></span>
    </div>
  </section>
  @endif

  {{-- ===== Revenue (lab + invoices, Welly cards) ===== --}}
  @if($dashAdmin || $dashFrontDesk)
  <div class="grid grid-cols-1 items-start gap-4 xl:grid-cols-2">
    @if($dashAdmin)
    <section class="welly-card">
      <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-5 py-3.5">
        <div>
          <h2 class="welly-card-title">Revenue</h2>
          <p class="m-0 mt-0.5 text-[12px] text-mut">Lab orders &amp; invoices</p>
        </div>
        <a href="{{ route('admin.lab-orders.index', ['status' => 'completed']) }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">View completed →</a>
      </div>
      <div class="flex flex-wrap gap-x-8 gap-y-3 px-5 py-4">
        <div>
          <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">This month</p>
          <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">${{ number_format($revenueThisMonth, 2) }}</p>
        </div>
        <div>
          <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">All time</p>
          <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">${{ number_format($revenueAllTime, 2) }}</p>
        </div>
        <div>
          <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Lab orders this month</p>
          <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">{{ $labOrdersThisMonth }}</p>
        </div>
        <div>
          <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Awaiting processing</p>
          <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">{{ $labOrdersPending }}</p>
        </div>
      </div>
      <div class="border-t border-line-2 px-5 pb-4 pt-3">
        <div class="flex items-center justify-between gap-2.5">
          <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Invoice revenue</p>
          <a href="{{ route('admin.invoices.index') }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">View invoices →</a>
        </div>
        <div class="flex flex-wrap gap-x-8 gap-y-3 pt-2">
          <div>
            <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Collected this month</p>
            <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">${{ number_format($invoicePaidThisMonth, 2) }}</p>
          </div>
          <div>
            <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Collected all time</p>
            <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">${{ number_format($invoicePaidAllTime, 2) }}</p>
          </div>
          <div>
            <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Outstanding</p>
            <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">${{ number_format($invoiceOutstanding, 2) }}</p>
          </div>
          <div>
            <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Unpaid invoices</p>
            <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">{{ $invoicesPendingCount }}</p>
          </div>
        </div>
      </div>
    </section>
    @endif

    @if($dashFrontDesk)
    <section class="welly-card">
      <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-5 py-3.5">
        <div>
          <h2 class="welly-card-title">Appointments by status</h2>
          <p class="m-0 mt-0.5 text-[12px] text-mut">{{ $totalAppointments }} total</p>
        </div>
      </div>
      <div class="px-5 py-4">
        @php $barW = fn ($n) => max(0, round($n / $statusTotal * 100)); @endphp
        <div class="grid items-center gap-2.5 text-[14px] md:grid-cols-[88px_1fr_30px]">
          <span class="text-ink-2">Pending</span>
          <div class="h-2 overflow-hidden rounded bg-grey-bg"><i class="block h-full rounded bg-amber-dot" style="width:{{ $barW($pendingAppointments) }}%"></i></div>
          <span class="text-right font-bold tabular-nums text-ink">{{ $pendingAppointments }}</span>
        </div>
        <div class="mt-2 grid items-center gap-2.5 text-[14px] md:grid-cols-[88px_1fr_30px]">
          <span class="text-ink-2">Confirmed</span>
          <div class="h-2 overflow-hidden rounded bg-grey-bg"><i class="block h-full rounded bg-teal" style="width:{{ $barW($confirmedAppointments) }}%"></i></div>
          <span class="text-right font-bold tabular-nums text-ink">{{ $confirmedAppointments }}</span>
        </div>
        <div class="mt-2 grid items-center gap-2.5 text-[14px] md:grid-cols-[88px_1fr_30px]">
          <span class="text-ink-2">Completed</span>
          <div class="h-2 overflow-hidden rounded bg-grey-bg"><i class="block h-full rounded bg-bright" style="width:{{ $barW($completedAppointments) }}%"></i></div>
          <span class="text-right font-bold tabular-nums text-ink">{{ $completedAppointments }}</span>
        </div>
        <div class="mt-2 grid items-center gap-2.5 text-[14px] md:grid-cols-[88px_1fr_30px]">
          <span class="text-ink-2">Cancelled</span>
          <div class="h-2 overflow-hidden rounded bg-grey-bg"><i class="block h-full rounded bg-faint" style="width:{{ $barW($cancelledAppointments) }}%"></i></div>
          <span class="text-right font-bold tabular-nums text-ink">{{ $cancelledAppointments }}</span>
        </div>
      </div>
      <p class="m-0 border-t border-line-2 px-5 py-2.5 text-[12px] text-mut">Book more from the front desk or review the pending queue.</p>
    </section>
    @endif
  </div>
  @endif

  {{-- ===== Today's register + side rails (kept from previous dashboard, Welly cards) ===== --}}
  @if($dashFrontDesk || $dashLab || $dashPharm)
  <div class="grid grid-cols-1 items-start gap-4 xl:grid-cols-[1fr_340px]">
    @if($dashFrontDesk)
    <section class="welly-card">
      <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-5 py-3.5">
        <div>
          <h2 class="welly-card-title">Today's appointments</h2>
          <p class="m-0 mt-0.5 text-[12px] text-mut">Pending first · today's queue across all doctors</p>
        </div>
        <a href="{{ route('admin.appointments.index') }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">View all {{ $totalAppointments }} →</a>
      </div>
      <div class="overflow-x-auto">
        <table class="mc-tbl w-full">
          <thead><tr><th>Patient</th><th>Doctor</th><th>Time</th><th>Status</th><th></th></tr></thead>
          <tbody>
            @forelse($recentAppointments as $a)
              <tr>
                <td>
                  <div class="mc-who">
                    <div class="mc-av">{{ $a->patient_name ? strtoupper(mb_substr($a->patient_name, 0, 1)) : '?' }}</div>
                    <div>
                      <b>{{ $a->patient_name }}</b>
                      <span class="mc-sub2">{{ $a->phone }} · {{ $a->age ? $a->age . 'y' : '—' }}</span>
                    </div>
                  </div>
                </td>
                <td>
                  <b class="block text-ink">{{ $a->doctor->name ?? '—' }}</b>
                  <span class="text-[12px] text-mut">{{ $a->doctor->department ?? 'General' }}</span>
                </td>
                <td>
                  {{ Carbon::parse($a->appointment_date)->format('M j') }}<br>
                  <span class="text-[12px] text-mut">{{ $a->timeSlot->time ?? '—' }}</span>
                </td>
                <td>
                  @if($a->status == 0)<span class="mc-pill p-pending"><i></i>Pending</span>
                  @elseif($a->status == 1)<span class="mc-pill p-confirmed"><i></i>Confirmed</span>
                  @elseif($a->status == 2)<span class="mc-pill p-completed"><i></i>Completed</span>
                  @else<span class="mc-pill p-cancelled"><i></i>Cancelled</span>@endif
                </td>
                <td><a class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline" href="{{ route('admin.appointments.edit', $a->id) }}">Open →</a></td>
              </tr>
            @empty
              <tr><td colspan="5" class="py-6 text-center text-mut">No appointments scheduled for today.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
    @endif

    <div class="flex flex-col gap-4">
      @if($dashFrontDesk)
      <section class="welly-card">
        <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-5 py-3.5">
          <div>
            <h2 class="welly-card-title">Doctors on duty</h2>
            <p class="m-0 mt-0.5 text-[12px] text-mut">{{ $activeDoctorsToday }} on duty today</p>
          </div>
          <a href="{{ route('admin.doctors.index') }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">All {{ $totalDoctors }} →</a>
        </div>
        <div class="px-5 pb-3 pt-1">
          @forelse($dutyDoctors as $doc)
            <div class="flex items-center gap-2.5 border-b border-line-2 py-2.5 last:border-b-0">
              <div class="mc-av">{{ $doc->name ? strtoupper(mb_substr($doc->name, 0, 1)) : '?' }}</div>
              <div class="min-w-0">
                <b class="block text-[14px] text-ink">{{ $doc->name }}</b>
                <span class="text-[12px] text-mut">{{ $doc->department ?? 'General' }} · {{ $doc->appointment_count }} today</span>
              </div>
              <span class="ml-auto shrink-0 text-[12px] font-bold {{ $doc->status ? 'text-teal-dk' : 'text-faint' }}">• {{ $doc->status ? 'Active' : 'Off duty' }}</span>
            </div>
          @empty
            <div class="py-3.5 text-[14px] text-mut">Nobody on duty today.</div>
          @endforelse
        </div>
      </section>

      <section class="welly-card">
        <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-5 py-3.5">
          <div>
            <h2 class="welly-card-title">Doctor load</h2>
            <p class="m-0 mt-0.5 text-[12px] text-mut">Active bookings per doctor</p>
          </div>
        </div>
        <div class="px-5 py-4">
          @forelse($doctorLoad as $doc)
            <div class="grid items-center gap-3 text-[14px] md:grid-cols-[1fr_52px] {{ !$loop->last ? 'mb-2' : '' }}">
              <div>
                <b class="block text-ink">{{ $doc->name }}</b>
                <div class="mt-1 h-2 overflow-hidden rounded bg-grey-bg"><i class="block h-full rounded bg-teal" style="width:{{ round($doc->appointment_count / $maxDocLoad * 100) }}%"></i></div>
              </div>
              <span class="text-right font-bold tabular-nums text-ink">{{ $doc->appointment_count }}</span>
            </div>
          @empty
            <div class="py-3 text-[14px] text-mut">No doctors found.</div>
          @endforelse
        </div>
      </section>
      @endif

      @if($dashAdmin)
      <section class="welly-card">
        <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-5 py-3.5">
          <div>
            <h2 class="welly-card-title">Pending comments</h2>
            <p class="m-0 mt-0.5 text-[12px] text-mut">Awaiting moderation</p>
          </div>
        </div>
        <div class="px-5 pb-2 pt-0.5">
          @forelse($recentComments as $c)
            <div class="border-b border-line-2 py-2.5 text-[14px] last:border-b-0">
              <q class="block font-medium text-ink" quotes="“”">{{ \Illuminate\Support\Str::limit($c->comment, 90) }}</q>
              <span class="text-[12px] text-mut">{{ $c->name }} on "{{ $c->blog->title ?? '—' }}"</span>
            </div>
          @empty
            <div class="py-3.5 text-[14px] text-mut">No pending comments. All clear.</div>
          @endforelse
        </div>
        <a class="mx-5 mb-5 block rounded-lg bg-ink py-2 text-center text-[14px] font-semibold text-white no-underline hover:bg-black" href="{{ route('admin.comments.index') }}">Moderate comments</a>
      </section>
      @endif
    </div>

    @if($dashLab)
    <section class="welly-card xl:col-span-2">
      <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-5 py-3.5">
        <div>
          <h2 class="welly-card-title">Lab queue</h2>
          <p class="m-0 mt-0.5 text-[12px] text-mut">Oldest unprocessed orders first</p>
        </div>
        <a href="{{ route('admin.lab-orders.index') }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">All orders →</a>
      </div>
      <div class="overflow-x-auto">
        <table class="mc-tbl w-full">
          <thead><tr><th>Order</th><th>Patient</th><th>Tests</th><th>Status</th><th></th></tr></thead>
          <tbody>
            @forelse($pendingLabOrders as $o)
              <tr>
                <td>
                  <b class="block text-ink">#{{ $o->id }}</b>
                  <span class="text-[12px] text-mut">{{ $o->created_at->format('M j') }} · {{ $o->doctor->name ?? '—' }}</span>
                </td>
                <td>
                  <b class="block text-ink">{{ $o->patient_name }}</b>
                  <span class="text-[12px] text-mut">{{ $o->phone ?? '—' }}</span>
                </td>
                <td><span class="text-[12px] text-mut">{{ $o->items->count() }} test(s)</span></td>
                <td>
                  @if($o->status === 'in-progress')<span class="mc-pill p-confirmed"><i></i>In Progress</span>
                  @else<span class="mc-pill p-pending"><i></i>Pending</span>@endif
                </td>
                <td><a class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline" href="{{ route('admin.lab-orders.show', $o->id) }}">Open →</a></td>
              </tr>
            @empty
              <tr><td colspan="5" class="py-6 text-center text-mut">Queue clear — no pending lab orders.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
    @endif

    @if($dashPharm)
    <section class="welly-card xl:col-span-2">
      <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-5 py-3.5">
        <div>
          <h2 class="welly-card-title">Recent prescriptions</h2>
          <p class="m-0 mt-0.5 text-[12px] text-mut">Latest written across doctors</p>
        </div>
        <a href="{{ route('admin.prescriptions.index') }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">All →</a>
      </div>
      <div class="overflow-x-auto">
        <table class="mc-tbl w-full">
          <thead><tr><th>Patient</th><th>Doctor</th><th>Date</th><th></th></tr></thead>
          <tbody>
            @forelse($recentPrescriptions as $p)
              <tr>
                <td><b class="block text-ink">{{ $p->patient_name }}</b></td>
                <td><span class="text-[13px] text-ink">{{ $p->doctor->name ?? '—' }}</span></td>
                <td><span class="text-[12px] text-mut">{{ $p->created_at->format('M j, Y') }}</span></td>
                <td><a class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline" href="{{ route('admin.prescriptions.show', $p->id) }}">Open →</a></td>
              </tr>
            @empty
              <tr><td colspan="4" class="py-6 text-center text-mut">No prescriptions yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>
    @endif
  </div>
  @endif

</div>

<script>
  (function () {
    var donut = document.getElementById('welly-donut');
    var elNew = document.getElementById('welly-pct-new');
    var elRec = document.getElementById('welly-pct-recovered');
    var elTreat = document.getElementById('welly-pct-treating');
    var elTotal = document.getElementById('welly-range-total');
    if (!donut) return;
    function paint(btn) {
      var n = parseInt(btn.dataset.new, 10) || 0;
      var r = parseInt(btn.dataset.recovered, 10) || 0;
      var t = parseInt(btn.dataset.treating, 10) || 0;
      donut.style.background = 'conic-gradient(from -90deg, #c2a15a 0 ' + n + '%, #0b8f74 ' + n + '% ' + (n + r) + '%, #2f353f ' + (n + r) + '% 100%)';
      elNew.textContent = n + '%';
      elRec.textContent = r + '%';
      elTreat.textContent = t + '%';
      elTotal.textContent = (parseInt(btn.dataset.total, 10) || 0) + ' bookings in range';
    }
    document.querySelectorAll('[data-welly-tab]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('[data-welly-tab]').forEach(function (b) { b.classList.remove('on'); });
        btn.classList.add('on');
        paint(btn);
      });
    });
  })();
</script>

@endsection
