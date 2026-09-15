@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Settings</p>
        <h1 class="mc-title">Service <em>SEO</em></h1>
        <p class="mc-sub">Meta tags for the Service page.</p>
    </div>
</div>

@include('backend.components.seo-settings', [
    'title' => 'Service',
    'page' => 'service',
])

@endsection
