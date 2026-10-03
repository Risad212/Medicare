@extends('frontend.layouts.front-app')

@section('meta_title', 'My Vaccinations')
@section('meta_description', 'Vaccination history and upcoming due dates')
@section('meta_keywords', 'vaccination, immunization, medicare')

@section('front-content')

@include('frontend.components.breadcrumb', [
    'title' => 'My Vaccinations'
])

<section class="py-4 py-md-5" style="background:#f4f7fb">
<div class="container" style="max-width:960px">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="mb-1" style="font-weight:800;letter-spacing:-.02em">Vaccination timeline</h2>
            <p class="text-muted mb-0">Completed doses and what's due next — for you and your children.</p>
        </div>
        <a href="{{ route('profile') }}" class="btn btn-outline-secondary" style="border-radius:12px">Back to profile</a>
    </div>

    @if($vaccinations->count())
        @foreach($vaccinations as $vaccination)
            <div class="card mb-3 shadow-sm border-0" style="border-radius:18px">
                <div class="card-body d-flex gap-3 align-items-start">
                    <div class="flex-shrink-0 d-flex align-items-center justify-content-center fw-bold text-white" style="width:48px;height:48px;border-radius:15px;background:linear-gradient(135deg,#05d3b0,#0ea5e9)">
                        {{ $vaccination->dose_number }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0 fw-bold">{{ $vaccination->vaccine_name }}</h5>
                            @if($vaccination->status == 1)<span class="badge bg-success">Completed</span>
                            @elseif($vaccination->status == 2)<span class="badge bg-secondary">Missed</span>
                            @elseif($vaccination->is_overdue)<span class="badge bg-warning text-dark">Overdue</span>
                            @else<span class="badge bg-info text-dark">Scheduled</span>@endif
                        </div>
                        <div class="text-muted small mt-1">
                            {{ $vaccination->subject_name }}
                            @if($vaccination->date_given)
                                &middot; Given {{ $vaccination->date_given->format('d M Y') }}
                            @endif
                            @if($vaccination->next_due_date)
                                &middot; Next due {{ $vaccination->next_due_date->format('d M Y') }}
                            @endif
                        </div>
                        @if($vaccination->is_overdue)
                            <div class="alert alert-warning mt-2 mb-0 py-2 px-3 small">This dose is overdue — please contact the clinic to reschedule.</div>
                        @endif
                        @if($vaccination->notes)
                            <p class="text-muted small mb-0 mt-2">{{ $vaccination->notes }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="card shadow-sm border-0" style="border-radius:18px">
            <div class="card-body text-center py-5">
                <h5 class="fw-bold">No vaccination records yet</h5>
                <p class="text-muted small mb-0">Records added by your doctor will appear here with due dates.</p>
            </div>
        </div>
    @endif

</div>
</section>

@endsection
