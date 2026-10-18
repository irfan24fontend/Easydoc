<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login | EasyDoc</title>
    <meta name="theme-color" content="#0a6b65">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: { ink: '#10262b', soft: '#46606a', teal: { DEFAULT: '#0c8c84', deep: '#0a6b65' }, mint: '#e3f3f0', mist: '#f3f7f7', line: '#d3e0e0', amber: '#f4b63f' },
                fontFamily: { display: ['"Bricolage Grotesque"', '"Hind Siliguri"', 'sans-serif'], sans: ['"Hind Siliguri"', 'system-ui', 'sans-serif'] }
            } }
        }
    </script>
    <style>
        body { font-family: 'Hind Siliguri', system-ui, sans-serif; -webkit-tap-highlight-color: transparent; }
        .font-display { letter-spacing: -0.01em; }
        .ruled { background-image: repeating-linear-gradient(transparent 0, transparent 39px, rgba(255,255,255,.07) 39px, rgba(255,255,255,.07) 40px); }
        a:focus-visible, button:focus-visible { outline: 3px solid #f4b63f; outline-offset: 2px; border-radius: 10px; }
        .rise { animation: rise .45s ease both; }
        @keyframes rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { .rise { animation: none; } }
        /* 16px inputs stop iOS from zooming on focus */
        input[type="email"], input[type="password"], input[type="text"] { font-size: 16px; }
    </style>
</head>
<body class="bg-white text-ink antialiased">

<div class="min-h-screen lg:grid lg:grid-cols-[1fr_1.05fr]">

    {{-- ============ BRAND PANEL (desktop) ============ --}}
    <aside class="hidden lg:flex ruled bg-teal-deep text-white flex-col justify-between p-12 xl:p-16">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5 w-fit" aria-label="EasyDoc home">
            <span class="w-10 h-10 rounded-lg bg-white text-teal-deep grid place-items-center font-display font-extrabold">ED</span>
            <span class="font-display font-extrabold text-xl">EasyDoc</span>
        </a>

        <div class="max-w-md">
            <h2 class="font-display font-extrabold text-4xl leading-[1.2]">আপনার Chamber, আরও গোছানো ও দ্রুত।</h2>
            <p class="mt-4 text-white/80 text-lg leading-relaxed">
                রোগীর তথ্য, AI diagnosis সাজেশন আর নিজের ডিজাইনের Prescription, সব এক Dashboard-এ।
            </p>

            <ul class="mt-8 space-y-4">
                <li class="flex gap-3">
                    <i class="fa-solid fa-shield-heart text-amber mt-1"></i>
                    <span><strong class="font-semibold">BMDC verified</strong><br><span class="text-white/70 text-[15px]">Super Admin যাচাই করা ডাক্তাররাই সিস্টেম ব্যবহার করেন।</span></span>
                </li>
                <li class="flex gap-3">
                    <i class="fa-solid fa-wand-magic-sparkles text-amber mt-1"></i>
                    <span><strong class="font-semibold">AI সাজেশন</strong><br><span class="text-white/70 text-[15px]">Diagnosis ও medicine সাজেশন, এডিট করার পূর্ণ স্বাধীনতা সহ।</span></span>
                </li>
                <li class="flex gap-3">
                    <i class="fa-solid fa-mobile-screen text-amber mt-1"></i>
                    <span><strong class="font-semibold">যেকোনো ডিভাইসে</strong><br><span class="text-white/70 text-[15px]">Chamber, বাসা বা পথে, ফোন থেকেই কাজ চলবে।</span></span>
                </li>
            </ul>
        </div>

        <p class="text-white/60 text-sm">&copy; {{ date('Y') }} EasyDoc. All rights reserved.</p>
    </aside>

    {{-- ============ FORM ============ --}}
    <main class="flex flex-col min-h-screen lg:min-h-0">
        {{-- Mobile top bar --}}
        <div class="lg:hidden flex items-center justify-between px-5 py-4" style="padding-top: calc(1rem + env(safe-area-inset-top, 0px))">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5" aria-label="EasyDoc home">
                <span class="w-9 h-9 rounded-lg bg-teal text-white grid place-items-center font-display font-extrabold">ED</span>
                <span class="font-display font-extrabold text-lg">EasyDoc</span>
            </a>
            <a href="{{ url('/') }}" class="text-sm font-semibold text-soft inline-flex items-center gap-1.5 px-3 py-2 rounded-lg hover:bg-mist"><i class="fa-solid fa-arrow-left text-xs"></i> Home</a>
        </div>

        <div class="flex-1 flex items-center justify-center px-5 sm:px-10 py-8">
            <div class="w-full max-w-md rise">

                <h1 class="font-display font-extrabold text-3xl">Login করুন</h1>
                <p class="mt-2 text-soft">ডাক্তার অ্যাকাউন্টের ইমেইল ও পাসওয়ার্ড দিন।</p>

                {{-- Session status (e.g. password reset link sent) --}}
                @if (session('status'))
                    <div class="mt-6 flex items-start gap-3 bg-mint text-teal-deep text-[15px] font-medium px-4 py-3 rounded-xl" role="status">
                        <i class="fa-solid fa-circle-check mt-1"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                {{-- Errors --}}
                @if ($errors->any())
                    <div class="mt-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 text-[15px] px-4 py-3 rounded-xl" role="alert">
                        <i class="fa-solid fa-circle-exclamation mt-1"></i>
                        <div class="space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form id="loginForm" method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold mb-1.5">Email</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-soft/70" aria-hidden="true"></i>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                   autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false"
                                   placeholder="doctor@example.com"
                                   class="w-full pl-11 pr-4 py-3.5 rounded-xl border bg-white placeholder:text-soft/50 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal {{ $errors->has('email') ? 'border-red-400' : 'border-line' }}">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-sm font-semibold">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-teal-deep hover:underline">পাসওয়ার্ড ভুলে গেছেন?</a>
                            @endif
                        </div>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-soft/70" aria-hidden="true"></i>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                   placeholder="আপনার পাসওয়ার্ড"
                                   class="w-full pl-11 pr-14 py-3.5 rounded-xl border bg-white placeholder:text-soft/50 focus:outline-none focus:ring-2 focus:ring-teal/40 focus:border-teal {{ $errors->has('password') ? 'border-red-400' : 'border-line' }}">
                            <button type="button" id="togglePw" class="absolute right-1.5 top-1/2 -translate-y-1/2 w-11 h-11 grid place-items-center text-soft hover:text-ink" aria-label="পাসওয়ার্ড দেখান" aria-pressed="false">
                                <i id="pwIcon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <p id="capsHint" class="hidden mt-2 text-sm items-center gap-1.5" style="color:#92600a"><i class="fa-solid fa-triangle-exclamation"></i> Caps Lock চালু আছে।</p>
                    </div>

                    <label for="remember_me" class="inline-flex items-center gap-2.5 cursor-pointer select-none py-1">
                        <input id="remember_me" type="checkbox" name="remember" class="w-5 h-5 rounded border-line text-teal focus:ring-teal">
                        <span class="text-[15px] text-soft">এই ডিভাইসে মনে রাখুন</span>
                    </label>

                    <button id="submitBtn" type="submit" class="w-full bg-teal hover:bg-teal-deep disabled:opacity-70 disabled:cursor-not-allowed text-white font-semibold py-3.5 rounded-xl inline-flex items-center justify-center gap-2 transition-colors">
                        <i id="btnIcon" class="fa-solid fa-right-to-bracket"></i>
                        <span id="btnText">Login</span>
                    </button>
                </form>

                @if (Route::has('register'))
                    <div class="mt-8 rounded-xl bg-mist px-5 py-4">
                        <p class="font-semibold">নতুন ডাক্তার?</p>
                        <p class="text-soft text-[15px] mt-0.5">BMDC নম্বর ও certificate দিয়ে Register করুন। Approve হলে সব ফিচার খুলে যাবে।</p>
                        <a href="{{ route('register') }}" class="mt-3 inline-flex items-center gap-2 font-semibold text-teal-deep hover:underline">
                            <i class="fa-solid fa-user-doctor"></i> Doctor Register
                        </a>
                    </div>
                @endif

                <a href="{{ url('/') }}" class="hidden lg:inline-flex items-center gap-2 text-soft hover:text-ink text-sm font-medium mt-8">
                    <i class="fa-solid fa-arrow-left text-xs"></i> হোমপেজে ফিরে যান
                </a>
            </div>
        </div>

        <p class="lg:hidden text-center text-soft text-xs pb-6" style="padding-bottom: calc(1.5rem + env(safe-area-inset-bottom, 0px))">&copy; {{ date('Y') }} EasyDoc</p>
    </main>
</div>

<script>
(function () {
    var pw = document.getElementById('password'), tg = document.getElementById('togglePw'), ic = document.getElementById('pwIcon');
    tg.addEventListener('click', function () {
        var show = pw.type === 'password';
        pw.type = show ? 'text' : 'password';
        ic.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        tg.setAttribute('aria-pressed', show ? 'true' : 'false');
        tg.setAttribute('aria-label', show ? 'পাসওয়ার্ড লুকান' : 'পাসওয়ার্ড দেখান');
    });

    var caps = document.getElementById('capsHint');
    function checkCaps(e) {
        var on = e.getModifierState && e.getModifierState('CapsLock');
        caps.classList.toggle('hidden', !on);
        caps.classList.toggle('inline-flex', !!on);
    }
    pw.addEventListener('keyup', checkCaps);
    pw.addEventListener('keydown', checkCaps);
    pw.addEventListener('blur', function () { caps.classList.add('hidden'); caps.classList.remove('inline-flex'); });

    var form = document.getElementById('loginForm'), btn = document.getElementById('submitBtn');
    var bi = document.getElementById('btnIcon'), bt = document.getElementById('btnText');
    function reset() { btn.disabled = false; bi.className = 'fa-solid fa-right-to-bracket'; bt.textContent = 'Login'; }
    form.addEventListener('submit', function () {
        if (!form.checkValidity()) { return; }
        btn.disabled = true; bi.className = 'fa-solid fa-circle-notch fa-spin'; bt.textContent = 'Login হচ্ছে...';
    });
    // Back button after a submit must not leave the button stuck
    window.addEventListener('pageshow', function (e) { if (e.persisted) { reset(); } });
})();
</script>
</body>
</html>