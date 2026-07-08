@extends('backend.layouts.app')

@section('content')

<div class="row">
    <div class="col-md-12">
       @include('backend.components.seo-settings', [
            'title' => 'Doctor',
            'page' => 'doctor',
        ])
    </div>
</div>

@endsection