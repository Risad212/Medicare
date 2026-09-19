@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Access control</p>
        <h1 class="mc-title">Edit <em>{{ $role->name }}</em></h1>
        <p class="mc-sub">Changes apply to every staffer holding this role, immediately.</p>
    </div>
</div>

@if(session('success'))
    <div class="mb-3 rounded-lg bg-green-bg px-4 py-3 text-sm text-green-t">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
    @csrf
    @method('PUT')
    @include('backend.roles._form', ['role' => $role, 'granted' => $granted])
</form>

@endsection
