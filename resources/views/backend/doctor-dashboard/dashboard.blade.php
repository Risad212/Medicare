@extends('backend.layouts.app')

@section('content')

<div class="space-y-5">

  {{-- Welcome --}}
  <div>
    <h1 class="font-display text-[28px] font-bold tracking-tight text-ink">Welcome, Dr. {{ auth()->user()->name }}</h1>
  </div>

  {{-- Stats cards --}}
  <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="mc-card flex items-center gap-4 p-5">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal text-white text-xl">
        <i class="bi bi-calendar-check"></i>
      </div>
      <div>
        <p class="text-[13px] text-mut">Today's Appointments</p>
        <p class="font-display text-[26px] font-bold tabular-nums tracking-tight text-ink">{{ $todayAppointments }}</p>
      </div>
    </div>
    <div class="mc-card flex items-center gap-4 p-5">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-bg text-blue-t text-xl">
        <i class="bi bi-calendar2"></i>
      </div>
      <div>
        <p class="text-[13px] text-mut">Total Appointments</p>
        <p class="font-display text-[26px] font-bold tabular-nums tracking-tight text-ink">{{ $totalAppointments }}</p>
      </div>
    </div>
    <div class="mc-card flex items-center gap-4 p-5">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-bg text-amber-t text-xl">
        <i class="bi bi-hourglass-split"></i>
      </div>
      <div>
        <p class="text-[13px] text-mut">Pending Appointments</p>
        <p class="font-display text-[26px] font-bold tabular-nums tracking-tight text-ink">{{ $pendingAppointments }}</p>
      </div>
    </div>
  </div>

  {{-- Today's appointments table --}}
  <section class="mc-card">
    <div class="border-b border-line-2 px-[18px] py-3.5">
      <h2 class="text-[16px] font-bold tracking-tight text-ink">Today's Appointments</h2>
    </div>
    <div class="overflow-x-auto">
    <table class="mc-tbl w-full">
      <thead>
        <tr>
          <th>#</th>
          <th>Patient Name</th>
          <th>Phone</th>
          <th>Time</th>
          <th>Visit Type</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($todayAppointmentsList as $appointment)
        <tr>
          <td class="mc-num">{{ $loop->iteration }}</td>
          <td><span class="font-medium text-ink">{{ $appointment->patient_name }}</span></td>
          <td class="text-ink-2">{{ $appointment->phone }}</td>
          <td>{{ $appointment->timeSlot->time ?? 'N/A' }}</td>
          <td class="text-ink-2">
            @if($appointment->visit_type == 1) First Visit
            @elseif($appointment->visit_type == 2) Second Visit
            @elseif($appointment->visit_type == 3) Report Review
            @else N/A
            @endif
          </td>
          <td>
            @if($appointment->status == 0)
              <span class="mc-pill p-pending"><i></i>Pending</span>
            @elseif($appointment->status == 1)
              <span class="mc-pill p-info"><i></i>Approved</span>
            @else
              <span class="mc-pill p-completed"><i></i>Completed</span>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="py-8 text-center text-mut">No appointments today.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
    </div>
  </section>

</div>

@endsection