@extends('backend.layouts.app')

@section('content')

<div class="mc-head">
    <div>
        <p class="mc-kicker">MediCare · Settings</p>
        <h1 class="mc-title">Blog <em>SEO</em></h1>
        <p class="mc-sub">Meta tags for the Blog listing page.</p>
    </div>
</div>

@include('backend.components.seo-settings', [
    'title' => 'Blog',
    'page' => 'blog',
])

@endsection
