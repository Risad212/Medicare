@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Emergency</p>
        <h1 class="mc-title">Ambulance <em>requests</em></h1>
        <p class="mc-sub">Newest emergencies on top — tap the phone number to call back.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 mt-0 list-inside list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mc-ecg"><span>Emergency queue</span><span>{{ $requests->total() }} requests</span></div>

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Requester</th>
                    <th>Phone</th>
                    <th>Pickup</th>
                    <th>Emergency</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($requests as $key => $ambulanceRequest)
                <tr>
                    <td class="mc-idx">{{ $requests->firstItem() + $key }}</td>
                    <td><b>{{ $ambulanceRequest->requester_name }}</b></td>
                    <td class="mc-num"><a href="tel:{{ $ambulanceRequest->requester_phone }}">{{ $ambulanceRequest->requester_phone }}</a></td>
                    <td>{{ \Illuminate\Support\Str::limit($ambulanceRequest->pickup_address, 60) }}</td>
                    <td>{{ $ambulanceRequest->emergency_type ?? '—' }}</td>
                    <td>
                        @if($ambulanceRequest->status == 1)<span class="mc-pill p-progress">Dispatched</span>
                        @elseif($ambulanceRequest->status == 2)<span class="mc-pill p-confirmed">Completed</span>
                        @elseif($ambulanceRequest->status == 3)<span class="mc-pill p-cancelled">Cancelled</span>
                        @else<span class="mc-pill p-pending">Requested</span>@endif
                    </td>
                    <td class="mc-num text-nowrap">{{ $ambulanceRequest->created_at->diffForHumans() }}</td>
                    <td>
                        @if(in_array($ambulanceRequest->status, [0, 1], true))
                            <form action="{{ route('admin.ambulance-requests.update', $ambulanceRequest->id) }}" method="POST" class="d-flex gap-2">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select form-select-sm" style="min-width:130px">
                                    @foreach(\App\Modules\Ambulance\Models\AmbulanceRequest::TRANSITIONS[$ambulanceRequest->status] as $next)
                                        <option value="{{ $next }}">{{ \App\Modules\Ambulance\Models\AmbulanceRequest::STATUSES[$next] }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="mc-btn sm dark">Set</button>
                            </form>
                        @else
                            <span class="text-mut">Terminal</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8"><div class="mc-empty"><b>No requests</b>No ambulance requests yet.</div></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
        <div class="px-4.5 py-4">{{ $requests->links() }}</div>
    @endif
</div>

@endsection
