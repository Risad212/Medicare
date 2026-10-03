@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · In-patient</p>
        <h1 class="mc-title">New <em>bed</em></h1>
    </div>
    <div>
        <a href="{{ route('admin.beds.index') }}" class="mc-btn ghost"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

@include('beds._form', [
    'formAction' => route('admin.beds.store'),
    'method' => 'POST',
])

@endsection
