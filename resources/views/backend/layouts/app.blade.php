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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Summernote (WYSIWYG editor) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs5.min.css">

    <!-- Tailwind v4 admin theme -->
    @vite(['resources/css/admin.css'])
</head>

<body class="app bg-panel text-ink antialiased">

    @includeIf('backend.layouts.partial.header')

    <main id="mc-content" class="mc-content bg-panel">
        @yield('content')
    </main>

    <!-- jQuery (kept for main.js search filters + Summernote) -->
    <script src="{{ asset('backend-assets/js/jquery-3.7.0.min.js') }}"></script>
    <!-- Bootstrap 5 JS (dropdowns, modals, tabs) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- Main (sidebar toggle, treeview expand, page search filters) -->
    <script src="{{ asset('backend-assets/js/main.js') }}"></script>
    <!-- Summernote -->
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs5.min.js"></script>
</body>

</html>
