<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | EasyDoc</title>

    {{-- Sidebar-er ager obostha page load-er age-i boshiye dei (jate jhikmik na kore) --}}
    <script>
        (function () {
            try {
                if (localStorage.getItem('easydoc.sidebar') === 'collapsed' && window.innerWidth >= 992) {
                    document.documentElement.classList.add('sb-collapsed');
                }
            } catch (e) {}
        })();
    </script>

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
    </style>

    @stack('styles')
</head>

<body>

@include('layouts.doctor-sidebar')

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="main-content">

    @include('layouts.doctor-navbar')

    <div class="container-fluid p-4">

        @include('layouts.flash')

        @yield('content')

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ===== Sidebar toggle: desktop-e collapse/expand (icon rail), mobile-e drawer =====
    (function () {
        const root = document.documentElement;
        const KEY = 'easydoc.sidebar';
        const mq = window.matchMedia('(max-width: 991.98px)');
        const buttons = document.querySelectorAll('[data-sidebar-toggle]');
        const overlay = document.getElementById('sidebarOverlay');
        const links = document.querySelectorAll('.sidebar-container .side-link');

        const isMobile = () => mq.matches;

        function syncTitles() {
            // Collapsed obosthay icon-er upor mouse nile naam dekhabe
            const collapsed = !isMobile() && root.classList.contains('sb-collapsed');
            links.forEach(a => {
                const label = a.querySelector('span');
                if (collapsed && label) a.setAttribute('title', label.textContent.trim());
                else a.removeAttribute('title');
            });
        }

        function update() {
            const expanded = isMobile() ? root.classList.contains('sb-open') : !root.classList.contains('sb-collapsed');
            buttons.forEach(b => b.setAttribute('aria-expanded', expanded ? 'true' : 'false'));
            syncTitles();
        }

        function setCollapsed(v) {
            root.classList.toggle('sb-collapsed', v);
            try { localStorage.setItem(KEY, v ? 'collapsed' : 'expanded'); } catch (e) {}
            update();
        }

        function setOpen(v) {
            root.classList.toggle('sb-open', v);
            document.body.style.overflow = v ? 'hidden' : '';
            update();
        }

        buttons.forEach(b => b.addEventListener('click', function () {
            if (isMobile()) setOpen(!root.classList.contains('sb-open'));
            else setCollapsed(!root.classList.contains('sb-collapsed'));
        }));

        overlay.addEventListener('click', () => setOpen(false));
        document.addEventListener('keydown', e => { if (e.key === 'Escape') setOpen(false); });
        links.forEach(a => a.addEventListener('click', () => { if (isMobile() && !a.classList.contains('disabled') && !a.classList.contains('has-sub')) setOpen(false); }));
        mq.addEventListener('change', () => { setOpen(false); update(); });

        update();
    })();
</script>

@if(\Illuminate\Support\Facades\Route::has('doctor.notifications.poll'))
    @include('doctor.notifications._popup')
@endif

@stack('scripts')

</body>

</html>