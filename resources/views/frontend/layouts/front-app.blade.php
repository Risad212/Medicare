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

    @auth
    <!-- Patient notification bell: poll unread count every 60s -->
    <script>
        (function () {
            var badge = document.getElementById('mc-patient-notif-badge');
            if (!badge) return;
            var url = '{{ route('notifications.unread-count') }}';
            function refresh() {
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (!data) return;
                        var n = parseInt(data.count, 10) || 0;
                        badge.textContent = n > 99 ? '99+' : n;
                        badge.style.display = n > 0 ? '' : 'none';
                    })
                    .catch(function () {});
            }
            refresh();
            setInterval(refresh, 60000);
        })();
    </script>
    @endauth
</body>

</html>