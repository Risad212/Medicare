@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Prescription</p>
        <h1 class="mc-title">Edit <em>prescription</em> #{{ $prescription->id }}</h1>
        <p class="mc-sub">Update the diagnosis, advice or the list of medicines.</p>
    </div>
    <div>
        <a href="{{ route('doctor.prescriptions.show', $prescription) }}" class="mc-btn ghost">
            <i class="bi bi-capsule"></i> View Record
        </a>
    </div>
</div>

@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 mt-0 list-inside list-disc">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@include('backend.doctor-dashboard.prescriptions._form', [
    'formAction' => route('doctor.prescriptions.update', $prescription),
    'method' => 'PUT',
])

@endsection