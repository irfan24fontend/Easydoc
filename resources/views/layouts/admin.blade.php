<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | EasyDoc</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }

        /* Tailwind (compiled into app.css) has a ".collapse { visibility: collapse }" utility.
           It hides Bootstrap's dropdown/collapse menus even when they are open, so undo it. */
        .collapse { visibility: visible !important; }

        /* Phones: tighter page padding, and tables that are not already wrapped scroll sideways */
        @media (max-width: 767px) {
            .main-content > .container-fluid { padding: 1rem !important; }
            .main-content table:not(.table-responsive table) {
                display: block; max-width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

@include('layouts.sidebar')

<div class="main-content">

    @include('layouts.navbar')

    <div class="container-fluid p-4">

        @include('layouts.flash')

        @yield('content')

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

</body>

</html>