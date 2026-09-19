@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Content</p>
        <h1 class="mc-title">New serv<em>ice</em></h1>
        <p class="mc-sub">A card for the home and services pages.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-4 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

@include('backend.services._form')

@endsection
