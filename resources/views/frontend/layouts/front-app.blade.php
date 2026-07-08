<!DOCTYPE html>
<html lang="en">

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