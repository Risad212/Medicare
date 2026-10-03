@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · In-patient</p>
        <h1 class="mc-title">Edit <em>room</em></h1>
    </div>
    <div>
        <a href="{{ route('admin.rooms.show', $room) }}" class="mc-btn ghost">View room</a>
    </div>
</div>

@include('rooms._form', [
    'formAction' => route('admin.rooms.update', $room),
    'method' => 'PUT',
])

@endsection
