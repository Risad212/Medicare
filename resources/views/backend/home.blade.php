@extends('backend.layouts.app')

@section('content')
@php
  $hour = (int) now()->format('G');
  $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
  $deptCount = $topDoctors->pluck('department')->filter()->unique()->count();
  $confirmPct = round($confirmedAppointments / max(1, $totalAppointments) * 100);
  $statusTotal = max(1, $pendingAppointments + $confirmedAppointments + $completedAppointments + $cancelledAppointments);
  $barW = function ($n) use ($statusTotal) { return max(0, round($n / $statusTotal * 100)); };
  $maxTrend = max(1, max($weekTrend));
  $barH = fn ($n) => $maxTrend > 0 ? max(4, round($n / $maxTrend * 20)) : 4;
  $needLabel = $pendingAppointments == 1 ? '1 need' : $pendingAppointments . ' need';
  $maxAppt = max(1, max($monthlyAppointments));
  $maxLab = max(1, max($monthlyLabOrders));
  $maxDocLoad = max(1, $doctorLoad->max('appointment_count') ?? 0);
@endphp

<div class="space-y-5">

  {{-- ===== Page header ===== --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div>
      <h1 class="font-display text-[30px] font-bold leading-[1.15] tracking-tight text-ink">{{ $greeting }}, {{ auth()->user()->name }}</h1>
      <p class="mt-1 text-[14px] text-mut">{{ now()->format('l, F j, Y') }} · <strong class="text-ink">{{ $todayAppointments }} appointments today</strong></p>
    </div>
    <div class="flex flex-wrap gap-2.5 pt-1.5">
      <a href="{{ route('admin.doctors.create') }}" class="mc-btn">Add doctor</a>
      <a href="{{ route('admin.appointments.index') }}" class="mc-btn ghost">View appointments</a>
    </div>
  </div>

  {{-- ===== Metric cards ===== --}}
  <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="mc-card p-4 lg:p-5">
      <p class="text-[12px] font-semibold uppercase tracking-wider text-mut">Doctors</p>
      <p class="mt-1.5 font-display text-[30px] font-bold tabular-nums tracking-tight text-ink">{{ $totalDoctors }} <span class="text-[13px] font-normal text-mut">across {{ $deptCount ?: 0 }} departments</span></p>
      <p class="text-[13px] text-ink-2">{{ $activeDoctorsToday }} on duty today</p>
      <div class="mt-2.5 flex items-end gap-0.5">
        @foreach($topDoctors->take(8) as $doc)
          <i class="block w-full rounded-sm bg-line {{ $loop->last ? 'bg-ink-2' : '' }}" style="height:{{ max(4, min(22, 5 + $doc->appointment_count)) }}px"></i>
        @endforeach
        @for($i = 0; $i < max(0, 8 - $topDoctors->count()); $i++)
          <i class="block w-full rounded-sm bg-line"></i>
        @endfor
      </div>
    </div>

    <div class="mc-card p-4 lg:p-5">
      <p class="text-[12px] font-semibold uppercase tracking-wider text-mut">Appointments</p>
      <p class="mt-1.5 font-display text-[30px] font-bold tabular-nums tracking-tight text-ink">{{ $totalAppointments }} <span class="text-[13px] font-normal text-mut">· {{ $confirmPct }}% confirmed</span></p>
      <p class="text-[13px] text-ink-2">
        @if($pendingAppointments > 0)<span class="font-bold text-red">{{ $needLabel }} confirmation →</span>@else No pending appointments @endif
      </p>
      <div class="mt-2.5 flex items-end gap-0.5">
        @foreach($weekTrend as $i => $n)
          <i class="block w-full rounded-sm bg-line {{ $i === count($weekTrend) - 1 ? 'bg-ink-2' : '' }}" style="height:{{ $barH($n) }}px"></i>
        @endforeach
      </div>
    </div>

    <div class="mc-card p-4 lg:p-5">
      <p class="text-[12px] font-semibold uppercase tracking-wider text-mut">Lab tests</p>
      <p class="mt-1.5 font-display text-[30px] font-bold tabular-nums tracking-tight text-ink">{{ \App\Models\LabTest::count() }}</p>
      <p class="text-[13px] text-ink-2"><a href="{{ route('admin.lab-tests.index') }}" class="font-bold text-teal-dk no-underline hover:underline">Manage catalog →</a></p>
      <div class="mt-2.5 flex items-end gap-0.5">
        <i class="block w-full rounded-sm bg-line" style="height:10px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:12px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:11px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:14px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:13px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:16px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:15px"></i>
        <i class="block w-full rounded-sm bg-ink-2" style="height:18px"></i>
      </div>
    </div>

    <div class="mc-card p-4 lg:p-5">
      <p class="text-[12px] font-semibold uppercase tracking-wider text-mut">Blog posts</p>
      <p class="mt-1.5 font-display text-[30px] font-bold tabular-nums tracking-tight text-ink">{{ $totalBlogs }}</p>
      <p class="text-[13px] text-ink-2">Published and live</p>
      <div class="mt-2.5 flex items-end gap-0.5">
        <i class="block w-full rounded-sm bg-line" style="height:10px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:12px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:11px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:14px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:13px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:16px"></i>
        <i class="block w-full rounded-sm bg-line" style="height:15px"></i>
        <i class="block w-full rounded-sm bg-ink-2" style="height:18px"></i>
      </div>
    </div>
  </section>

  {{-- ===== Main grid ===== --}}
  <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_336px] items-start">

    {{-- Left column --}}
    <div class="flex flex-col gap-4">

      {{-- Today's appointments --}}
      <section class="mc-card">
        <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-[18px] py-3.5">
          <div>
            <h2 class="text-[16px] font-bold tracking-tight text-ink">Today's appointments</h2>
            <p class="mt-0.5 text-[12px] text-mut">Pending first · latest bookings across all doctors</p>
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
                  {{ \Illuminate\Support\Carbon::parse($a->appointment_date)->format('M j') }}<br>
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
              <tr><td colspan="5" class="py-6 text-center text-mut">No appointments yet — new bookings will appear here.</td></tr>
            @endforelse
          </tbody>
        </table>
        </div>
      </section>

      {{-- Status breakdown --}}
      <section class="mc-card">
        <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-[18px] py-3.5">
          <div>
            <h2 class="text-[16px] font-bold tracking-tight text-ink">Appointments by status</h2>
            <p class="mt-0.5 text-[12px] text-mut">{{ $totalAppointments }} total</p>
          </div>
        </div>
        <div class="px-[18px] py-4">
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
        <p class="border-t border-line-2 px-[18px] py-2 text-[12px] text-mut">
          {{ $confirmPct }}% confirmed ·
          @if($pendingAppointments > 0) {{ $pendingAppointments . ($pendingAppointments == 1 ? ' still needs' : ' still need') }} confirmation.@else all caught up.@endif
        </p>
      </section>

    </div>

    {{-- Right column --}}
    <div class="flex flex-col gap-4">

      {{-- Doctors on duty --}}
      <section class="mc-card">
        <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-[18px] py-3.5">
          <div>
            <h2 class="text-[16px] font-bold tracking-tight text-ink">Doctors on duty</h2>
            <p class="mt-0.5 text-[12px] text-mut">Top by appointment load</p>
          </div>
          <a href="{{ route('admin.doctors.index') }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">All {{ $totalDoctors }} →</a>
        </div>
        <div class="px-[18px] pb-3 pt-1">
          @forelse($topDoctors as $doc)
            <div class="flex items-center gap-2.5 border-b border-line-2 py-2.5 last:border-b-0">
              <div class="mc-av">{{ $doc->name ? strtoupper(mb_substr($doc->name, 0, 1)) : '?' }}</div>
              <div class="min-w-0">
                <b class="block text-[14px] text-ink">{{ $doc->name }}</b>
                <span class="text-[12px] text-mut">{{ $doc->department ?? 'General' }} · {{ $doc->appointment_count }} appts</span>
              </div>
              <span class="ml-auto shrink-0 text-[12px] font-bold {{ $doc->status ? 'text-teal-dk' : 'text-faint' }}">&bull; {{ $doc->status ? 'Active' : 'Off duty' }}</span>
            </div>
          @empty
            <div class="py-3.5 text-[14px] text-mut">No doctors found. <a href="{{ route('admin.doctors.create') }}" class="font-bold text-teal-dk">Add one</a>.</div>
          @endforelse
        </div>
      </section>

      {{-- Pending comments --}}
      <section class="mc-card">
        <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-[18px] py-3.5">
          <div>
            <h2 class="text-[16px] font-bold tracking-tight text-ink">Pending comments</h2>
            <p class="mt-0.5 text-[12px] text-mut">Awaiting moderation</p>
          </div>
        </div>
        <div class="px-[18px] pb-4 pt-0.5">
          @forelse($recentComments as $c)
            <div class="border-b border-line-2 py-2.5 text-[14px] last:border-b-0">
              <q class="block text-ink font-medium" quotes="“”">{{ \Illuminate\Support\Str::limit($c->comment, 90) }}</q>
              <span class="text-[12px] text-mut">{{ $c->name }} on "{{ $c->blog->title ?? '—' }}"</span>
            </div>
          @empty
            <div class="py-3.5 text-[14px] text-mut">No pending comments. All clear.</div>
          @endforelse
        </div>
        <a class="block mx-[18px] mb-[18px] rounded-lg bg-ink text-center py-2 text-[14px] font-semibold text-white no-underline hover:bg-black" href="{{ route('admin.comments.index') }}">Moderate comments</a>
      </section>

    </div>
  </div>

  {{-- ===== Analytics ===== --}}
  <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <section class="mc-card">
      <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-[18px] py-3.5">
        <div>
          <h2 class="text-[16px] font-bold tracking-tight text-ink">Monthly trends</h2>
          <p class="mt-0.5 text-[12px] text-mut">Appointments vs lab orders · last 6 months</p>
        </div>
        <a href="{{ route('admin.lab-orders.index') }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">Lab orders →</a>
      </div>
      <div class="flex items-end gap-1.5 px-[18px] pt-3.5 pb-1.5">
        @foreach($monthLabels as $i => $label)
          <div class="flex flex-1 flex-col items-center gap-1">
            <div class="flex items-end gap-0.5" style="height:90px">
              <i class="block w-[9px] rounded-sm bg-teal" style="height:{{ max(3, round($monthlyAppointments[$i] / $maxAppt * 88)) }}px" title="{{ $monthlyAppointments[$i] }} appts"></i>
              <i class="block w-[9px] rounded-sm bg-bright" style="height:{{ max(3, round($monthlyLabOrders[$i] / $maxLab * 88)) }}px" title="{{ $monthlyLabOrders[$i] }} lab orders"></i>
            </div>
            <span class="text-[11px] text-mut">{{ $label }}</span>
          </div>
        @endforeach
      </div>
      <div class="flex gap-3.5 px-[18px] pb-3 pt-1 text-[12px] text-mut">
        <span><i class="mr-1.5 inline-block h-2.5 w-2.5 rounded-sm bg-teal align-[-1px]"></i>Appointments</span>
        <span><i class="mr-1.5 inline-block h-2.5 w-2.5 rounded-sm bg-bright align-[-1px]"></i>Lab orders</span>
      </div>
    </section>

    <section class="mc-card">
      <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-[18px] py-3.5">
        <div>
          <h2 class="text-[16px] font-bold tracking-tight text-ink">Revenue</h2>
          <p class="mt-0.5 text-[12px] text-mut">Completed lab orders</p>
        </div>
        <a href="{{ route('admin.lab-orders.index', ['status' => 'completed']) }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">View completed →</a>
      </div>
      <div class="flex flex-wrap gap-3 px-[18px] py-4">
        <div>
          <p class="text-[12px] font-semibold uppercase tracking-wider text-mut">This month</p>
          <p class="mt-1 font-display text-[26px] font-bold tabular-nums tracking-tight text-ink">${{ number_format($revenueThisMonth, 2) }}</p>
        </div>
        <div>
          <p class="text-[12px] font-semibold uppercase tracking-wider text-mut">All time</p>
          <p class="mt-1 font-display text-[26px] font-bold tabular-nums tracking-tight text-ink">${{ number_format($revenueAllTime, 2) }}</p>
        </div>
        <div>
          <p class="text-[12px] font-semibold uppercase tracking-wider text-mut">Lab orders this month</p>
          <p class="mt-1 font-display text-[26px] font-bold tabular-nums tracking-tight text-ink">{{ $labOrdersThisMonth }}</p>
        </div>
        <div>
          <p class="text-[12px] font-semibold uppercase tracking-wider text-mut">Awaiting processing</p>
          <p class="mt-1 font-display text-[26px] font-bold tabular-nums tracking-tight text-ink">{{ $labOrdersPending }}</p>
        </div>
      </div>
    </section>
  </div>

  {{-- ===== Doctor load ===== --}}
  <section class="mc-card">
    <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-[18px] py-3.5">
      <div>
        <h2 class="text-[16px] font-bold tracking-tight text-ink">Doctor load</h2>
        <p class="mt-0.5 text-[12px] text-mut">Active bookings per doctor (cancelled excluded)</p>
      </div>
      <a href="{{ route('admin.doctors.index') }}" class="whitespace-nowrap text-[13px] font-bold text-teal-dk no-underline hover:underline">All doctors →</a>
    </div>
    <div class="px-[18px] py-4">
      @forelse($doctorLoad as $doc)
        <div class="grid items-center gap-3 text-[14px] md:grid-cols-[180px_1fr_52px] {{ !$loop->last ? 'mb-2' : '' }}">
          <div class="min-w-0">
            <b class="block text-ink">{{ $doc->name }}</b>
            <span class="text-[12px] text-mut">{{ $doc->department ?? 'General' }}</span>
          </div>
          <div class="h-2 overflow-hidden rounded bg-grey-bg"><i class="block h-full rounded bg-teal" style="width:{{ round($doc->appointment_count / $maxDocLoad * 100) }}%"></i></div>
          <span class="text-right font-bold tabular-nums text-ink">{{ $doc->appointment_count }}</span>
        </div>
      @empty
        <div class="py-3 text-[14px] text-mut">No doctors found.</div>
      @endforelse
    </div>
  </section>

</div>

@endsection