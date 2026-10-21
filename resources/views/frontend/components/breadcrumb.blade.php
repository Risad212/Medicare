<div class="breadcrumb">
    <div class="container">
        <div class="col-xs-12">
            <div class="breadcrumb-content">
                <div class="breadcrumb-text-wrapper">
                    <h1 class="page-name">{{ $title }}</h1>
                    <ul class="breadcrumb-list">
                        <li><a href="{{ route('home') }}">{{ __('messages.nav.home') }}</a></li>
                        <li>{{ $title }}</li>
                    </ul>
                </div><!--/.breadcrumb-text-wrapper-->
            </div><!--/.breadcrumb-content-->
        </div><!--/.col-xs-12-->
    </div><!--/.col-xs-12-->
</div>

@php
    $jsonld = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => __('messages.nav.home'), 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => (string) $title],
        ],
    ];
@endphp
@include('frontend.components.seo-jsonld', ['jsonld' => $jsonld])
</div>