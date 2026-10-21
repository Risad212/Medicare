<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <title>@yield('meta_title', 'Medicare')</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Your description')">
    <meta name="keywords" content="@yield('meta_keywords', 'Your keywords')">
    
    <link rel="stylesheet" href="{{ asset('frontend-assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend-assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend-assets/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend-assets/css/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend-assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend-assets/css/main.css') }}">
    <style>
        html[dir="rtl"] body { direction: rtl; text-align: right; }
        html[dir="rtl"] .dropdown-menu { text-align: right; }
    </style>

    @php
        // Organization + WebSite for every frontend page. Each block is
        // emitted only when its required values exist in real settings —
        // nothing is invented to fill schema fields.
        $mcSettings = App\Models\GeneralSetting::first();
        $mcOrg = [];
        if ($mcSettings && trim((string) $mcSettings->site_name) !== '') {
            $mcOrg = ['@type' => 'Organization', 'name' => $mcSettings->site_name, 'url' => route('home')];
            if (trim((string) $mcSettings->logo) !== '') {
                $mcOrg['logo'] = asset('storage/'.$mcSettings->logo);
            }
            if (trim((string) $mcSettings->address) !== '') {
                $mcOrg['address'] = ['@type' => 'PostalAddress', 'streetAddress' => $mcSettings->address];
            }
            if (trim((string) $mcSettings->phone) !== '') {
                $mcOrg['contactPoint'] = ['@type' => 'ContactPoint', 'contactType' => 'customer service', 'telephone' => $mcSettings->phone];
            }
        }
        $mcJsonLd = $mcOrg ? [
            '@context' => 'https://schema.org',
            '@graph' => [
                $mcOrg,
                ['@type' => 'WebSite', 'name' => $mcSettings->site_name, 'url' => route('home')],
            ],
        ] : [];
    @endphp
    @include('frontend.components.seo-jsonld', ['jsonld' => $mcJsonLd])
</head>

<body>

    @includeIf('frontend.layouts.partial.header')

       @yield('front-content')

    @includeIf('frontend.layouts.partial.footer')

    <script src="{{ asset('frontend-assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('frontend-assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontend-assets/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('frontend-assets/js/parallax100.js') }}"></script>
    <script src="{{ asset('frontend-assets/js/jquery.waypoints.js') }}"></script>
    <script src="{{ asset('frontend-assets/js/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('frontend-assets/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('frontend-assets/js/moment.min.js') }}"></script>
    <script src="{{ asset('frontend-assets/js/main.js') }}"></script>

</body>

</html>