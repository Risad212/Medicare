{{-- Analytics module (app/Modules/Analytics/). Rendered only when the
     'analytics' flag is on; AdminController passes $analytics then. --}}
@if($dashAdmin && ! empty($analytics))
<section class="welly-card">
  <div class="welly-card-hd">
    <div>
      <h2 class="welly-card-title">Analytics</h2>
      <p class="m-0 mt-0.5 text-[12px] text-mut">Bookings, patients &amp; revenue at a glance</p>
    </div>
  </div>
  <div class="flex flex-wrap gap-x-8 gap-y-3 px-5 py-4">
    <div>
      <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Appointments this month</p>
      <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">{{ $analytics['appointmentsThisMonth'] }}
        <small class="text-[13px] font-bold {{ $analytics['appointmentMonthChange'] >= 0 ? 'text-teal-dk' : 'text-red-t' }}">({{ $analytics['appointmentMonthChange'] >= 0 ? '+' : '' }}{{ $analytics['appointmentMonthChange'] }}% vs last month)</small>
      </p>
    </div>
    <div>
      <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Registered patients</p>
      <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">{{ $analytics['totalRegisteredPatients'] }}</p>
    </div>
    <div>
      <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">New patients this month</p>
      <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">{{ $analytics['newPatientsThisMonth'] }}</p>
    </div>
    <div>
      <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Invoice revenue (this / last month)</p>
      <p class="m-0 mt-1 text-[24px] font-extrabold tabular-nums tracking-tight text-ink">${{ number_format($invoicePaidThisMonth, 0) }} <small class="text-[13px] text-mut">/ ${{ number_format($analytics['invoicePaidLastMonth'], 0) }}</small></p>
    </div>
    <div>
      <p class="m-0 text-[12px] font-semibold uppercase tracking-wider text-mut">Top 5 busiest doctors</p>
      <ol class="m-0 mt-1 pl-4 text-[13px] font-semibold text-ink">
        @forelse($analytics['busiestDoctors'] as $doc)
          <li>{{ $doc->name }} <span class="text-mut">({{ $doc->appointment_count }})</span></li>
        @empty
          <li class="text-mut">No bookings yet.</li>
        @endforelse
      </ol>
    </div>
  </div>
  <div class="grid grid-cols-1 gap-4 px-5 pb-5 xl:grid-cols-2">
    <div>
      <p class="m-0 mb-2 text-[12px] font-semibold uppercase tracking-wider text-mut">Appointments — last 30 days</p>
      <canvas id="analyticsTrend" height="140"></canvas>
    </div>
    <div>
      <p class="m-0 mb-2 text-[12px] font-semibold uppercase tracking-wider text-mut">Status breakdown</p>
      <canvas id="analyticsStatus" height="140"></canvas>
    </div>
  </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
  if (typeof Chart === 'undefined') return;
  var trend = document.getElementById('analyticsTrend');
  if (trend) {
    new Chart(trend, {
      type: 'line',
      data: {
        labels: @json($analytics['trendLabels']),
        datasets: [{ data: @json($analytics['trendCounts']), borderColor: '#0b8f74', backgroundColor: 'rgba(11,143,116,.12)', fill: true, tension: .35, pointRadius: 0 }]
      },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
  }
  var status = document.getElementById('analyticsStatus');
  if (status) {
    new Chart(status, {
      type: 'doughnut',
      data: {
        labels: ['Pending', 'Approved', 'Completed', 'Cancelled'],
        datasets: [{ data: @json(array_values($analytics['statusBreakdown'])), backgroundColor: ['#c2a15a', '#0b8f74', '#2f353f', '#c0392b'] }]
      },
      options: { plugins: { legend: { position: 'right' } } }
    });
  }
})();
</script>
@endpush
@endif
