@extends('backend.layouts.app')

@section('content')
@php
  $breakTotal = max(1, $todayBreakdown['pending'] + $todayBreakdown['confirmed'] + $todayBreakdown['completed'] + $todayBreakdown['cancelled']);
  $barW = fn ($n) => max(0, round($n / $breakTotal * 100));
@endphp

<div class="space-y-4">

  {{-- ===== Page header ===== --}}
  <div class="flex flex-wrap items-start justify-between gap-3">
    <div>
      <h1 class="welly-title">Dashboard</h1>
      <p class="welly-subtitle">{{ $doctor->name }} · {{ $doctor->specialist ?? $doctor->department ?? 'General' }} — {{ $todayAppointments }} appointment{{ $todayAppointments === 1 ? '' : 's' }} today</p>
    </div>
    <div class="flex flex-wrap gap-2.5 pt-1">
      <a href="{{ route('doctor.appointments') }}" class="welly-outline-btn"><i class="bi bi-calendar-check"></i> All appointments</a>
      <a href="{{ route('doctor.profile.edit') }}" class="mc-btn sm"><i class="bi bi-person-gear"></i> My profile</a>
    </div>
  </div>

  {{-- ===== Stat cards ===== --}}
  <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="welly-stat">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="welly-stat-num">{{ $todayAppointments }}</p>
          <p class="welly-stat-label">Today's appointments</p>
        </div>
        <span class="welly-stat-icon"><i class="bi bi-calendar-date"></i></span>
      </div>
    </div>
    <div class="welly-stat">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="welly-stat-num">{{ $pendingAppointments }}</p>
          <p class="welly-stat-label">Pending approvals</p>
        </div>
        <span class="welly-stat-icon"><i class="bi bi-hourglass-split"></i></span>
      </div>
    </div>
    <div class="welly-stat">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="welly-stat-num">{{ $completedThisMonth }}</p>
          <p class="welly-stat-label">Completed this month</p>
        </div>
        <span class="welly-stat-icon"><i class="bi bi-check2-circle"></i></span>
      </div>
    </div>
    <div class="welly-stat">
      <div class="flex items-start justify-between gap-3">
        <div>
          <p class="welly-stat-num">{{ number_format($totalAppointments) }}</p>
          <p class="welly-stat-label">Total appointments</p>
        </div>
        <span class="welly-stat-icon"><i class="bi bi-heart-pulse"></i></span>
      </div>
    </div>
  </section>

  {{-- ===== Today's list + side rail ===== --}}
  <div class="grid grid-cols-1 items-start gap-4 xl:grid-cols-[1fr_340px]">

    {{-- Today's appointments --}}
    <section class="welly-card">
      <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-5 py-3.5">
        <div>
          <h2 class="welly-card-title">Today's appointments</h2>
          <p class="m-0 mt-0.5 text-[12px] text-mut">Pending first · approve or cancel in one click</p>
        </div>
        <a href="{{ route('doctor.appointments') }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">View all {{ $totalAppointments }} →</a>
      </div>
      <div class="overflow-x-auto">
        <table class="mc-tbl w-full">
          <thead><tr><th>Patient</th><th>Time</th><th>Visit Type</th><th>Status</th><th></th></tr></thead>
          <tbody>
            @forelse($todayAppointmentsList->sortBy('status')->values() as $a)
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
                <td><span class="text-[13px] font-bold tabular-nums text-ink">{{ $a->timeSlot->time ?? '—' }}</span></td>
                <td>
                  @if($a->visit_type == 1) First Visit
                  @elseif($a->visit_type == 2) Second Visit
                  @elseif($a->visit_type == 3) Report Review
                  @else — @endif
                </td>
                <td>
                  @if($a->status == 0)<span class="mc-pill p-pending"><i></i>Pending</span>
                  @elseif($a->status == 1)<span class="mc-pill p-confirmed"><i></i>Confirmed</span>
                  @elseif($a->status == 2)<span class="mc-pill p-completed"><i></i>Completed</span>
                  @else<span class="mc-pill p-cancelled"><i></i>Cancelled</span>@endif
                </td>
                <td>
                  @if($a->status == 0 || $a->status == 1)
                  <div class="mc-acts">
                    @if($a->status == 0)
                    <form action="{{ route('doctor.appointments.update', $a->id) }}" method="POST" class="m-0">
                      @csrf @method('PUT')
                      <input type="hidden" name="status" value="1">
                      <button class="border-0 bg-transparent p-0 text-[16px] text-teal hover:text-teal-dk" title="Approve"><i class="bi bi-check-circle"></i></button>
                    </form>
                    @else
                    <form action="{{ route('doctor.appointments.update', $a->id) }}" method="POST" class="m-0">
                      @csrf @method('PUT')
                      <input type="hidden" name="status" value="2">
                      <button class="border-0 bg-transparent p-0 text-[16px] text-teal hover:text-teal-dk" title="Complete"><i class="bi bi-check2-circle"></i></button>
                    </form>
                    @endif
                    <form action="{{ route('doctor.appointments.update', $a->id) }}" method="POST" class="m-0" onsubmit="return confirm('Cancel this appointment?')">
                      @csrf @method('PUT')
                      <input type="hidden" name="status" value="3">
                      <button class="border-0 bg-transparent p-0 text-[16px] text-red hover:text-red-t" title="Cancel"><i class="bi bi-x-circle"></i></button>
                    </form>
                  </div>
                  @else
                  <a class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline" href="{{ route('doctor.appointments') }}">Open →</a>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="py-6 text-center text-mut">No appointments today. Enjoy the quiet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <div class="flex flex-col gap-4">
      {{-- Today's status breakdown --}}
      <section class="welly-card">
        <div class="border-b border-line-2 px-5 py-3.5">
          <h2 class="welly-card-title">Today's status</h2>
          <p class="m-0 mt-0.5 text-[12px] text-mut">{{ $todayAppointments }} booked · {{ $todayBreakdown['cancelled'] }} cancelled</p>
        </div>
        <div class="px-5 py-4">
          <div class="grid items-center gap-2.5 text-[14px]">
            <span class="text-ink-2">Pending</span>
            <div class="h-2 overflow-hidden rounded bg-grey-bg"><i class="block h-full rounded bg-amber-dot" style="width:{{ $barW($todayBreakdown['pending']) }}%"></i></div>
            <span class="text-right font-bold tabular-nums text-ink">{{ $todayBreakdown['pending'] }}</span>
          </div>
          <div class="mt-2 grid items-center gap-2.5 text-[14px]">
            <span class="text-ink-2">Confirmed</span>
            <div class="h-2 overflow-hidden rounded bg-grey-bg"><i class="block h-full rounded bg-teal" style="width:{{ $barW($todayBreakdown['confirmed']) }}%"></i></div>
            <span class="text-right font-bold tabular-nums text-ink">{{ $todayBreakdown['confirmed'] }}</span>
          </div>
          <div class="mt-2 grid items-center gap-2.5 text-[14px]">
            <span class="text-ink-2">Completed</span>
            <div class="h-2 overflow-hidden rounded bg-grey-bg"><i class="block h-full rounded bg-bright" style="width:{{ $barW($todayBreakdown['completed']) }}%"></i></div>
            <span class="text-right font-bold tabular-nums text-ink">{{ $todayBreakdown['completed'] }}</span>
          </div>
          <div class="mt-2 grid items-center gap-2.5 text-[14px]">
            <span class="text-ink-2">Cancelled</span>
            <div class="h-2 overflow-hidden rounded bg-grey-bg"><i class="block h-full rounded bg-faint" style="width:{{ $barW($todayBreakdown['cancelled']) }}%"></i></div>
            <span class="text-right font-bold tabular-nums text-ink">{{ $todayBreakdown['cancelled'] }}</span>
          </div>
        </div>
      </section>

      {{-- Upcoming visits --}}
      <section class="welly-card">
        <div class="border-b border-line-2 px-5 py-3.5">
          <h2 class="welly-card-title">Upcoming visits</h2>
          <p class="m-0 mt-0.5 text-[12px] text-mut">Next appointments after today</p>
        </div>
        <div class="px-5 pb-3 pt-1">
          @forelse($upcomingList as $a)
            <div class="welly-sched">
              <div class="flex items-center justify-between gap-2">
                <span class="welly-sched-meta"><i class="bi bi-calendar3"></i> {{ \Illuminate\Support\Carbon::parse($a->appointment_date)->format('D, M j') }}</span>
                <div class="welly-sched-meta"><i class="bi bi-clock"></i> {{ $a->timeSlot->time ?? '—' }}</div>
              </div>
              <div class="flex items-center justify-between gap-2 pt-0.5">
                <span class="welly-sched-meta"><i class="bi bi-person"></i> {{ $a->patient_name }}</span>
                @if($a->status == 0)<span class="mc-pill p-pending"><i></i>Pending</span>
                @else<span class="mc-pill p-confirmed"><i></i>Confirmed</span>@endif
              </div>
            </div>
          @empty
            <div class="py-3.5 text-[14px] text-mut">Nothing scheduled after today.</div>
          @endforelse
        </div>
        <a href="{{ route('doctor.appointments') }}" class="mx-5 mb-5 block rounded-lg bg-ink py-2 text-center text-[14px] font-semibold text-white no-underline hover:bg-black">Open appointments →</a>
      </section>
    </div>
  </div>

</div>

@endsection
