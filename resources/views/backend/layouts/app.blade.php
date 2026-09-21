<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} — Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Bootstrap 5.3 CSS inside a cascade layer: it must NOT override the
         legacy Tailwind theme during the section-by-section migration.
         Layered styles always lose to unlayered ones, and this layer is
         declared before Tailwind's own layers, so Tailwind base/components
         keep winning (fonts, link colors, buttons) while Bootstrap's
         utilities/components (d-flex, dropdown, …) still work. -->
    <style>@import url("https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css") layer(bootstrap);</style>
    <!-- MediCare admin brand skin — plain CSS, no build step -->
    <link rel="stylesheet" href="{{ asset('backend-assets/css/admin-brand.css') }}">

    <!-- Tailwind v4 admin theme (legacy — being phased out section by section) -->
    @vite(['resources/css/admin.css'])
    @stack('styles')
</head>

<body class="app bg-panel text-ink antialiased">

    @includeIf('backend.layouts.partial.header')

    <main id="admin-content" class="admin-content">
        @yield('content')
    </main>

    <!-- jQuery (kept for main.js search filters) -->
    <script src="{{ asset('backend-assets/js/jquery-3.7.0.min.js') }}"></script>
    <!-- Bootstrap 5 JS (dropdowns, modals, tabs) -->
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- Main (sidebar toggle, treeview expand, page search filters) -->
    <script src="{{ asset('backend-assets/js/main.js') }}"></script>
    @stack('scripts')

    <!-- Notification bell: poll unread count every 60s -->
    <script>
        (function () {
            var badge = document.getElementById('mc-notif-badge');
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
</body>

</html>
