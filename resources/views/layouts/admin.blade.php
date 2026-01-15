<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel')</title>

    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    @include('layouts.partials.navbar')
    @include('layouts.partials.sidebar')

    <div class="content-wrapper">
        @yield('content')
    </div>

    @include('layouts.partials.footer')
</div>

<script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>
@stack('scripts')
</body>
</html>
