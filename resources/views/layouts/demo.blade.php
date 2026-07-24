<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('img/sija-logo-bg.png') }}">
    <title>@yield('title')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/css/nucleo-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/nucleo-svg.css') }}">
    <link rel="stylesheet" href="{{ asset('fontawesome-free-6.4.2-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/DataTables/datatables.css') }}">
    <link id="pagestyle" rel="stylesheet" href="{{ asset('assets/css/argon-dashboard.min.css?v=2.0.4') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('css')
</head>

<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-primary position-absolute w-100"></div>
    @include('layouts.partials.sidebar')
    @include('layouts.partials.navbar')

    <div class="container-fluid py-4">
        @yield('content')
    </div>
    <x-notification-component />

    <div id="modal-container"></div>

    <div class="fixed-plugin"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/DataTables/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/argon-dashboard.min.js?v=2.0.4') }}"></script>
    <script src="{{ asset('js/notification.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-toggle="modal"]').forEach(el => {
                el.setAttribute('data-bs-toggle', 'modal');
            });
            document.querySelectorAll('[data-dismiss="modal"]').forEach(el => {
                el.setAttribute('data-bs-dismiss', 'modal');
            });
            document.querySelectorAll('[data-target]').forEach(el => {
                el.setAttribute('data-bs-target', el.getAttribute('data-target'));
            });
        });

        document.getElementById('modal-container').innerHTML = '<object type="text/html" data="modal.html"></object>';
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = { damping: '0.5' }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>

    @stack('js')

</body>

</html>
