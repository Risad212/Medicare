@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Reviews</p>
        <h1 class="mc-title">Doctor <em>Reviews</em></h1>
        <p class="mc-sub">Moderate patient ratings before they appear on doctor profiles.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<div class="mc-card">
    <div class="table-responsive">
        <table class="mc-tbl">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Doctor</th>
                    <th>Patient</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                <tr>
                    <td class="mc-idx">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td data-notranslate>{{ $review->doctor?->name ?? 'N/A' }}</td>
                    <td>{{ $review->user?->name ?? 'Guest' }}</td>
                    <td data-notranslate class="text-warning">{{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', 5 - (int) $review->rating) }}</td>
                    <td>{{ Str::limit($review->comment, 60) }}</td>
                    <td>
                        @if($review->status == 1)
                            <span class="mc-pill p-active"><i></i>Approved</span>
                        @elseif($review->status == 2)
                            <span class="mc-pill p-cancelled"><i></i>Rejected</span>
                        @else
                            <span class="mc-pill p-pending"><i></i>Pending</span>
                        @endif
                    </td>
                    <td class="mc-num">{{ $review->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="mc-acts">
                            @if($review->status != 1)
                            <form action="{{ route('admin.doctor-reviews.update', $review->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="1">
                                <button type="submit" class="mc-btn sm dark"><i class="bi bi-check"></i> Approve</button>
                            </form>
                            @endif
                            @if($review->status == 0)
                            <form action="{{ route('admin.doctor-reviews.update', $review->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="2">
                                <button type="submit" class="mc-btn sm ghost"><i class="bi bi-x"></i> Reject</button>
                            </form>
                            @endif
                            <form action="{{ route('admin.doctor-reviews.destroy', $review->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mc-btn sm danger-ghost"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8"><div class="mc-empty"><b>Nothing on this chart</b>No doctor reviews yet. Submissions from patients appear here for approval.</div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
