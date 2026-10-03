@extends('backend.layouts.app')

@section('content')

<div class="space-y-5">

  {{-- Page header --}}
  <div class="mc-head">
      <div>
          <p class="mc-kicker">MediCare · Blood Bank</p>
          <h1 class="mc-title">Blood <em>inventory</em></h1>
          <p class="mc-sub">Live stock per group and the pool of available bags.</p>
      </div>
      <div class="mc-head-acts">
          <a href="{{ route('admin.bloodbank.dashboard') }}" class="mc-btn ghost"><i class="bi bi-speedometer"></i> Dashboard</a>
      </div>
  </div>

  @if(session('success'))
      <div class="rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
  @endif
  @if(session('error'))
      <div class="rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{{ session('error') }}</div>
  @endif

  {{-- Stock table --}}
  <section class="mc-card">
      <div class="flex items-center justify-between gap-2.5 border-b border-line-2 px-3.5 py-3">
          <h5 class="mb-0 text-ink">Stock by group</h5>
          <span class="text-xs text-mut">updated live from donations &amp; issues</span>
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
                      <td class="mc-num">
                          {{ $row['units']['available'] }} units
                          <br><span class="text-xs text-mut">{{ $row['quantity']['available'] }} ml</span>
                      </td>
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

  {{-- Settings form --}}
  <section class="mc-card">
      <div class="border-b border-line-2 px-3.5 py-3"><h5 class="mb-0 text-ink">Configuration</h5></div>
      <form action="{{ route('admin.bloodbank.settings.update') }}" method="POST" class="grid grid-cols-1 gap-4 p-4 md:grid-cols-3 md:items-end">
          @csrf
          @method('PATCH')
          <div class="mc-f">
              <label>Low-stock threshold <span class="req">*</span></label>
              <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings->low_stock_threshold) }}" min="0">
              <span class="mc-hint">Units below which a group counts as low.</span>
              @error('low_stock_threshold') <span class="text-xs text-red-t">{{ $message }}</span> @enderror
          </div>
          <div class="mc-f">
              <label>Minimum donation interval (days) <span class="req">*</span></label>
              <input type="number" name="min_donation_days" value="{{ old('min_donation_days', $settings->min_donation_days) }}" min="1">
              <span class="mc-hint">Administrative eligibility window.</span>
              @error('min_donation_days') <span class="text-xs text-red-t">{{ $message }}</span> @enderror
          </div>
          <div class="mc-f">
              <label class="invisible" aria-hidden="true">Save settings</label>
              <div>
                  <button type="submit" class="mc-btn"><i class="bi bi-save"></i> Save settings</button>
              </div>
              <span class="mc-hint invisible" aria-hidden="true">&nbsp;</span>
          </div>
      </form>
  </section>

  {{-- Available / reserved bags --}}
  <section class="mc-card">
      <div class="border-b border-line-2 px-3.5 py-3"><h5 class="mb-0 text-ink">Available / reserved bags</h5></div>
      <div class="overflow-x-auto">
          <table class="mc-tbl">
              <thead>
                  <tr>
                      <th>#</th>
                      <th>Bag</th>
                      <th>Donor</th>
                      <th>Group</th>
                      <th>Qty</th>
                      <th>Expiry</th>
                      <th>Status</th>
                  </tr>
              </thead>
              <tbody>
              @forelse($bags as $key => $bag)
                  <tr>
                      <td class="mc-idx">{{ $bags->firstItem() + $key }}</td>
                      <td>{{ $bag->bag_number ?: '#' . $bag->id }}</td>
                      <td>{{ $bag->donor->name ?? 'Removed' }}</td>
                      <td><span class="mc-av r sm">{{ $bag->bloodGroup->name }}</span></td>
                      <td class="mc-num">{{ $bag->quantity }} {{ $bag->unit }}</td>
                      <td class="mc-num {{ $bag->expiry_date->lt(now()->addWeek()) ? 'font-bold text-red' : '' }}">{{ $bag->expiry_date->format('Y-m-d') }}</td>
                      <td>
                          <span class="mc-pill {{ $bag->status === 'available' ? 'p-active' : '' }}">{{ ucfirst($bag->status) }}</span>
                      </td>
                  </tr>
              @empty
                  <tr><td colspan="7"><div class="mc-empty"><b>Pool is empty</b>No available or reserved bags right now.</div></td></tr>
              @endforelse
              </tbody>
          </table>
      </div>
      <div class="mc-pg">
          <span>Showing {{ $bags->firstItem() ?? 0 }}–{{ $bags->lastItem() ?? 0 }} of {{ $bags->total() }}</span>
          {{ $bags->links() }}
      </div>
  </section>

</div>

@endsection