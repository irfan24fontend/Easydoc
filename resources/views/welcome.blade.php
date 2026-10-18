<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>EasyDoc | ডাক্তারদের স্মার্ট Prescription Platform</title>
    <meta name="description" content="AI diagnosis সাজেশন, নিজের ব্র্যান্ডে Prescription, Print ও PDF। BMDC verified ডাক্তারদের জন্য EasyDoc।">
    <meta name="theme-color" content="#0c8c84">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: { ink: '#10262b', soft: '#46606a', teal: { DEFAULT: '#0c8c84', deep: '#0a6b65' }, mint: '#e3f3f0', mist: '#f3f7f7', line: '#d3e0e0', amber: '#f4b63f', paper: '#fdfcf8' },
                fontFamily: { display: ['"Bricolage Grotesque"', '"Hind Siliguri"', 'sans-serif'], sans: ['"Hind Siliguri"', 'system-ui', 'sans-serif'] }
            } }
        }
    </script>
    <style>
        body { font-family: 'Hind Siliguri', system-ui, sans-serif; -webkit-tap-highlight-color: transparent; }
        .font-display { letter-spacing: -0.01em; }
        a:focus-visible, button:focus-visible, summary:focus-visible { outline: 3px solid #f4b63f; outline-offset: 2px; border-radius: 10px; }
        .rx-paper {
            background-image: repeating-linear-gradient(transparent 0, transparent 31px, #e8efee 31px, #e8efee 32px);
        }
        .swap { transition: opacity .35s ease, transform .35s ease; }
        .swap.out { opacity: 0; transform: translateY(6px); }
        .caret::after { content: ''; display: inline-block; width: 2px; height: 1em; background: #0c8c84; margin-left: 2px; vertical-align: -2px; animation: blink 1s steps(1) infinite; }
        @keyframes blink { 50% { opacity: 0; } }
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .chev { transform: rotate(180deg); }
        .reveal { opacity: 0; transform: translateY(12px); transition: opacity .5s ease, transform .5s ease; }
        .reveal.in { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) {
            .reveal { opacity: 1; transform: none; transition: none; }
            .swap { transition: none; }
            .caret::after { animation: none; }
        }
        body { padding-bottom: env(safe-area-inset-bottom, 0px); }
    </style>
</head>
<body class="bg-white text-ink antialiased">
    @php
        // Pricing (৳). Change here and the whole page updates.
        $rxPrice = 5;  // per prescription
        $aiPrice = 2;  // per AI suggestion click
    @endphp

    {{-- ============ HEADER ============ --}}
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-line" style="padding-top: env(safe-area-inset-top, 0px)">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5" aria-label="EasyDoc home">
                <span class="w-9 h-9 rounded-lg bg-teal text-white grid place-items-center font-display font-extrabold">ED</span>
                <span class="font-display font-extrabold text-xl">EasyDoc</span>
            </a>

            <nav class="hidden md:flex items-center gap-7 text-[15px] font-medium text-soft" aria-label="Main">
                <a href="#features" class="hover:text-teal">Features</a>
                <a href="#how" class="hover:text-teal">কীভাবে শুরু করবেন</a>
                <a href="#pricing" class="hover:text-teal">Pricing</a>
                <a href="#faq" class="hover:text-teal">FAQ</a>
                <a href="#contact" class="hover:text-teal">যোগাযোগ</a>
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ url('/dashboard') }}" class="hidden sm:inline-flex items-center gap-2 bg-teal hover:bg-teal-deep text-white text-sm font-semibold px-4 py-2.5 rounded-lg"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex text-sm font-semibold px-4 py-2.5 rounded-lg border border-line hover:bg-mist">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center gap-2 bg-teal hover:bg-teal-deep text-white text-sm font-semibold px-4 py-2.5 rounded-lg">Doctor Register</a>
                    @endif
                @endauth
                <button id="menuBtn" class="md:hidden w-10 h-10 grid place-items-center rounded-lg border border-line" aria-label="Menu খুলুন" aria-expanded="false" aria-controls="mobileMenu">
                    <i class="fa-solid fa-bars" id="menuIcon"></i>
                </button>
            </div>
        </div>

        <div id="mobileMenu" class="md:hidden hidden border-t border-line bg-white">
            <nav class="px-4 py-3 grid gap-1 text-base font-medium" aria-label="Mobile">
                <a href="#features" class="menu-link px-3 py-3 rounded-lg hover:bg-mist">Features</a>
                <a href="#how" class="menu-link px-3 py-3 rounded-lg hover:bg-mist">কীভাবে শুরু করবেন</a>
                <a href="#pricing" class="menu-link px-3 py-3 rounded-lg hover:bg-mist">Pricing</a>
                <a href="#faq" class="menu-link px-3 py-3 rounded-lg hover:bg-mist">FAQ</a>
                <a href="#contact" class="menu-link px-3 py-3 rounded-lg hover:bg-mist">যোগাযোগ</a>
            </nav>
        </div>
    </header>

    {{-- ============ HERO ============ --}}
    <section class="bg-mist">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-10 pb-14 md:pt-16 md:pb-20 grid lg:grid-cols-[1.05fr_.95fr] gap-10 lg:gap-14 items-center">
            <div>
                <p class="inline-flex items-center gap-2 bg-mint text-teal-deep text-sm font-semibold px-3 py-1.5 rounded-full mb-5">
                    <i class="fa-solid fa-shield-heart"></i> BMDC verified ডাক্তারদের জন্য
                </p>
                <h1 class="font-display font-extrabold text-[2.1rem] leading-[1.2] sm:text-5xl sm:leading-[1.15] text-ink">
                    রোগী দেখুন, Prescription লিখুন, Print করুন। সব এক জায়গায়।
                </h1>
                <p class="mt-5 text-lg text-soft leading-relaxed max-w-xl">
                    রোগীর সমস্যা লিখলে AI সম্ভাব্য diagnosis ও medicine সাজেস্ট করে। আপনি এডিট করে নিজের chamber-এর ডিজাইনে Prescription Print বা PDF করেন, কয়েক ক্লিকে।
                </p>

                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex justify-center items-center gap-2 bg-teal hover:bg-teal-deep text-white font-semibold px-7 py-3.5 rounded-xl"><i class="fa-solid fa-gauge-high"></i> Dashboard-এ যান</a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex justify-center items-center gap-2 bg-teal hover:bg-teal-deep text-white font-semibold px-7 py-3.5 rounded-xl"><i class="fa-solid fa-user-doctor"></i> ডাক্তার হিসেবে Register করুন</a>
                        @endif
                        <a href="{{ route('login') }}" class="inline-flex justify-center items-center gap-2 bg-white border border-line hover:bg-mist font-semibold px-7 py-3.5 rounded-xl"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                    @endauth
                </div>

                <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-3 text-[15px] text-soft">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-lock text-teal"></i> Secure cloud</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-mobile-screen text-teal"></i> Mobile, tablet, PC</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-file-pdf text-teal"></i> Print ও PDF</li>
                </ul>
            </div>

            {{-- Live prescription demo --}}
            <div>
                <div class="flex gap-2 mb-3 overflow-x-auto pb-1" role="tablist" aria-label="Demo case">
                    <button class="case-tab shrink-0 text-sm font-semibold px-4 py-2 rounded-full border" role="tab" data-i="0">জ্বর</button>
                    <button class="case-tab shrink-0 text-sm font-semibold px-4 py-2 rounded-full border" role="tab" data-i="1">গ্যাস্ট্রিক ব্যথা</button>
                    <button class="case-tab shrink-0 text-sm font-semibold px-4 py-2 rounded-full border" role="tab" data-i="2">কাশি</button>
                </div>

                <div class="rx-paper bg-paper rounded-2xl border border-line shadow-xl shadow-teal/10 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 bg-white border-b border-line">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-full bg-mint text-teal grid place-items-center"><i class="fa-solid fa-user-doctor"></i></span>
                            <div class="leading-tight">
                                <p class="font-semibold">ডাঃ সাইদুর রহমান</p>
                                <p class="text-xs text-soft">MBBS, FCPS | Medicine Specialist</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold bg-mint text-teal-deep px-2.5 py-1 rounded-full">Demo</span>
                    </div>

                    <div id="rxBody" class="swap px-5 py-5 space-y-5 min-h-[330px]">
                        <div>
                            <p class="text-xs font-semibold text-soft mb-1">Chief complaint</p>
                            <p id="rxComplaint" class="caret text-[17px] font-medium min-h-[28px]"></p>
                        </div>
                        <div class="bg-mint rounded-xl px-4 py-3">
                            <p class="text-xs font-semibold text-teal-deep mb-0.5"><i class="fa-solid fa-wand-magic-sparkles"></i> AI diagnosis সাজেশন</p>
                            <p id="rxDx" class="font-display font-bold text-lg text-ink"></p>
                        </div>
                        <div>
                            <p class="font-display font-extrabold text-2xl text-teal leading-none mb-2">&#8478;</p>
                            <ul id="rxMeds" class="divide-y divide-line/80 text-[15px]"></ul>
                        </div>
                    </div>

                    <div class="px-5 py-4 bg-white border-t border-line flex items-center justify-between gap-3">
                        <p class="text-xs text-soft">চূড়ান্ত সিদ্ধান্ত সবসময় ডাক্তারের।</p>
                        <span class="inline-flex items-center gap-2 bg-ink text-white text-sm font-semibold px-4 py-2 rounded-lg shrink-0"><i class="fa-solid fa-print"></i> Print / PDF</span>
                    </div>
                </div>
                <p class="mt-3 text-xs text-soft">উপরের কেসগুলো শুধু demo। বাস্তব রোগীর তথ্য নয়।</p>
            </div>
        </div>
    </section>

    {{-- ============ FEATURES ============ --}}
    <section id="features" class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-24">
        <div class="max-w-2xl mb-10 md:mb-14">
            <h2 class="font-display font-extrabold text-3xl md:text-4xl leading-tight">রোগী দেখা থেকে Print পর্যন্ত, যা যা লাগে</h2>
            <p class="mt-3 text-soft text-lg">দিনে যত রোগীই দেখুন, প্রতিটা ধাপ থাকছে কয়েক ক্লিকের মধ্যে।</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <article class="reveal md:col-span-4 bg-ink text-white rounded-2xl p-6 md:p-8 flex flex-col justify-between gap-8">
                <div>
                    <i class="fa-solid fa-wand-magic-sparkles text-amber text-2xl"></i>
                    <h3 class="font-display font-bold text-2xl mt-4">AI Diagnosis ও Medicine সাজেশন</h3>
                    <p class="mt-3 text-white/75 leading-relaxed max-w-lg">রোগীর সমস্যা লিখুন। AI সম্ভাব্য diagnosis ও উপযুক্ত ওষুধ দেখাবে। আপনি যাচাই করে এডিট করবেন, তারপর Prescription-এ যোগ হবে।</p>
                </div>
                <p class="text-sm text-white/60">AI শুধু সহায়ক। সিদ্ধান্ত আপনার।</p>
            </article>

            <article class="reveal md:col-span-2 bg-mint rounded-2xl p-6">
                <i class="fa-solid fa-magnifying-glass text-teal text-xl"></i>
                <h3 class="font-bold text-lg mt-4">মোবাইল নম্বরে রোগী খুঁজুন</h3>
                <p class="mt-2 text-soft leading-relaxed">পুরোনো রোগীর তথ্য সাথে সাথে চলে আসবে। একই তথ্য বারবার টাইপ করতে হবে না।</p>
            </article>

            <article class="reveal md:col-span-2 bg-white border border-line rounded-2xl p-6">
                <i class="fa-solid fa-file-pen text-teal text-xl"></i>
                <h3 class="font-bold text-lg mt-4">নিজের Prescription Template</h3>
                <p class="mt-2 text-soft leading-relaxed">Chamber-এর নাম, logo ও ডিজাইন সেট করুন। প্রতিটা Print হবে আপনার নিজের ব্র্যান্ডে।</p>
            </article>

            <article class="reveal md:col-span-2 bg-white border border-line rounded-2xl p-6">
                <i class="fa-solid fa-print text-teal text-xl"></i>
                <h3 class="font-bold text-lg mt-4">Print ও PDF</h3>
                <p class="mt-2 text-soft leading-relaxed">এক ক্লিকে Print করুন, অথবা PDF করে WhatsApp বা email-এ রোগীকে পাঠান।</p>
            </article>

            <article class="reveal md:col-span-2 bg-white border border-line rounded-2xl p-6">
                <i class="fa-solid fa-wallet text-teal text-xl"></i>
                <h3 class="font-bold text-lg mt-4">Wallet billing</h3>
                <p class="mt-2 text-soft leading-relaxed">Recharge করুন। প্রতি Prescription ৳{{ $rxPrice }}, প্রতি AI সাজেশন ৳{{ $aiPrice }}, হিসাব এক জায়গায় দেখুন।</p>
            </article>

            <article class="reveal md:col-span-3 bg-white border border-line rounded-2xl p-6 flex gap-4">
                <i class="fa-solid fa-link text-teal text-xl mt-1"></i>
                <div>
                    <h3 class="font-bold text-lg">Public Profile link</h3>
                    <p class="mt-2 text-soft leading-relaxed">নিজের chamber, সময় ও পরিচিতি নিয়ে একটা shareable পেজ বানান। লিংক পাঠালেই রোগী দেখতে পারবে।</p>
                </div>
            </article>

            <article class="reveal md:col-span-3 bg-white border border-line rounded-2xl p-6 flex gap-4">
                <i class="fa-solid fa-cloud text-teal text-xl mt-1"></i>
                <div>
                    <h3 class="font-bold text-lg">যেকোনো ডিভাইসে কাজ</h3>
                    <p class="mt-2 text-soft leading-relaxed">Chamber, বাসা বা পথে, ফোন থেকেই login করে কাজ চালিয়ে যান। সব data cloud-এ থাকে।</p>
                </div>
            </article>
        </div>
    </section>

    {{-- ============ HOW IT STARTS ============ --}}
    <section id="how" class="bg-ink text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-24">
            <div class="max-w-2xl mb-10 md:mb-14">
                <h2 class="font-display font-extrabold text-3xl md:text-4xl leading-tight">চার ধাপে শুরু</h2>
                <p class="mt-3 text-white/70 text-lg">শুধু যাচাই করা ডাক্তাররাই সিস্টেম ব্যবহার করতে পারেন। তাই Register-এর পর একটা approval ধাপ আছে।</p>
            </div>

            <ol class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <li class="reveal rounded-2xl border border-white/15 p-6">
                    <span class="w-9 h-9 rounded-full bg-teal grid place-items-center font-bold">১</span>
                    <h3 class="font-bold text-lg mt-4">Register করুন</h3>
                    <p class="mt-2 text-white/70 leading-relaxed">BMDC নম্বর, ডিগ্রি, ছবি ও BMDC certificate দিয়ে ফর্ম পূরণ করুন।</p>
                </li>
                <li class="reveal rounded-2xl border border-white/15 p-6">
                    <span class="w-9 h-9 rounded-full bg-teal grid place-items-center font-bold">২</span>
                    <h3 class="font-bold text-lg mt-4">Verification</h3>
                    <p class="mt-2 text-white/70 leading-relaxed">Super Admin আপনার তথ্য ও certificate যাচাই করেন। Certificate private থাকে।</p>
                </li>
                <li class="reveal rounded-2xl border border-white/15 p-6">
                    <span class="w-9 h-9 rounded-full bg-teal grid place-items-center font-bold">৩</span>
                    <h3 class="font-bold text-lg mt-4">Approval</h3>
                    <p class="mt-2 text-white/70 leading-relaxed">Login করে Status পেজে অবস্থা দেখুন। Approve হলে পুরো সিস্টেম খুলে যাবে।</p>
                </li>
                <li class="reveal rounded-2xl border border-white/15 p-6">
                    <span class="w-9 h-9 rounded-full bg-amber text-ink grid place-items-center font-bold">৪</span>
                    <h3 class="font-bold text-lg mt-4">Prescription শুরু</h3>
                    <p class="mt-2 text-white/70 leading-relaxed">রোগী যোগ করুন, AI সাজেশন নিন, Print বা PDF করুন।</p>
                </li>
            </ol>

            @guest
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="mt-10 inline-flex items-center gap-2 bg-white text-ink font-semibold px-7 py-3.5 rounded-xl hover:bg-mint"><i class="fa-solid fa-user-doctor"></i> এখনই Register করুন</a>
                @endif
            @endguest
        </div>
    </section>

    {{-- ============ PRICING ============ --}}
    <section id="pricing" class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-24">
        <div class="max-w-2xl mb-10 md:mb-14">
            <h2 class="font-display font-extrabold text-3xl md:text-4xl leading-tight">ব্যবহার অনুযায়ী খরচ, আগে থেকেই জানা</h2>
            <p class="mt-3 text-soft text-lg">Wallet-এ balance রাখুন। Prescription তৈরি করলে ও AI সাজেশন নিলে সেই অনুযায়ী টাকা কাটা হয়।</p>
        </div>

        <div class="grid lg:grid-cols-2 gap-4 lg:gap-6">
            <div class="grid sm:grid-cols-2 lg:grid-cols-1 gap-4">
                <article class="reveal bg-mint rounded-2xl p-6 md:p-7 flex items-center gap-5">
                    <div class="shrink-0 text-center">
                        <p class="font-display font-extrabold text-5xl text-teal-deep leading-none">৳{{ $rxPrice }}</p>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">প্রতি Prescription</h3>
                        <p class="mt-1 text-soft leading-relaxed">একটা Prescription তৈরি করলে ৳{{ $rxPrice }} কাটা হয়। Print ও PDF এর মধ্যেই।</p>
                    </div>
                </article>

                <article class="reveal bg-ink text-white rounded-2xl p-6 md:p-7 flex items-center gap-5">
                    <div class="shrink-0 text-center">
                        <p class="font-display font-extrabold text-5xl text-amber leading-none">৳{{ $aiPrice }}</p>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg"><i class="fa-solid fa-wand-magic-sparkles text-amber mr-1"></i> প্রতি AI সাজেশন ক্লিক</h3>
                        <p class="mt-1 text-white/70 leading-relaxed">AI diagnosis বা medicine সাজেশন চাইলে প্রতিবার ৳{{ $aiPrice }}। না চাইলে কিছু কাটে না।</p>
                    </div>
                </article>

                <p class="sm:col-span-2 lg:col-span-1 text-soft">
                    উদাহরণ: একটা Prescription-এ ১ বার AI সাজেশন নিলে মোট <strong class="text-ink">৳{{ $rxPrice + $aiPrice }}</strong>।
                </p>
            </div>

            {{-- Cost calculator --}}
            <div class="reveal bg-white border border-line rounded-2xl p-6 md:p-7">
                <h3 class="font-display font-bold text-xl">মাসের খরচ হিসাব করুন</h3>
                <div class="mt-5 grid sm:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="text-sm font-semibold text-soft">মাসে কতটা Prescription?</span>
                        <input id="calcRx" type="number" inputmode="numeric" min="0" max="100000" value="100" class="mt-1.5 w-full rounded-lg border border-line px-3 py-3 text-lg font-semibold focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/30">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-soft">প্রতি Prescription-এ AI কতবার?</span>
                        <input id="calcAi" type="number" inputmode="numeric" min="0" max="50" value="1" class="mt-1.5 w-full rounded-lg border border-line px-3 py-3 text-lg font-semibold focus:border-teal focus:outline-none focus:ring-2 focus:ring-teal/30">
                    </label>
                </div>

                <dl class="mt-6 space-y-2 text-[15px]" aria-live="polite">
                    <div class="flex justify-between"><dt class="text-soft">Prescription খরচ</dt><dd id="outRx" class="font-semibold"></dd></div>
                    <div class="flex justify-between"><dt class="text-soft">AI সাজেশন খরচ</dt><dd id="outAi" class="font-semibold"></dd></div>
                    <div class="flex justify-between items-baseline border-t border-line pt-3 mt-3">
                        <dt class="font-bold">মোট আনুমানিক খরচ</dt>
                        <dd id="outTotal" class="font-display font-extrabold text-3xl text-teal-deep"></dd>
                    </div>
                </dl>
                <p class="mt-4 text-xs text-soft">এটা শুধু হিসাবের সুবিধার জন্য। আসল খরচ Wallet-এর লেনদেনে দেখা যাবে।</p>
            </div>
        </div>
    </section>

    {{-- ============ FAQ ============ --}}
    <section id="faq" class="max-w-3xl mx-auto px-4 sm:px-6 py-16 md:py-24">
        <h2 class="font-display font-extrabold text-3xl md:text-4xl mb-8">সাধারণ জিজ্ঞাসা</h2>

        <div class="space-y-3">
            <details class="bg-mist rounded-xl p-5">
                <summary class="flex items-center justify-between gap-4 cursor-pointer font-semibold">
                    Register করার পরই কি সিস্টেম ব্যবহার করা যাবে?
                    <i class="fa-solid fa-chevron-down chev text-teal text-sm transition-transform"></i>
                </summary>
                <p class="mt-3 text-soft leading-relaxed">না। Register-এর পর আপনার অ্যাকাউন্ট pending থাকে। Super Admin BMDC তথ্য ও certificate যাচাই করে approve করলে তবেই Prescription, রোগী ও অন্য ফিচার খুলবে। এর মধ্যে Login করে শুধু Status পেজ দেখা যাবে।</p>
            </details>
            <details class="bg-mist rounded-xl p-5">
                <summary class="flex items-center justify-between gap-4 cursor-pointer font-semibold">
                    আমার BMDC certificate কি অন্যরা দেখতে পাবে?
                    <i class="fa-solid fa-chevron-down chev text-teal text-sm transition-transform"></i>
                </summary>
                <p class="mt-3 text-soft leading-relaxed">না। Certificate একটা private ফোল্ডারে থাকে এবং শুধু Super Admin যাচাইয়ের জন্য খুলতে পারেন। Public profile-এ এটা দেখানো হয় না।</p>
            </details>
            <details class="bg-mist rounded-xl p-5">
                <summary class="flex items-center justify-between gap-4 cursor-pointer font-semibold">
                    AI diagnosis কি সবসময় ঠিক হবে?
                    <i class="fa-solid fa-chevron-down chev text-teal text-sm transition-transform"></i>
                </summary>
                <p class="mt-3 text-soft leading-relaxed">AI শুধু সাজেশন দেয়। কী লিখবেন আর কোন ওষুধ দেবেন, সেই সিদ্ধান্ত সবসময় আপনার। প্রতিটা সাজেশন আপনি এডিট বা বাদ দিতে পারবেন।</p>
            </details>
            <details class="bg-mist rounded-xl p-5">
                <summary class="flex items-center justify-between gap-4 cursor-pointer font-semibold">
                    নিজের Prescription Template ব্যবহার করা যাবে?
                    <i class="fa-solid fa-chevron-down chev text-teal text-sm transition-transform"></i>
                </summary>
                <p class="mt-3 text-soft leading-relaxed">হ্যাঁ। Chamber বা হাসপাতালের নাম, logo ও ডিজাইন সেট করলে প্রতিটা Print ও PDF-এ সেটাই ব্যবহার হবে।</p>
            </details>
            <details class="bg-mist rounded-xl p-5">
                <summary class="flex items-center justify-between gap-4 cursor-pointer font-semibold">
                    খরচ কীভাবে কাটা হয়?
                    <i class="fa-solid fa-chevron-down chev text-teal text-sm transition-transform"></i>
                </summary>
                <p class="mt-3 text-soft leading-relaxed">আপনার Wallet balance থেকে। প্রতি Prescription ৳{{ $rxPrice }} এবং প্রতিবার AI সাজেশন নিলে (ক্লিকে) ৳{{ $aiPrice }}। Balance শেষ হলে Recharge করে আবার চালিয়ে যেতে পারবেন।</p>
            </details>
        </div>
    </section>

    {{-- ============ CTA ============ --}}
    <section class="max-w-6xl mx-auto px-4 sm:px-6 pb-16 md:pb-24">
        <div class="bg-teal rounded-3xl px-6 py-12 md:py-14 text-center text-white">
            <h2 class="font-display font-extrabold text-3xl md:text-4xl leading-tight">আজই EasyDoc-এ শুরু করুন</h2>
            <p class="mt-3 text-white/85 max-w-xl mx-auto text-lg">নতুন হলে Register করুন, আগে থেকে অ্যাকাউন্ট থাকলে Login করুন।</p>
            <div class="mt-7 flex flex-col sm:flex-row gap-3 justify-center">
                @auth
                    <a href="{{ url('/dashboard') }}" class="inline-flex justify-center items-center gap-2 bg-white text-teal-deep font-semibold px-7 py-3.5 rounded-xl hover:bg-mint"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                @else
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex justify-center items-center gap-2 bg-white text-teal-deep font-semibold px-7 py-3.5 rounded-xl hover:bg-mint"><i class="fa-solid fa-user-doctor"></i> Doctor Register</a>
                    @endif
                    <a href="{{ route('login') }}" class="inline-flex justify-center items-center gap-2 border border-white/60 font-semibold px-7 py-3.5 rounded-xl hover:bg-white/10"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                @endauth
            </div>
        </div>
    </section>

    {{-- ============ FOOTER ============ --}}
    <footer id="contact" class="border-t border-line">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 grid sm:grid-cols-2 md:grid-cols-3 gap-10">
            <div>
                <div class="flex items-center gap-2.5 mb-3">
                    <span class="w-9 h-9 rounded-lg bg-teal text-white grid place-items-center font-display font-extrabold">ED</span>
                    <span class="font-display font-extrabold text-lg">EasyDoc</span>
                </div>
                <p class="text-soft leading-relaxed">ডাক্তারদের জন্য AI-সহায়ক ডিজিটাল Prescription Platform। সহজ, দ্রুত ও নিরাপদ।</p>
            </div>
            <div>
                <h4 class="font-bold mb-3">Links</h4>
                <ul class="space-y-2 text-soft">
                    <li><a href="#features" class="hover:text-teal">Features</a></li>
                    <li><a href="#how" class="hover:text-teal">কীভাবে শুরু করবেন</a></li>
                    <li><a href="#pricing" class="hover:text-teal">Pricing</a></li>
                    <li><a href="#faq" class="hover:text-teal">FAQ</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-teal">Login</a></li>
                    @if (Route::has('register'))
                        <li><a href="{{ route('register') }}" class="hover:text-teal">Doctor Register</a></li>
                    @endif
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-3">যোগাযোগ</h4>
                <ul class="space-y-2 text-soft">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-envelope text-teal w-4"></i> support@easydoc.app</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-phone text-teal w-4"></i> +8801XXXXXXXXX</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-teal w-4"></i> Dhaka, Bangladesh</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-line py-5 text-center text-sm text-soft">&copy; {{ date('Y') }} EasyDoc. All rights reserved.</div>
        <div class="h-16 md:hidden"></div>
    </footer>

    {{-- Mobile sticky action bar --}}
    <div class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur border-t border-line px-4 pt-3 grid gap-3 {{ auth()->check() ? 'grid-cols-1' : 'grid-cols-2' }}" style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px))">
        @auth
            <a href="{{ url('/dashboard') }}" class="text-center bg-teal text-white font-semibold py-3 rounded-xl">Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="text-center border border-line font-semibold py-3 rounded-xl">Login</a>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="text-center bg-teal text-white font-semibold py-3 rounded-xl">Register</a>
            @else
                <a href="#features" class="text-center bg-teal text-white font-semibold py-3 rounded-xl">Features</a>
            @endif
        @endauth
    </div>

    <script>
    (function () {
        // Mobile menu
        var btn = document.getElementById('menuBtn'), menu = document.getElementById('mobileMenu'), icon = document.getElementById('menuIcon');
        function setMenu(open) {
            menu.classList.toggle('hidden', !open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            icon.className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        }
        btn.addEventListener('click', function () { setMenu(menu.classList.contains('hidden')); });
        document.querySelectorAll('.menu-link').forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });

        // Scroll reveal
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var items = document.querySelectorAll('.reveal');
        if ('IntersectionObserver' in window && !reduce) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
            }, { threshold: 0.12 });
            items.forEach(function (el) { io.observe(el); });
        } else { items.forEach(function (el) { el.classList.add('in'); }); }

        // Cost calculator
        var PRICE_RX = {{ (int) $rxPrice }}, PRICE_AI = {{ (int) $aiPrice }};
        var inRx = document.getElementById('calcRx'), inAi = document.getElementById('calcAi');
        var oRx = document.getElementById('outRx'), oAi = document.getElementById('outAi'), oT = document.getElementById('outTotal');
        function fmt(n) { return '\u09F3' + new Intl.NumberFormat('en-US').format(n); }
        function calc() {
            var rx = Math.max(0, Math.min(100000, parseInt(inRx.value, 10) || 0));
            var ai = Math.max(0, Math.min(50, parseInt(inAi.value, 10) || 0));
            var a = rx * PRICE_RX, b = rx * ai * PRICE_AI;
            oRx.textContent = rx + ' \u00D7 ' + fmt(PRICE_RX) + ' = ' + fmt(a);
            oAi.textContent = (rx * ai) + ' \u00D7 ' + fmt(PRICE_AI) + ' = ' + fmt(b);
            oT.textContent = fmt(a + b);
        }
        inRx.addEventListener('input', calc); inAi.addEventListener('input', calc); calc();

        // Live prescription demo (sample data only)
        var cases = [
            { c: '৩ দিন ধরে জ্বর ও গা ব্যথা', dx: 'Viral fever', m: [['Paracetamol 500 mg', '১+১+১ | ৩ দিন'], ['ORS', 'প্রয়োজনমতো'], ['বিশ্রাম ও প্রচুর পানি', '']] },
            { c: 'খাওয়ার পর পেটের ওপরের অংশে জ্বালা', dx: 'Gastritis', m: [['Omeprazole 20 mg', '১+০+১ | ১৪ দিন'], ['Antacid syrup', 'খাওয়ার ১ ঘণ্টা পর'], ['তেল-মসলা কম খাওয়া', '']] },
            { c: 'শুকনো কাশি ও গলা ব্যথা', dx: 'Upper respiratory infection', m: [['Cough syrup', '১ চামচ × ৩ বার'], ['Lozenge', 'প্রয়োজনমতো'], ['কুসুম গরম পানি', '']] }
        ];
        var tabs = document.querySelectorAll('.case-tab'), body = document.getElementById('rxBody');
        var elC = document.getElementById('rxComplaint'), elD = document.getElementById('rxDx'), elM = document.getElementById('rxMeds');
        var cur = 0, typer = null, auto = null;

        function paintTabs() {
            tabs.forEach(function (t, i) {
                var on = i === cur;
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.className = 'case-tab shrink-0 text-sm font-semibold px-4 py-2 rounded-full border ' + (on ? 'bg-teal text-white border-teal' : 'bg-white text-soft border-line hover:bg-mist');
            });
        }
        function fill(i, instant) {
            var k = cases[i]; clearInterval(typer);
            elD.textContent = k.dx;
            elM.innerHTML = k.m.map(function (x) {
                return '<li class="py-2 flex justify-between gap-3"><span class="font-semibold">' + x[0] + '</span><span class="text-soft text-right">' + x[1] + '</span></li>';
            }).join('');
            if (instant || reduce) { elC.textContent = k.c; elC.classList.remove('caret'); return; }
            elC.classList.add('caret'); elC.textContent = ''; var n = 0;
            typer = setInterval(function () {
                n++; elC.textContent = k.c.slice(0, n);
                if (n >= k.c.length) { clearInterval(typer); elC.classList.remove('caret'); }
            }, 45);
        }
        function show(i, instant) {
            cur = i; paintTabs();
            if (instant || reduce) { fill(i, true); return; }
            body.classList.add('out');
            setTimeout(function () { fill(i, false); body.classList.remove('out'); }, 300);
        }
        tabs.forEach(function (t) {
            t.addEventListener('click', function () { clearInterval(auto); auto = null; show(parseInt(t.dataset.i, 10), false); });
        });
        show(0, true);
        if (!reduce) { auto = setInterval(function () { show((cur + 1) % cases.length, false); }, 6500); }
    })();
    </script>
</body>
</html>