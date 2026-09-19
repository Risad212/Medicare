@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Access control</p>
        <h1 class="mc-title">New <em>role</em></h1>
        <p class="mc-sub">Name it, then tick the modules its holders may open.</p>
    </div>
</div>

<form action="{{ route('admin.roles.store') }}" method="POST">
    @csrf
    @include('backend.roles._form', ['role' => null, 'granted' => []])
</form>

@endsection
