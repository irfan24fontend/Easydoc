<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'EasyDoc') }} | Prescriptions made simple for doctors</title>
    <meta name="description" content="Write, print and track prescriptions, manage chambers and keep patient records in one place.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Hind+Siliguri:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- Self-contained: no @vite needed, so this page works even without a Vite build --}}
    <style>
        :root {
            --ink: #10262b;
            --ink-soft: #46606a;
            --teal: #0c8c84;
            --teal-deep: #0a6b65;
            --mint: #e3f3f0;
            --mist: #f3f7f7;
            --line: #d8e4e4;
            --amber: #f4b63f;
            --warn-bg: #fff3dc;
            --warn-ink: #7a4a00;
            --white: #ffffff;
            --radius-lg: 22px;
            --radius-md: 14px;
            --radius-sm: 8px;
            --font-display: "Bricolage Grotesque", "Hind Siliguri", system-ui, sans-serif;
            --font-body: "Hind Siliguri", system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: var(--font-body);
            font-size: 17px;
            line-height: 1.65;
            color: var(--ink);
            background: var(--mist);
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3 { font-family: var(--font-display); line-height: 1.12; margin: 0; letter-spacing: -0.02em; }
        p { margin: 0; }
        a { color: inherit; }
        :focus-visible { outline: 3px solid var(--amber); outline-offset: 3px; border-radius: 6px; }

        .wrap { width: min(1160px, 100% - 40px); margin-inline: auto; }

        /* ---------- Buttons ---------- */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 13px 24px; border-radius: 999px; font: 600 16px/1 var(--font-body);
            text-decoration: none; border: 2px solid transparent; cursor: pointer;
            transition: background .2s, border-color .2s, transform .2s;
        }
        .btn-primary { background: var(--teal); color: var(--white); }
        .btn-primary:hover { background: var(--teal-deep); }
        .btn-ghost { background: transparent; color: var(--ink); border-color: var(--ink); }
        .btn-ghost:hover { background: var(--ink); color: var(--white); }
        .btn-light { background: var(--white); color: var(--teal-deep); }
        .btn-light:hover { background: var(--mint); }
        .btn-small { padding: 10px 18px; font-size: 15px; }

        /* ---------- Nav ---------- */
        .nav { position: sticky; top: 0; z-index: 20; background: rgba(243, 247, 247, .92); backdrop-filter: blur(10px); border-bottom: 1px solid var(--line); }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; height: 68px; }
        .brand { display: flex; align-items: center; gap: 10px; font: 800 22px var(--font-display); text-decoration: none; letter-spacing: -0.03em; }
        .brand-mark { width: 34px; height: 34px; border-radius: 10px; background: var(--teal); display: grid; place-items: center; }
        .nav-links { display: flex; gap: 30px; }
        .nav-links a { text-decoration: none; font-weight: 500; color: var(--ink-soft); }
        .nav-links a:hover { color: var(--ink); }
        .nav-actions { display: flex; gap: 10px; align-items: center; }

        /* ---------- Hero ---------- */
        .hero { padding: 72px 0 88px; }
        .hero-grid { display: grid; grid-template-columns: 1.05fr .95fr; gap: 56px; align-items: center; }
        .hero h1 { font-size: clamp(38px, 5.4vw, 66px); font-weight: 800; }
        .hero-bn { margin-top: 14px; font-size: 20px; color: var(--teal-deep); font-weight: 500; }
        .hero-lead { margin-top: 20px; max-width: 520px; color: var(--ink-soft); font-size: 19px; }
        .hero-cta { margin-top: 32px; display: flex; flex-wrap: wrap; gap: 12px; }
        .hero-note { margin-top: 18px; font-size: 15px; color: var(--ink-soft); }

        /* Prescription demo */
        .rx-stage { position: relative; }
        .rx {
            background: var(--white); border-radius: var(--radius-lg); padding: 26px 26px 22px;
            border: 1px solid var(--line); box-shadow: 0 30px 60px -30px rgba(16, 38, 43, .35);
            transform: rotate(1.2deg);
        }
        .rx-head { display: flex; justify-content: space-between; gap: 16px; padding-bottom: 16px; border-bottom: 2px solid var(--ink); }
        .rx-doc strong { display: block; font: 700 18px var(--font-display); }
        .rx-doc span { font-size: 14px; color: var(--ink-soft); }
        .rx-meta { text-align: right; font-size: 14px; color: var(--ink-soft); }
        .rx-symbol { font: 800 40px/1 var(--font-display); color: var(--teal); margin: 16px 0 6px; }
        .rx-list { list-style: none; margin: 0; padding: 0; }
        .rx-list li { display: flex; justify-content: space-between; gap: 12px; padding: 11px 0; border-bottom: 1px dashed var(--line); font-weight: 500; }
        .rx-list li span { color: var(--ink-soft); font-weight: 400; font-size: 15px; white-space: nowrap; }
        .rx-check { margin-top: 16px; border-radius: var(--radius-md); padding: 13px 15px; font-size: 15px; display: flex; gap: 10px; align-items: flex-start; }
        .rx-check.ok { background: var(--mint); color: var(--teal-deep); }
        .rx-check.warn { background: var(--warn-bg); color: var(--warn-ink); }
        .rx-check svg { flex: none; margin-top: 3px; }
        .rx-actions { margin-top: 16px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .rx-try {
            font: 600 14px var(--font-body); padding: 9px 15px; border-radius: 999px;
            border: 2px dashed var(--teal); background: transparent; color: var(--teal-deep); cursor: pointer;
        }
        .rx-try:hover { background: var(--mint); }
        .rx-demo-label { font-size: 13px; color: var(--ink-soft); }

        /* ---------- Section shell ---------- */
        section { scroll-margin-top: 80px; }
        .section { padding: 88px 0; }
        .section-head { max-width: 640px; margin-bottom: 48px; }
        .section-head h2 { font-size: clamp(30px, 3.8vw, 44px); font-weight: 800; }
        .section-head p { margin-top: 14px; color: var(--ink-soft); font-size: 18px; }

        /* ---------- Features (bento) ---------- */
        .bento { display: grid; grid-template-columns: repeat(6, 1fr); gap: 18px; }
        .tile { background: var(--white); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 28px; }
        .tile h3 { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        .tile p { color: var(--ink-soft); font-size: 16px; }
        .tile .icon { width: 46px; height: 46px; border-radius: 12px; background: var(--mint); display: grid; place-items: center; margin-bottom: 18px; color: var(--teal-deep); }
        .t-a { grid-column: span 4; background: var(--ink); border-color: var(--ink); color: var(--white); display: grid; grid-template-columns: 1.1fr .9fr; gap: 28px; align-items: center; }
        .t-a p { color: #b9cfd3; }
        .t-a .icon { background: rgba(255, 255, 255, .1); color: var(--amber); }
        .t-a ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
        .t-a li { background: rgba(255, 255, 255, .07); border-radius: var(--radius-sm); padding: 10px 14px; font-size: 15px; color: #e6f1f2; }
        .t-b { grid-column: span 2; background: var(--mint); border-color: var(--mint); }
        .t-c, .t-d, .t-e { grid-column: span 2; }
        .t-f { grid-column: span 6; display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
        .t-f > div { max-width: 640px; }

        /* ---------- Steps ---------- */
        .steps-band { background: var(--white); border-block: 1px solid var(--line); }
        .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; counter-reset: s; }
        .step { padding: 0 32px 0 0; position: relative; }
        .step + .step { padding-left: 32px; border-left: 1px solid var(--line); }
        .step::before {
            counter-increment: s; content: counter(s);
            display: inline-grid; place-items: center; width: 40px; height: 40px; border-radius: 50%;
            background: var(--teal); color: var(--white); font: 700 18px var(--font-display); margin-bottom: 18px;
        }
        .step h3 { font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        .step p { color: var(--ink-soft); }

        /* ---------- Reviews ---------- */
        .reviews-grid { display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 18px; align-items: start; }
        .review { background: var(--white); border: 1px solid var(--line); border-radius: var(--radius-lg); padding: 28px; }
        .review.big { background: var(--teal); border-color: var(--teal); color: var(--white); }
        .review.big .who span { color: #cdeeea; }
        .stars { display: flex; gap: 3px; color: var(--amber); margin-bottom: 14px; }
        .review blockquote { margin: 0; font-size: 18px; line-height: 1.6; }
        .review.big blockquote { font: 600 24px/1.4 var(--font-display); letter-spacing: -0.01em; }
        .who { margin-top: 22px; display: flex; align-items: center; gap: 12px; }
        .avatar { width: 44px; height: 44px; border-radius: 50%; display: grid; place-items: center; font: 700 16px var(--font-display); background: var(--mint); color: var(--teal-deep); flex: none; }
        .review.big .avatar { background: rgba(255, 255, 255, .18); color: var(--white); }
        .who strong { display: block; font-size: 16px; line-height: 1.3; }
        .who span { font-size: 14px; color: var(--ink-soft); }
        .reviews-grid .col { display: grid; gap: 18px; }

        /* ---------- FAQ ---------- */
        .faq { max-width: 780px; }
        .faq details { border-bottom: 1px solid var(--line); padding: 20px 0; }
        .faq summary { cursor: pointer; font: 700 19px var(--font-display); list-style: none; display: flex; justify-content: space-between; gap: 20px; align-items: center; }
        .faq summary::-webkit-details-marker { display: none; }
        .faq summary::after { content: "+"; font-size: 26px; color: var(--teal); transition: transform .2s; }
        .faq details[open] summary::after { transform: rotate(45deg); }
        .faq details p { margin-top: 12px; color: var(--ink-soft); max-width: 640px; }

        /* ---------- CTA ---------- */
        .cta { background: var(--teal-deep); color: var(--white); border-radius: 32px; padding: 64px 48px; text-align: center; }
        .cta h2 { font-size: clamp(30px, 4vw, 46px); font-weight: 800; max-width: 680px; margin: 0 auto; }
        .cta p { margin: 16px auto 30px; max-width: 520px; color: #c9ece8; font-size: 18px; }
        .cta-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .cta .btn-outline { border-color: rgba(255, 255, 255, .5); color: var(--white); }
        .cta .btn-outline:hover { background: rgba(255, 255, 255, .12); }

        /* ---------- Footer ---------- */
        footer { padding: 56px 0 40px; color: var(--ink-soft); font-size: 15px; }
        .foot { display: flex; justify-content: space-between; gap: 24px; flex-wrap: wrap; align-items: center; }
        .foot nav { display: flex; gap: 22px; }
        .foot a { text-decoration: none; }
        .foot a:hover { color: var(--ink); }

        /* One entrance moment: the prescription settles into place */
        @keyframes settle { from { opacity: 0; transform: translateY(18px) rotate(3deg); } to { opacity: 1; transform: translateY(0) rotate(1.2deg); } }
        .rx { animation: settle .8s cubic-bezier(.2, .7, .2, 1) both; }
        @media (prefers-reduced-motion: reduce) {
            .rx { animation: none; }
            html { scroll-behavior: auto; }
            * { transition: none !important; }
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 980px) {
            .hero-grid { grid-template-columns: 1fr; gap: 44px; }
            .nav-links { display: none; }
            .t-a { grid-column: span 6; grid-template-columns: 1fr; }
            .t-b, .t-c, .t-d, .t-e { grid-column: span 3; }
            .reviews-grid { grid-template-columns: 1fr; }
            .steps { grid-template-columns: 1fr; gap: 36px; }
            .step, .step + .step { padding: 0; border-left: 0; }
        }
        @media (max-width: 640px) {
            body { font-size: 16px; }
            .hero { padding: 44px 0 56px; }
            .section { padding: 60px 0; }
            .t-b, .t-c, .t-d, .t-e { grid-column: span 6; }
            .cta { padding: 44px 22px; border-radius: 24px; }
            .nav-actions .btn-ghost { display: none; }
            .rx { transform: none; }
        }
    </style>
</head>
<body>

@php
    $hasLogin = Route::has('login');
    $hasRegister = Route::has('register');
    $dashboardUrl = Route::has('dashboard') ? route('dashboard') : url('/');
@endphp

<header class="nav">
    <div class="wrap nav-inner">
        <a href="{{ url('/') }}" class="brand" aria-label="{{ config('app.name', 'EasyDoc') }} home">
            <span class="brand-mark" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            </span>
            {{ config('app.name', 'EasyDoc') }}
        </a>

        <nav class="nav-links" aria-label="Main">
            <a href="#features">Features</a>
            <a href="#how">How it works</a>
            <a href="#reviews">Doctor reviews</a>
            <a href="#faq">FAQ</a>
        </nav>

        <div class="nav-actions">
            @auth
                <a href="{{ $dashboardUrl }}" class="btn btn-primary btn-small">Open dashboard</a>
            @else
                @if ($hasLogin)
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-small">Log in</a>
                @endif
                @if ($hasRegister)
                    <a href="{{ route('register') }}" class="btn btn-primary btn-small">Create account</a>
                @endif
            @endauth
        </div>
    </div>
</header>

<main>
    {{-- ================= HERO ================= --}}
    <section class="hero">
        <div class="wrap hero-grid">
            <div>
                <h1>Write the prescription. Skip the paperwork.</h1>
                <p class="hero-bn">প্রেসক্রিপশন লিখুন সহজে, রোগীর তথ্য রাখুন এক জায়গায়।</p>
                <p class="hero-lead">
                    {{ config('app.name', 'EasyDoc') }} helps doctors write, print and track prescriptions, run more than one chamber, and keep every patient record organised.
                </p>
                <div class="hero-cta">
                    @auth
                        <a href="{{ $dashboardUrl }}" class="btn btn-primary">Open dashboard</a>
                    @else
                        @if ($hasRegister)
                            <a href="{{ route('register') }}" class="btn btn-primary">Create a doctor account</a>
                        @endif
                        @if ($hasLogin)
                            <a href="{{ route('login') }}" class="btn btn-ghost">Log in</a>
                        @endif
                    @endauth
                </div>
                <p class="hero-note">Made for doctors in Bangladesh. Works on any phone, tablet or computer.</p>
            </div>

            {{-- Interactive prescription: shows the interaction check in action --}}
            <div class="rx-stage">
                <div class="rx" aria-label="Sample prescription">
                    <div class="rx-head">
                        <div class="rx-doc">
                            <strong>Dr. Sample Name</strong>
                            <span>MBBS, Medicine</span>
                        </div>
                        <div class="rx-meta">Patient: Sample, 42 y<br>Chamber: Dhanmondi</div>
                    </div>
                    <div class="rx-symbol" aria-hidden="true">Rx</div>
                    <ul class="rx-list" id="rxList">
                        <li>Ibuprofen 400 mg <span>1+0+1, after meal</span></li>
                        <li>Omeprazole 20 mg <span>1+0+0, before meal</span></li>
                    </ul>

                    <div class="rx-check ok" id="rxCheck" role="status" aria-live="polite">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        <span id="rxCheckText">No conflicts found between these medicines.</span>
                    </div>

                    <div class="rx-actions">
                        <button type="button" class="rx-try" id="rxToggle">Try adding Aspirin 75 mg</button>
                        <span class="rx-demo-label">Sample data for demonstration</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= FEATURES ================= --}}
    <section id="features" class="section" style="padding-top:24px">
        <div class="wrap">
            <div class="section-head">
                <h2>Everything a chamber needs, nothing it doesn't</h2>
                <p>From the first patient of the day to the last invoice, each tool is built around how doctors really work.</p>
            </div>

            <div class="bento">
                <article class="tile t-a">
                    <div>
                        <div class="icon" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M9 13h6M9 17h4"/></svg>
                        </div>
                        <h3>A prescription builder that keeps up with you</h3>
                        <p>Search medicines, set dose and duration, and reuse your own templates for common cases. Add investigations and advice in the same screen.</p>
                    </div>
                    <ul>
                        <li>Save templates for your usual treatments</li>
                        <li>Syrup, tablet and other dose formats</li>
                        <li>Investigation advice included</li>
                    </ul>
                </article>

                <article class="tile t-b">
                    <div class="icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.4 8.4 8 9 4.6-.6 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <h3>Interaction check</h3>
                    <p>See a warning when chosen medicines may conflict, and a clear note when they don't.</p>
                </article>

                <article class="tile t-c">
                    <div class="icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M18.4 5.6l-2.1 2.1M7.7 16.3l-2.1 2.1"/></svg>
                    </div>
                    <h3>AI suggestions</h3>
                    <p>Get diagnosis and medicine ideas while you write. They are suggestions only; you decide what goes on the prescription.</p>
                </article>

                <article class="tile t-d">
                    <div class="icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
                    </div>
                    <h3>Multiple chambers</h3>
                    <p>Keep each chamber's address, timing and patients separate, and switch between them in one tap.</p>
                </article>

                <article class="tile t-e">
                    <div class="icon" aria-hidden="true">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 15h2"/></svg>
                    </div>
                    <h3>Wallet and invoices</h3>
                    <p>Recharge your wallet, see every transaction, and get an invoice each time your balance is topped up.</p>
                </article>

                <article class="tile t-f">
                    <div>
                        <h3>Print-ready, every time</h3>
                        <p>Prescriptions print cleanly on plain or letterhead paper, with your name, registration and chamber details already in place.</p>
                    </div>
                    @auth
                        <a href="{{ $dashboardUrl }}" class="btn btn-primary">Go to dashboard</a>
                    @else
                        @if ($hasRegister)
                            <a href="{{ route('register') }}" class="btn btn-primary">Start writing prescriptions</a>
                        @endif
                    @endauth
                </article>
            </div>
        </div>
    </section>

    {{-- ================= HOW IT WORKS ================= --}}
    <section id="how" class="steps-band">
        <div class="wrap section">
            <div class="section-head">
                <h2>Ready in three steps</h2>
                <p>No installation and no training session. Sign up and start with your next patient.</p>
            </div>
            <div class="steps">
                <div class="step">
                    <h3>Create your account</h3>
                    <p>Add your name, degrees and registration number. Add your chamber details once.</p>
                </div>
                <div class="step">
                    <h3>Write a prescription</h3>
                    <p>Pick the patient, choose medicines, review the interaction check and add advice.</p>
                </div>
                <div class="step">
                    <h3>Print and keep the record</h3>
                    <p>Print the prescription and find it later from the patient's history.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= DOCTOR REVIEWS ================= --}}
    {{-- NOTE: these are SAMPLE reviews. Replace with real doctor feedback before going public. --}}
    <section id="reviews" class="section">
        <div class="wrap">
            <div class="section-head">
                <h2>What doctors say</h2>
                <p>Feedback from doctors using {{ config('app.name', 'EasyDoc') }} in their daily practice.</p>
            </div>

            <div class="reviews-grid">
                <figure class="review big" style="margin:0">
                    <div class="stars" aria-label="5 out of 5 stars">
                        @for ($i = 0; $i < 5; $i++)
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9L12 17.8 5.8 21.1 7 14.2 2 9.3l6.9-1z"/></svg>
                        @endfor
                    </div>
                    <blockquote>I used to spend the evening rewriting notes. Now the prescription is done while the patient is still sitting in front of me.</blockquote>
                    <figcaption class="who">
                        <span class="avatar" aria-hidden="true">RA</span>
                        <div><strong>Dr. Rahim Ahmed</strong><span>General Physician, Dhaka</span></div>
                    </figcaption>
                </figure>

                <div class="col">
                    <figure class="review" style="margin:0">
                        <div class="stars" aria-label="5 out of 5 stars">
                            @for ($i = 0; $i < 5; $i++)
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9L12 17.8 5.8 21.1 7 14.2 2 9.3l6.9-1z"/></svg>
                            @endfor
                        </div>
                        <blockquote>Templates save me the most time. My common cases take under a minute.</blockquote>
                        <figcaption class="who">
                            <span class="avatar" aria-hidden="true">NS</span>
                            <div><strong>Dr. Nusrat Sultana</strong><span>Gynaecologist, Chattogram</span></div>
                        </figcaption>
                    </figure>
                </div>

                <div class="col">
                    <figure class="review" style="margin:0">
                        <div class="stars" aria-label="4 out of 5 stars">
                            @for ($i = 0; $i < 4; $i++)
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9L12 17.8 5.8 21.1 7 14.2 2 9.3l6.9-1z"/></svg>
                            @endfor
                        </div>
                        <blockquote>I run two chambers, and keeping their patients separate is now effortless.</blockquote>
                        <figcaption class="who">
                            <span class="avatar" aria-hidden="true">MH</span>
                            <div><strong>Dr. Mahmud Hasan</strong><span>Paediatrician, Sylhet</span></div>
                        </figcaption>
                    </figure>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section id="faq" class="section" style="padding-top:0">
        <div class="wrap">
            <div class="section-head">
                <h2>Common questions</h2>
            </div>
            <div class="faq">
                <details>
                    <summary>Does the AI write the prescription for me?</summary>
                    <p>No. The AI only offers suggestions for diagnosis and medicines. Nothing is added to a prescription until you choose it, and the final decision is always yours.</p>
                </details>
                <details>
                    <summary>How does the wallet work?</summary>
                    <p>You recharge your wallet and the balance is used for the platform's services. Every recharge and charge appears in your transaction list, and each recharge comes with an invoice.</p>
                </details>
                <details>
                    <summary>Can I work from more than one chamber?</summary>
                    <p>Yes. Add each chamber with its own address and timing, then choose the chamber when you write a prescription.</p>
                </details>
                <details>
                    <summary>Is patient information kept private?</summary>
                    <p>Patient records are visible only to the doctor who created them and are kept behind a secure login.</p>
                </details>
                <details>
                    <summary>What do I need to get started?</summary>
                    <p>A phone or computer with internet, and your registration details. There is nothing to install.</p>
                </details>
            </div>
        </div>
    </section>

    {{-- ================= CTA ================= --}}
    <section class="section" style="padding-top:0">
        <div class="wrap">
            <div class="cta">
                <h2>See your next patient with less paperwork</h2>
                <p>Create your account in a few minutes and write your first prescription today.</p>
                <div class="cta-actions">
                    @auth
                        <a href="{{ $dashboardUrl }}" class="btn btn-light">Open dashboard</a>
                    @else
                        @if ($hasRegister)
                            <a href="{{ route('register') }}" class="btn btn-light">Create a doctor account</a>
                        @endif
                        @if ($hasLogin)
                            <a href="{{ route('login') }}" class="btn btn-outline">Log in</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="wrap foot">
        <span>&copy; {{ date('Y') }} {{ config('app.name', 'EasyDoc') }}. All rights reserved.</span>
        <nav aria-label="Footer">
            <a href="#features">Features</a>
            <a href="#reviews">Reviews</a>
            <a href="#faq">FAQ</a>
        </nav>
    </div>
</footer>

<script>
    (function () {
        var btn = document.getElementById('rxToggle');
        var list = document.getElementById('rxList');
        var box = document.getElementById('rxCheck');
        var text = document.getElementById('rxCheckText');
        if (!btn || !list || !box || !text) return;

        var added = false;
        var extra = document.createElement('li');
        extra.innerHTML = 'Aspirin 75 mg <span>0+1+0, after meal</span>';

        btn.addEventListener('click', function () {
            added = !added;
            if (added) {
                list.appendChild(extra);
                box.className = 'rx-check warn';
                text.textContent = 'Ibuprofen and Aspirin taken together may raise the risk of stomach bleeding. Review before prescribing.';
                btn.textContent = 'Remove Aspirin 75 mg';
            } else {
                list.removeChild(extra);
                box.className = 'rx-check ok';
                text.textContent = 'No conflicts found between these medicines.';
                btn.textContent = 'Try adding Aspirin 75 mg';
            }
        });
    })();
</script>
</body>
</html>