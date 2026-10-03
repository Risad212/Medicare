@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Pharmacy</p>
        <h1 class="mc-title">Edit <em>medicine</em></h1>
    </div>
    <div>
        <a href="{{ route('admin.medicines.index') }}" class="mc-btn ghost">Back to stock</a>
    </div>
</div>

@include('pharmacy._form', [
    'formAction' => route('admin.medicines.update', $medicine),
    'method' => 'PUT',
])

@endsection
