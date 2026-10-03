@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · In-patient</p>
        <h1 class="mc-title">Edit <em>bed</em></h1>
    </div>
    <div>
        <a href="{{ route('admin.beds.index') }}" class="mc-btn ghost">Bed dashboard</a>
    </div>
</div>

@if($errors->any())
    <div class="mb-3 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">
        <ul class="mb-0 mt-0 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@include('beds._form', [
    'formAction' => route('admin.beds.update', $bed),
    'method' => 'PUT',
])

@endsection
