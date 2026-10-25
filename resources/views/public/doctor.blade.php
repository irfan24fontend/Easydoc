@php
    $str       = \Illuminate\Support\Str::class;
    $name      = $doctor->displayName();
    $photo     = $doctor->photoUrl();
    $url       = $profile->url();
    $initials  = strtoupper($str::substr(trim(preg_replace('/^dr\.?\s+/i', '', $name)), 0, 2));
    $expertise = $profile->expertise ?? [];
    $languages = $profile->languages ?? [];
    $education = $profile->education ?? [];
    $workExp   = $profile->work_experience ?? [];
    $title     = $name . ' | ' . $doctor->speciality . ' | ' . config('app.name', 'EasyDoc');
    $desc      = $str::limit($profile->tagline . '. ' . strip_tags($profile->about), 155);
    $phone     = $profile->show_contact ? ($doctor->user->phone ?? null) : null;
    $email     = $profile->show_contact ? ($doctor->user->email ?? null) : null;
    $firstChamber = $chambers->first();

    $schema = array_filter([
        '@context'         => 'https://schema.org',
        '@type'            => 'Physician',
        'name'             => $name,
        'url'              => $url,
        'image'            => $photo,
        'medicalSpecialty' => $doctor->speciality,
        'description'      => $profile->tagline,
    ]);

    $svg = fn (string $path) => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
    $ico = [
        'id'    => $svg('<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="11" r="2"/><path d="M14 10h4M14 14h4M6 16c.5-1.5 5.5-1.5 6 0"/>'),
        'spec'  => $svg('<path d="M12 3v18M3 12h18"/>'),
        'work'  => $svg('<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>'),
        'lang'  => $svg('<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14.5 0 18M12 3c-3 3.5-3 14.5 0 18"/>'),
        'fee'   => $svg('<rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 15h2"/>'),
        'phone' => $svg('<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>'),
        'mail'  => $svg('<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>'),
        'link'  => $svg('<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>'),
        'pin'   => $svg('<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>'),
        'cal'   => $svg('<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/>'),
        'clock' => $svg('<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'),
        'share' => $svg('<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>'),
        'nav'   => $svg('<path d="m3 11 19-9-9 19-2-8z"/>'),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#4c1d95">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $desc }}">
    <link rel="canonical" href="{{ $url }}">
    @if ($isPreview)<meta name="robots" content="noindex">@endif

    <meta property="og:type" content="profile">
    <meta property="og:title" content="{{ $name }} - {{ $doctor->speciality }}">
    <meta property="og:description" content="{{ $desc }}">
    <meta property="og:url" content="{{ $url }}">
    @if ($photo)<meta property="og:image" content="{{ $photo }}">@endif
    <meta name="twitter:card" content="summary">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>

    <script>
        // theme আগেই বসিয়ে দিই, যাতে পেজ লোডে ঝিকমিক না করে
        (function () {
            try {
                var t = localStorage.getItem('pp-theme');
                if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>

    <style>
        :root{
            --p1:#7c3aed; --p2:#06b6d4; --p3:#ec4899;
            --ink:#0f172a; --ink-soft:#475569; --mute:#94a3b8;
            --bg:#f5f3ff; --glass:rgba(255,255,255,.66); --glass-b:rgba(255,255,255,.85); --line:rgba(124,58,237,.16);
            --chip:rgba(124,58,237,.10); --chip-ink:#5b21b6; --shadow:0 18px 50px rgba(76,29,149,.14);
            --display:"Plus Jakarta Sans","Hind Siliguri",system-ui,sans-serif;
            --body:"Hind Siliguri",system-ui,-apple-system,"Segoe UI",sans-serif;
            --grad:linear-gradient(135deg,#6d28d9,#4f46e5 50%,#0891b2);
        }
        [data-theme="dark"]{
            --ink:#f1f5f9; --ink-soft:#cbd5e1; --mute:#64748b;
            --bg:#050816; --glass:rgba(15,23,42,.62); --glass-b:rgba(148,163,184,.18); --line:rgba(148,163,184,.2);
            --chip:rgba(139,92,246,.18); --chip-ink:#c4b5fd; --shadow:0 18px 50px rgba(0,0,0,.5);
        }
        *,*::before,*::after{box-sizing:border-box}
        html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
        body{margin:0;font:400 16px/1.7 var(--body);color:var(--ink);background:var(--bg);-webkit-font-smoothing:antialiased;overflow-x:hidden;transition:background .3s,color .3s}
        h1,h2,h3{font-family:var(--display);margin:0;letter-spacing:-.02em;line-height:1.2}
        p{margin:0} a{color:inherit} img{max-width:100%;display:block}
        :focus-visible{outline:3px solid var(--p2);outline-offset:2px;border-radius:10px}

        /* ---------- aurora background ---------- */
        .aurora{position:fixed;inset:0;z-index:-1;overflow:hidden;pointer-events:none}
        .aurora i{position:absolute;border-radius:50%;filter:blur(90px);opacity:.55;animation:float 18s ease-in-out infinite}
        .aurora i:nth-child(1){width:520px;height:520px;background:#a78bfa;top:-160px;left:-120px}
        .aurora i:nth-child(2){width:460px;height:460px;background:#22d3ee;top:10%;right:-140px;animation-delay:-6s}
        .aurora i:nth-child(3){width:520px;height:520px;background:#f472b6;bottom:-200px;left:30%;animation-delay:-12s;opacity:.35}
        [data-theme="dark"] .aurora i{opacity:.28}
        .aurora::after{content:'';position:absolute;inset:0;background-image:radial-gradient(rgba(124,58,237,.18) 1px,transparent 1px);background-size:26px 26px;mask-image:linear-gradient(#000,transparent 70%);-webkit-mask-image:linear-gradient(#000,transparent 70%)}
        @keyframes float{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(40px,30px) scale(1.12)}}
        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes rise{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:none}}

        .page{width:min(1100px,100%);margin-inline:auto;padding:0 16px calc(110px + env(safe-area-inset-bottom,0px))}
        .preview{background:linear-gradient(90deg,#f59e0b,#f97316);color:#fff;text-align:center;padding:10px 16px;font-weight:700;font-size:14px}

        /* ---------- top bar ---------- */
        .bar{display:flex;justify-content:space-between;align-items:center;padding:calc(16px + env(safe-area-inset-top,0px)) 4px 14px}
        .brand{display:flex;align-items:center;gap:10px;text-decoration:none;font:800 18px var(--display);letter-spacing:-.02em}
        .brand i{width:34px;height:34px;border-radius:12px;display:grid;place-items:center;background:var(--grad);box-shadow:0 8px 22px rgba(99,102,241,.45)}
        .theme{border:1px solid var(--glass-b);background:var(--glass);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);color:var(--ink);width:42px;height:42px;border-radius:14px;font-size:17px;cursor:pointer;transition:transform .2s}
        .theme:hover{transform:rotate(18deg) scale(1.06)}

        /* ---------- glass ---------- */
        .glass{background:var(--glass);backdrop-filter:blur(20px) saturate(170%);-webkit-backdrop-filter:blur(20px) saturate(170%);border:1px solid var(--glass-b);border-radius:28px;box-shadow:var(--shadow),inset 0 1px 0 rgba(255,255,255,.35)}

        /* ---------- hero ---------- */
        .hero{padding:30px;display:grid;grid-template-columns:auto 1fr;gap:28px;align-items:center;position:relative;overflow:hidden;animation:rise .7s ease both}
        .hero::before{content:'';position:absolute;width:380px;height:380px;right:-120px;top:-180px;border-radius:50%;background:radial-gradient(circle,rgba(124,58,237,.28),transparent 65%);pointer-events:none}
        .avatar{position:relative;width:160px;height:160px;border-radius:48px;isolation:isolate}
        .avatar::before{content:'';position:absolute;inset:-5px;border-radius:52px;background:conic-gradient(from 0deg,#7c3aed,#06b6d4,#ec4899,#7c3aed);animation:spin 7s linear infinite;z-index:-2;filter:blur(1px)}
        .avatar::after{content:'';position:absolute;inset:0;border-radius:48px;background:var(--bg);z-index:-1}
        .avatar img,.avatar .ph{width:100%;height:100%;border-radius:44px;object-fit:cover;border:4px solid var(--bg)}
        .avatar .ph{display:grid;place-items:center;font:800 46px var(--display);color:#fff;background:var(--grad)}
        .avatar .verified{position:absolute;right:-8px;bottom:-8px;width:40px;height:40px;border-radius:14px;display:grid;place-items:center;color:#fff;background:linear-gradient(135deg,#059669,#06b6d4);border:3px solid var(--bg);box-shadow:0 8px 20px rgba(5,150,105,.45)}
        .id h1{font-size:clamp(26px,4.4vw,42px);font-weight:800;background:linear-gradient(135deg,var(--ink) 30%,var(--p1));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
        .spec{display:inline-flex;align-items:center;gap:8px;margin-top:10px;font:700 14px var(--display);color:var(--chip-ink);background:var(--chip);border:1px solid var(--line);padding:6px 14px;border-radius:999px}
        .degrees{margin-top:10px;font-weight:500;color:var(--ink-soft);font-size:15px}
        .tagline{margin-top:10px;color:var(--ink-soft);font-size:16px;max-width:60ch}
        .actions{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:10px}

        .btn{display:inline-flex;align-items:center;justify-content:center;gap:9px;border:0;cursor:pointer;text-decoration:none;font:700 14.5px var(--display);padding:13px 22px;border-radius:16px;transition:transform .18s,filter .18s,box-shadow .18s}
        .btn:hover{transform:translateY(-2px)}
        .btn-primary{color:#fff;background:var(--grad);box-shadow:0 10px 26px rgba(99,102,241,.4)}
        .btn-primary:hover{filter:brightness(1.1);box-shadow:0 14px 32px rgba(99,102,241,.5)}
        .btn-soft{color:var(--chip-ink);background:var(--chip);border:1px solid var(--line)}
        .btn-soft:hover{background:rgba(124,58,237,.18)}
        .btn-wa{color:#fff;background:linear-gradient(135deg,#16a34a,#22c55e);box-shadow:0 10px 26px rgba(34,197,94,.35)}
        .btn-fb{color:#fff;background:linear-gradient(135deg,#1d4ed8,#3b82f6);box-shadow:0 10px 26px rgba(59,130,246,.35)}

        /* ---------- stat tiles ---------- */
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-top:14px;animation:rise .8s .08s ease both}
        .stat{padding:16px 18px;display:flex;align-items:center;gap:13px;border-radius:22px}
        .stat .si{width:44px;height:44px;border-radius:15px;display:grid;place-items:center;color:#fff;background:var(--grad);flex:none;box-shadow:0 8px 20px rgba(99,102,241,.35)}
        .stat:nth-child(2) .si{background:linear-gradient(135deg,#ec4899,#8b5cf6)}
        .stat:nth-child(3) .si{background:linear-gradient(135deg,#06b6d4,#10b981)}
        .stat:nth-child(4) .si{background:linear-gradient(135deg,#f59e0b,#ef4444)}
        .stat small{display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:var(--mute)}
        .stat b{font:800 16px var(--display)}

        /* ---------- tabs ---------- */
        .tabs{position:sticky;top:10px;z-index:30;margin:18px 0;padding:6px;border-radius:20px}
        .tabs-in{display:flex;gap:4px;overflow-x:auto;scrollbar-width:none}
        .tabs-in::-webkit-scrollbar{display:none}
        .tabs a{flex:none;text-decoration:none;font:700 14px var(--display);padding:10px 20px;border-radius:14px;color:var(--ink-soft);transition:all .2s}
        .tabs a:hover{color:var(--p1)}
        .tabs a.on{color:#fff;background:var(--grad);box-shadow:0 8px 20px rgba(99,102,241,.4)}

        /* ---------- layout ---------- */
        .grid{display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start}
        .col{display:grid;gap:20px;min-width:0}
        @media (max-width:900px){.grid{grid-template-columns:1fr}.hero{grid-template-columns:1fr;text-align:center;justify-items:center;padding:26px 20px}.actions{justify-content:center}.tagline{margin-inline:auto}}
        .card{padding:26px}
        .card h2{font-size:19px;font-weight:800;display:flex;align-items:center;gap:12px;margin-bottom:16px}
        .card h2::before{content:'';width:5px;height:22px;border-radius:5px;background:linear-gradient(var(--p1),var(--p2))}
        .sticky{position:sticky;top:86px}
        @media (max-width:900px){.sticky{position:static}}
        .reveal{opacity:0;transform:translateY(24px);transition:opacity .6s ease,transform .6s ease}
        .reveal.in{opacity:1;transform:none}

        .facts{list-style:none;margin:0;padding:0;display:grid;gap:14px}
        .facts li{display:flex;gap:13px;align-items:flex-start}
        .fi{width:38px;height:38px;border-radius:13px;display:grid;place-items:center;flex:none;color:var(--chip-ink);background:var(--chip);border:1px solid var(--line)}
        .facts small{display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:var(--mute)}
        .facts b{font-weight:700;word-break:break-word}
        .facts a{text-decoration:none;color:var(--p1)} .facts a:hover{text-decoration:underline}
        .ok{display:inline-flex;align-items:center;gap:5px;margin-top:3px;font-size:12px;font-weight:700;color:#059669;background:rgba(16,185,129,.12);padding:2px 10px;border-radius:999px}

        .about{white-space:pre-line;color:var(--ink-soft);font-size:16.5px;line-height:1.85}
        .chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:20px}
        .chip{font:700 13px var(--display);color:var(--chip-ink);background:linear-gradient(135deg,rgba(124,58,237,.12),rgba(6,182,212,.12));border:1px solid var(--line);padding:7px 15px;border-radius:999px;transition:transform .15s}
        .chip:hover{transform:translateY(-2px)}

        .timeline{list-style:none;margin:0;padding:0 0 0 6px;display:grid;gap:18px;position:relative}
        .timeline::before{content:'';position:absolute;left:25px;top:8px;bottom:8px;width:2px;background:linear-gradient(var(--p1),var(--p2),transparent)}
        .timeline li{display:flex;gap:16px;align-items:flex-start;position:relative}
        .dot{width:40px;height:40px;border-radius:14px;display:grid;place-items:center;flex:none;color:#fff;background:var(--grad);box-shadow:0 0 0 5px var(--bg),0 8px 20px rgba(99,102,241,.35);z-index:1}
        .timeline strong{display:block;font:800 16px var(--display)}
        .timeline span{display:block;color:var(--ink-soft);font-size:15px}
        .timeline em{display:inline-block;margin-top:4px;font-style:normal;font-size:12px;font-weight:700;color:var(--chip-ink);background:var(--chip);padding:2px 11px;border-radius:999px}

        .empty{color:var(--mute)}
        .chamber{position:relative;padding:20px 22px 20px 26px;border-radius:22px;background:rgba(255,255,255,.35);border:1px solid var(--line);overflow:hidden}
        [data-theme="dark"] .chamber{background:rgba(30,41,59,.4)}
        .chamber::before{content:'';position:absolute;left:0;top:0;bottom:0;width:5px;background:linear-gradient(var(--p1),var(--p2),var(--p3))}
        .chamber + .chamber{margin-top:14px}
        .chamber h3{font-size:18px;font-weight:800}
        .hosp{color:var(--p1);font-weight:700;font-size:14px;margin-top:2px}
        .rows{display:grid;gap:10px;margin:14px 0}
        .rows > div{display:flex;gap:11px;align-items:flex-start;color:var(--ink-soft);font-size:15px}
        .rows svg{flex:none;margin-top:3px;color:var(--p1)}
        .rows a{color:var(--p1);font-weight:700;text-decoration:none}
        .days{display:flex;flex-wrap:wrap;gap:6px}
        .days b{font:700 12.5px var(--display);color:var(--chip-ink);background:var(--chip);border:1px solid var(--line);padding:3px 12px;border-radius:999px}
        .btns{display:flex;flex-wrap:wrap;gap:10px}
        .btns .btn{padding:11px 18px;font-size:13.5px}

        .linkbox{padding:14px 18px;border-radius:16px;background:var(--chip);border:1px dashed rgba(124,58,237,.4);font-weight:700;color:var(--chip-ink);word-break:break-all;font-size:14px}
        .share-row{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}

        footer{margin:34px 0 0;text-align:center;color:var(--mute);font-size:13px;display:grid;gap:6px}
        footer a{color:var(--p1);font-weight:700;text-decoration:none}

        /* ---------- mobile dock ---------- */
        .dock{display:none}
        @media (max-width:900px){
            .dock{display:flex;gap:8px;position:fixed;left:12px;right:12px;bottom:calc(12px + env(safe-area-inset-bottom,0px));z-index:40;padding:8px;border-radius:24px;background:var(--glass);backdrop-filter:blur(22px) saturate(180%);-webkit-backdrop-filter:blur(22px) saturate(180%);border:1px solid var(--glass-b);box-shadow:0 18px 50px rgba(76,29,149,.3)}
            .dock .btn{flex:1;padding:13px 8px;font-size:13.5px;border-radius:18px}
        }
        @media (prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important}.reveal{opacity:1;transform:none}}
    </style>
</head>
<body>

<div class="aurora" aria-hidden="true"><i></i><i></i><i></i></div>

@if ($isPreview)
    <div class="preview">Preview only. This profile is not visible to the public{{ $profile->is_published ? ' until your account is approved.' : ' because it is hidden. Publish it from Public Profile > Manage.' }}</div>
@endif

<div class="page">

    <div class="bar">
        <a href="{{ url('/') }}" class="brand">
            <i aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></i>
            {{ config('app.name', 'EasyDoc') }}
        </a>
        <button type="button" class="theme" id="themeBtn" aria-label="Toggle dark mode">🌙</button>
    </div>

    <header>
        <div class="hero glass">
            <div class="avatar">
                @if ($photo)
                    <img src="{{ $photo }}" alt="Photo of {{ $name }}" width="160" height="160">
                @else
                    <div class="ph" aria-hidden="true">{{ $initials }}</div>
                @endif
                <span class="verified" title="BMDC verified by {{ config('app.name', 'EasyDoc') }}" aria-label="BMDC verified">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
            </div>

            <div class="id">
                <h1>{{ $name }}</h1>
                <p class="spec">{!! $ico['spec'] !!} {{ $doctor->speciality }}</p>
                <p class="degrees">{{ $doctor->qualification }}@if ($doctor->designation) &middot; {{ $doctor->designation }}@endif</p>
                <p class="tagline">{{ $profile->tagline }}</p>
            </div>

            <div class="actions">
                @if ($phone)<a class="btn btn-primary" href="tel:{{ $phone }}">{!! $ico['phone'] !!} Call now</a>@endif
                @if ($chambers->isNotEmpty())<a class="btn {{ $phone ? 'btn-soft' : 'btn-primary' }}" href="#chambers">{!! $ico['pin'] !!} Chambers</a>@endif
                <button type="button" class="btn btn-soft" data-share="{{ $url }}" data-title="{{ $name }}">{!! $ico['share'] !!} Share</button>
            </div>
        </div>

        <div class="stats">
            <div class="stat glass"><span class="si">{!! $ico['id'] !!}</span><div><small>BMDC</small><b>{{ $doctor->bmdc_number }}</b></div></div>
            @if ($doctor->experienceLabel())<div class="stat glass"><span class="si">{!! $ico['work'] !!}</span><div><small>Experience</small><b>{{ $doctor->experienceLabel() }}</b></div></div>@endif
            @if ($profile->consultation_fee !== null)<div class="stat glass"><span class="si">{!! $ico['fee'] !!}</span><div><small>Consultation</small><b>&#2547;{{ number_format($profile->consultation_fee) }}</b></div></div>@endif
            @if (count($languages))<div class="stat glass"><span class="si">{!! $ico['lang'] !!}</span><div><small>Languages</small><b>{{ implode(', ', $languages) }}</b></div></div>@endif
        </div>

        <nav class="tabs glass" aria-label="Profile sections">
            <div class="tabs-in" id="tabsIn">
                <a href="#about" class="on">About</a>
                <a href="#education">Education</a>
                @if (count($workExp))<a href="#experience">Experience</a>@endif
                <a href="#chambers">Chambers</a>
                <a href="#share">Share</a>
            </div>
        </nav>
    </header>

    <main class="grid">

        {{-- Left column: facts --}}
        <div class="col">
            <section class="card glass sticky reveal" aria-label="Quick facts">
                <h2>Quick facts</h2>
                <ul class="facts">
                    <li>
                        <span class="fi">{!! $ico['id'] !!}</span>
                        <div><small>BMDC registration</small><b>{{ $doctor->bmdc_number }}</b><br>
                            <span class="ok"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> Verified by {{ config('app.name', 'EasyDoc') }}</span></div>
                    </li>
                    <li><span class="fi">{!! $ico['spec'] !!}</span><div><small>Speciality</small><b>{{ $doctor->speciality }}</b></div></li>
                    @if ($doctor->experienceLabel())
                        <li><span class="fi">{!! $ico['work'] !!}</span><div><small>Experience</small><b>{{ $doctor->experienceLabel() }}</b></div></li>
                    @endif
                    @if (count($languages))
                        <li><span class="fi">{!! $ico['lang'] !!}</span><div><small>Languages</small><b>{{ implode(', ', $languages) }}</b></div></li>
                    @endif
                    @if ($profile->consultation_fee !== null)
                        <li><span class="fi">{!! $ico['fee'] !!}</span><div><small>Consultation fee</small><b>&#2547;{{ number_format($profile->consultation_fee) }} per visit</b></div></li>
                    @endif
                    @if ($phone)
                        <li><span class="fi">{!! $ico['phone'] !!}</span><div><small>Mobile</small><b><a href="tel:{{ $phone }}">{{ $phone }}</a></b></div></li>
                    @endif
                    @if ($email)
                        <li><span class="fi">{!! $ico['mail'] !!}</span><div><small>Email</small><b><a href="mailto:{{ $email }}">{{ $email }}</a></b></div></li>
                    @endif
                    @if ($profile->website_url)
                        <li><span class="fi">{!! $ico['link'] !!}</span><div><small>Website</small><b><a href="{{ $profile->website_url }}" target="_blank" rel="noopener nofollow">Visit website</a></b></div></li>
                    @endif
                    @if ($profile->facebook_url)
                        <li><span class="fi">{!! $ico['link'] !!}</span><div><small>Facebook</small><b><a href="{{ $profile->facebook_url }}" target="_blank" rel="noopener nofollow">Facebook page</a></b></div></li>
                    @endif
                </ul>
            </section>
        </div>

        {{-- Right column: content --}}
        <div class="col">

            <section class="card glass reveal" id="about">
                <h2>About</h2>
                <p class="about">{{ $profile->about }}</p>
                @if (count($expertise))
                    <div class="chips" aria-label="Areas of expertise">
                        @foreach ($expertise as $item)<span class="chip">{{ $item }}</span>@endforeach
                    </div>
                @endif
            </section>

            <section class="card glass reveal" id="education">
                <h2>Education</h2>
                <ol class="timeline">
                    @foreach ($education as $e)
                        <li>
                            <span class="dot">{!! $ico['spec'] !!}</span>
                            <div>
                                <strong>{{ $e['degree'] }}</strong>
                                <span>{{ $e['institution'] }}</span>
                                @if (! empty($e['year']))<em>{{ $e['year'] }}</em>@endif
                            </div>
                        </li>
                    @endforeach
                    @if ($doctor->medical_college && ! collect($education)->contains(fn ($e) => $str::contains($str::lower($e['institution']), $str::lower($doctor->medical_college))))
                        <li>
                            <span class="dot">{!! $ico['spec'] !!}</span>
                            <div><strong>MBBS</strong><span>{{ $doctor->medical_college }}</span></div>
                        </li>
                    @endif
                </ol>
            </section>

            @if (count($workExp))
            <section class="card glass reveal" id="experience">
                <h2>Professional experience</h2>
                <ol class="timeline">
                    @foreach ($workExp as $w)
                        <li>
                            <span class="dot">{!! $ico['work'] !!}</span>
                            <div>
                                <strong>{{ $w['position'] }}</strong>
                                <span>{{ $w['organization'] }}</span>
                                @if (! empty($w['from']))<em>{{ $w['from'] }} &ndash; {{ ! empty($w['to']) ? $w['to'] : 'Present' }}</em>@endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
            @endif

            <section class="card glass reveal" id="chambers">
                <h2>Chambers</h2>
                @if ($chambers->isEmpty())
                    <p class="empty">No active chamber is listed right now.</p>
                @else
                    @foreach ($chambers as $c)
                        @php $days = preg_split('/\s*[,،|\/]\s*/u', (string) $c->visiting_days, -1, PREG_SPLIT_NO_EMPTY); @endphp
                        <article class="chamber">
                            <h3>{{ $c->chamber_name }}</h3>
                            <p class="hosp">{{ $c->hospital_name }}</p>
                            <div class="rows">
                                <div>{!! $ico['pin'] !!}<span>{{ $c->address }}</span></div>
                                <div>{!! $ico['cal'] !!}
                                    @if (count($days) > 1)
                                        <span class="days">@foreach ($days as $d)<b>{{ $d }}</b>@endforeach</span>
                                    @else
                                        <span>{{ $c->visiting_days }}</span>
                                    @endif
                                </div>
                                <div>{!! $ico['clock'] !!}<span>{{ $c->visiting_time }}</span></div>
                                @if ($profile->show_contact && $c->phone)
                                    <div>{!! $ico['phone'] !!}<a href="tel:{{ preg_replace('/\s+/', '', $c->phone) }}">{{ $c->phone }}</a></div>
                                @endif
                            </div>
                            <div class="btns">
                                <a class="btn btn-primary" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($c->address) }}">{!! $ico['nav'] !!} Directions</a>
                                @if ($profile->show_contact && $c->phone)
                                    <a class="btn btn-soft" href="tel:{{ preg_replace('/\s+/', '', $c->phone) }}">{!! $ico['phone'] !!} Call</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                @endif
            </section>

            <section class="card glass reveal share" id="share">
                <h2>Share this profile</h2>
                <div class="linkbox">{{ $url }}</div>
                <div class="share-row">
                    <button type="button" class="btn btn-primary" data-copy="{{ $url }}">{!! $ico['link'] !!} Copy link</button>
                    <a class="btn btn-wa" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($name . ' - ' . $url) }}">WhatsApp</a>
                    <a class="btn btn-fb" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}">Facebook</a>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <p>This page is for general information and is not a substitute for medical advice. In an emergency, go to the nearest hospital.</p>
        <p>Profile powered by <a href="{{ url('/') }}">{{ config('app.name', 'EasyDoc') }}</a>.</p>
    </footer>
</div>

{{-- Mobile action bar --}}
<div class="dock" aria-label="Quick actions">
    @if ($phone)
        <a class="btn btn-primary" href="tel:{{ $phone }}">{!! $ico['phone'] !!} Call</a>
    @elseif ($firstChamber)
        <a class="btn btn-primary" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($firstChamber->address) }}">{!! $ico['nav'] !!} Directions</a>
    @endif
    @if ($chambers->isNotEmpty())<a class="btn btn-soft" href="#chambers">{!! $ico['pin'] !!} Chambers</a>@endif
    <button type="button" class="btn btn-soft" data-share="{{ $url }}" data-title="{{ $name }}">{!! $ico['share'] !!} Share</button>
</div>

<script>
(function () {
    function flash(btn, text) {
        var original = btn.innerHTML;
        btn.textContent = text;
        setTimeout(function () { btn.innerHTML = original; }, 1800);
    }
    function copy(text, btn) {
        function done() { flash(btn, 'Link copied'); }
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(text).then(done); return; }
        var t = document.createElement('textarea');
        t.value = text; t.setAttribute('readonly', ''); t.style.position = 'fixed'; t.style.opacity = '0';
        document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); done(); } catch (e) {}
        document.body.removeChild(t);
    }

    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () { copy(btn.getAttribute('data-copy'), btn); });
    });

    // Share: native share sheet on phones, copy link elsewhere
    document.querySelectorAll('[data-share]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-share');
            if (navigator.share) {
                navigator.share({ title: btn.getAttribute('data-title'), url: url }).catch(function () {});
            } else {
                copy(url, btn);
            }
        });
    });

    // Dark / light toggle
    var root = document.documentElement, tb = document.getElementById('themeBtn');
    function paintTheme() { tb.textContent = root.getAttribute('data-theme') === 'dark' ? '☀️' : '🌙'; }
    tb.addEventListener('click', function () {
        var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        try { localStorage.setItem('pp-theme', next); } catch (e) {}
        paintTheme();
    });
    paintTheme();

    // Tab highlight while scrolling
    var bar = document.getElementById('tabsIn');
    var links = [].slice.call(document.querySelectorAll('.tabs a'));
    var byId = {};
    links.forEach(function (a) { byId[a.getAttribute('href').slice(1)] = a; });

    function setOn(id) {
        var a = byId[id];
        if (!a) return;
        links.forEach(function (l) { l.classList.remove('on'); });
        a.classList.add('on');
        if (bar && bar.scrollWidth > bar.clientWidth) {
            bar.scrollTo({ left: a.offsetLeft - (bar.clientWidth - a.offsetWidth) / 2, behavior: 'smooth' });
        }
    }

    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) setOn(e.target.id); });
        }, { rootMargin: '-25% 0px -65% 0px' });
        Object.keys(byId).forEach(function (id) {
            var s = document.getElementById(id);
            if (s) io.observe(s);
        });

        // Reveal cards as they scroll into view
        var rv = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); rv.unobserve(e.target); } });
        }, { threshold: 0.08 });
        document.querySelectorAll('.reveal').forEach(function (el) { rv.observe(el); });
    } else {
        document.querySelectorAll('.reveal').forEach(function (el) { el.classList.add('in'); });
    }
})();
</script>
</body>
</html>