@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Immunization</p>
        <h1 class="mc-title">Edit <em>vaccination</em> #{{ $vaccination->id }}</h1>
        <p class="mc-sub">Update dose details or reschedule.</p>
    </div>
    <div>
        <a href="{{ route('doctor.vaccinations.show', $vaccination) }}" class="mc-btn ghost">View record</a>
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
    'formAction' => route('doctor.vaccinations.update', $vaccination),
    'method' => 'PUT',
])

@endsection
