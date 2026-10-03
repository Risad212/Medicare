@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · In-patient</p>
        <h1 class="mc-title">Edit <em>ward</em></h1>
    </div>
    <div>
        <a href="{{ route('admin.wards.show', $ward) }}" class="mc-btn ghost">View ward</a>
    </div>
</div>

@include('wards._form', [
    'formAction' => route('admin.wards.update', $ward),
    'method' => 'PUT',
])

@endsection
