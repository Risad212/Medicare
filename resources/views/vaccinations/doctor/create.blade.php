@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Immunization</p>
        <h1 class="mc-title">New <em>vaccination</em></h1>
        <p class="mc-sub">Log a dose given or schedule an upcoming one.</p>
    </div>
    <div>
        <a href="{{ route('doctor.vaccinations.index') }}" class="mc-btn ghost"><i class="bi bi-arrow-left"></i> Back</a>
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

@include('vaccinations._form', [
    'formAction' => route('doctor.vaccinations.store'),
    'method' => 'POST',
])

@endsection
