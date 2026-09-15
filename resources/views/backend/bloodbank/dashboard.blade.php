@extends('backend.layouts.app')

@section('content')

<div class="space-y-5">

  {{-- Page header --}}
  <div class="mc-head">
      <div>
          <p class="mc-kicker">MediCare · Blood Bank</p>
          <h1 class="mc-title">Blood bank <em>dashboard</em></h1>
          <p class="mc-sub">Stock at a glance, urgent needs, and recent activity.</p>
      </div>
      <div class="mc-head-acts">
          <a href="{{ route('admin.bloodbank.inventory') }}" class="mc-btn"><i class="bi bi-box-seam"></i> Inventory</a>
          <a href="{{ route('admin.blood-donations.create') }}" class="mc-btn ghost"><i class="bi bi-plus-lg"></i> Record donation</a>
      </div>
  </div>

  @if(session('success'))
      <div class="rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
  @endif

  {{-- Stat cards --}}
  <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <div class="mc-card p-4">
          <div class="flex items-start justify-between">
              <div>
                  <p class="mb-0 text-sm text-mut">Total donors</p>
                  <p class="mb-0 font-display text-2xl font-bold">{{ $totalDonors }}</p>
              </div>
              <i class="bi bi-people text-2xl text-bright"></i>
          </div>
      </div>
      <div class="mc-card p-4">
          <div class="flex items-start justify-between">
              <div>
                  <p class="mb-0 text-sm text-mut">Total donations</p>
                  <p class="mb-0 font-display text-2xl font-bold">{{ $totalDonations }}</p>
              </div>
              <i class="bi bi-droplet text-2xl text-red"></i>
          </div>
      </div>
      <div class="mc-card p-4">
          <div class="flex items-start justify-between">
              <div>
                  <p class="mb-0 text-sm text-mut">Available stock</p>
                  <p class="mb-0 font-display text-2xl font-bold">{{ $availableQty }} <span class="text-sm font-normal text-mut">ml</span></p>
              </div>
              <i class="bi bi-box-seam text-2xl text-teal"></i>
          </div>
      </div>
      <div class="mc-card p-4">
          <div class="flex items-start justify-between">
              <div>
                  <p class="mb-0 text-sm text-mut">Pending requests</p>
                  <p class="mb-0 font-display text-2xl font-bold">{{ $pendingRequests }}</p>
                  <span class="text-sm text-mut">{{ $approvedRequests }} approved</span>
              </div>
              <i class="bi bi-clipboard-plus text-2xl text-faint"></i>
          </div>
      </div>
  </div>

  {{-- Emergency requests --}}
  @if($emergencies->isNotEmpty())
      <div class="rounded-xl border border-red/30 bg-red-bg/40 p-4">
          <h5 class="mb-3 font-bold text-red"><i class="bi bi-exclamation-triangle-fill"></i> Emergency requests</h5>
          <div class="overflow-x-auto">
              <table class="mc-tbl">
                  <thead>
                      <tr>
                          <th>#</th>
                          <th>Patient</th>
                          <th>Group</th>
                          <th>Qty</th>
                          <th>Required</th>
                          <th>Status</th>
                          <th class="text-right">Action</th>
                      </tr>
                  </thead>
                  <tbody>
                  @foreach($emergencies as $r)
                      <tr>
                          <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                          <td><b>{{ $r->patient->name }}</b></td>
                          <td><span class="mc-av r sm">{{ $r->bloodGroup->name }}</span></td>
                          <td>{{ $r->quantity }} {{ $r->unit }}</td>
                          <td class="mc-num">{{ $r->required_date->format('Y-m-d') }}</td>
                          <td><span class="mc-pill p-cancelled"><i></i>{{ ucfirst(str_replace('_', ' ', $r->status)) }}</span></td>
                          <td class="text-right">
                              <a href="{{ route('admin.blood-requests.show', $r->id) }}" class="mc-btn sm">Review</a>
                          </td>
                      </tr>
                  @endforeach
                  </tbody>
              </table>
          </div>
      </div>
  @endif

  {{-- Main content --}}
  <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
      {{-- Stock table --}}
      <section class="mc-card lg:col-span-2">
          <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-3.5 py-3">
              <h5 class="mb-0 text-ink">Stock by group</h5>
              <span class="text-xs text-mut">low-stock threshold: {{ $settings->low_stock_threshold }} units</span>
          </div>
          <div class="overflow-x-auto">
              <table class="mc-tbl">
                  <thead>
                      <tr>
                          <th>Group</th>
                          <th>Available</th>
                          <th>Reserved</th>
                          <th>Issued</th>
                          <th>Expired</th>
                          <th>Status</th>
                      </tr>
                  </thead>
                  <tbody>
                  @forelse($inventory as $row)
                      <tr>
                          <td><span class="mc-av r sm">{{ $row['blood_group']->name }}</span> {{ $row['blood_group']->name }}</td>
                          <td class="mc-num">{{ $row['units']['available'] }} <span class="text-xs text-mut">({{ $row['quantity']['available'] }} ml)</span></td>
                          <td class="mc-num">{{ $row['units']['reserved'] }}</td>
                          <td class="mc-num">{{ $row['units']['issued'] }}</td>
                          <td class="mc-num">{{ $row['units']['expired'] }}</td>
                          <td>
                              @if($row['status'] === 'available')
                                  <span class="mc-pill p-active"><i></i>Available</span>
                              @elseif($row['status'] === 'low')
                                  <span class="mc-pill p-low"><i></i>Low stock</span>
                              @else
                                  <span class="mc-pill p-cancelled"><i></i>Out of stock</span>
                              @endif
                          </td>
                      </tr>
                  @empty
                      <tr><td colspan="6"><div class="mc-empty"><b>Nothing on this chart</b>No blood groups defined yet.</div></td></tr>
                  @endforelse
                  </tbody>
              </table>
          </div>
      </section>

      {{-- Sidebar widgets --}}
      <div class="flex flex-col gap-4">
          {{-- Low stock --}}
          <div class="mc-card">
              <div class="border-b border-line-2 px-3.5 py-3"><h5 class="mb-0 text-ink">Low stock</h5></div>
              @forelse($lowStock as $row)
                  <div class="flex items-center gap-2.5 border-b border-line-2 px-3.5 py-3 last:border-b-0">
                      <span class="mc-av r sm">{{ $row['blood_group']->name }}</span>
                      <div class="min-w-0">
                          <b class="block text-sm text-ink">{{ $row['blood_group']->name }}</b>
                          <div class="text-xs text-mut">{{ $row['units']['available'] }} available</div>
                      </div>
                      <span class="ml-auto shrink-0 text-sm font-bold {{ $row['status'] === 'low' ? 'text-amber-dot' : 'text-red' }}">
                          {{ $row['status'] === 'low' ? 'Low' : 'Out' }}
                      </span>
                  </div>
              @empty
                  <div class="px-3.5 py-3 text-sm text-mut">All groups are sufficiently stocked.</div>
              @endforelse
          </div>

          {{-- Recent donations --}}
          <div class="mc-card">
              <div class="border-b border-line-2 px-3.5 py-3"><h5 class="mb-0 text-ink">Recent donations</h5></div>
              @forelse($recentDonations as $donation)
                  <div class="flex items-center gap-2.5 border-b border-line-2 px-3.5 py-3 last:border-b-0">
                      <span class="mc-av r sm">{{ $donation->bloodGroup->name }}</span>
                      <div class="min-w-0">
                          <b class="block text-sm text-ink">{{ $donation->donor->name ?? 'Removed' }}</b>
                          <div class="text-xs text-mut">{{ $donation->donation_date->format('Y-m-d') }} · {{ $donation->quantity }} ml</div>
                      </div>
                      <span class="ml-auto mc-pill {{ $donation->status === 'available' ? 'p-active' : 'p-cancelled' }}">{{ ucfirst($donation->status) }}</span>
                  </div>
              @empty
                  <div class="px-3.5 py-3 text-sm text-mut">No donations yet.</div>
              @endforelse
          </div>
      </div>
  </div>

  {{-- Recent requests --}}
  <section class="mc-card">
      <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-3.5 py-3">
          <h5 class="mb-0 text-ink">Recent requests</h5>
          <a href="{{ route('admin.blood-requests.index') }}" class="mc-btn sm">All requests</a>
      </div>
      <div class="overflow-x-auto">
          <table class="mc-tbl">
              <thead>
                  <tr>
                      <th>#</th>
                      <th>Patient</th>
                      <th>Group</th>
                      <th>Qty</th>
                      <th>Urgency</th>
                      <th>Required</th>
                      <th>Status</th>
                  </tr>
              </thead>
              <tbody>
              @forelse($recentRequests as $r)
                  <tr>
                      <td class="mc-idx">#{{ $r->id }}</td>
                      <td><b>{{ $r->patient->name }}</b></td>
                      <td><span class="mc-av r sm">{{ $r->bloodGroup->name }}</span></td>
                      <td class="mc-num">{{ $r->quantity }} {{ $r->unit }}</td>
                      <td>
                          @php
                              $uClass = match($r->urgency) {
                                  'emergency' => 'p-cancelled',
                                  'urgent' => '',
                                  default => 'p-active',
                              };
                          @endphp
                          <span class="mc-pill {{ $uClass }}">{{ ucfirst($r->urgency) }}</span>
                      </td>
                      <td class="mc-num">{{ $r->required_date->format('Y-m-d') }}</td>
                      <td><span class="mc-pill {{ $r->status === 'pending' ? '' : 'p-active' }}">{{ ucfirst(str_replace('_', ' ', $r->status)) }}</span></td>
                  </tr>
              @empty
                  <tr><td colspan="7"><div class="mc-empty"><b>Nothing on this chart</b>No blood requests yet.</div></td></tr>
              @endforelse
              </tbody>
          </table>
      </div>
  </section>

</div>

@endsection