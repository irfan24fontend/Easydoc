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
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#10262b">
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
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Hind+Siliguri:wght@400;500;600&display=swap" rel="stylesheet">

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>

    <style>
        :root{
            --ink:#10262b; --ink-soft:#4b626b; --teal:#0c8c84; --teal-deep:#0a6b65; --mint:#e3f3f0;
            --bg:#eaf0f0; --card:#fff; --line:#dbe5e5; --amber:#f4b63f;
            --display:"Bricolage Grotesque","Hind Siliguri",system-ui,sans-serif;
            --body:"Hind Siliguri",system-ui,-apple-system,"Segoe UI",sans-serif;
            --tabs-h:56px;
        }
        *,*::before,*::after{box-sizing:border-box}
        html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
        body{margin:0;font:400 16px/1.65 var(--body);color:var(--ink);background:var(--bg);-webkit-font-smoothing:antialiased;overflow-x:hidden}
        h1,h2,h3{font-family:var(--display);margin:0;letter-spacing:-.02em;line-height:1.2}
        p{margin:0}
        a{color:inherit}
        img{max-width:100%;display:block}
        :focus-visible{outline:3px solid var(--amber);outline-offset:2px;border-radius:8px}
        .page{width:min(1040px,100%);margin-inline:auto}

        .preview{background:var(--amber);color:#3d2a00;text-align:center;padding:10px 16px;font-weight:600;font-size:15px}

        /* ---------- Cover ---------- */
        .cover{
            height:clamp(150px,27vw,300px);position:relative;overflow:hidden;
            background:
              radial-gradient(520px 320px at 85% 120%,rgba(12,140,132,.65),transparent 70%),
              url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='56' height='56'%3E%3Cpath d='M28 22v12M22 28h12' stroke='%23ffffff' stroke-opacity='.08' stroke-width='3' stroke-linecap='round'/%3E%3C/svg%3E"),
              linear-gradient(135deg,#0a6b65 0%,#10262b 70%);
        }
        .cover-bar{display:flex;justify-content:space-between;align-items:center;padding:calc(14px + env(safe-area-inset-top,0px)) 18px 0}
        .brand{display:inline-flex;align-items:center;gap:8px;font:800 17px var(--display);color:#fff;text-decoration:none;letter-spacing:-.03em;background:rgba(16,38,43,.35);backdrop-filter:blur(6px);padding:6px 12px 6px 8px;border-radius:999px}
        .brand i{width:26px;height:26px;border-radius:8px;background:var(--teal);display:grid;place-items:center}
        @media (min-width:1040px){.cover{border-radius:0 0 16px 16px}}

        /* ---------- Header card ---------- */
        .head{background:var(--card);border:1px solid var(--line);border-top:0;border-radius:0 0 16px 16px;margin:0 0 14px;position:relative}
        @media (max-width:1039px){.head{border-left:0;border-right:0;border-radius:0}}
        .head-main{display:flex;gap:26px;align-items:flex-end;padding:0 28px 22px}
        .avatar{
            flex:none;width:172px;height:172px;margin-top:-86px;border-radius:50%;
            border:6px solid #fff;background:#d6e6e5;overflow:hidden;position:relative;
            box-shadow:0 6px 22px -8px rgba(16,38,43,.45);
        }
        .avatar img{width:100%;height:100%;object-fit:cover}
        .avatar .ph{width:100%;height:100%;display:grid;place-items:center;font:800 54px var(--display);color:var(--teal-deep);background:var(--mint)}
        .id{flex:1;min-width:0;padding-top:14px}
        .id h1{font-size:clamp(26px,3.4vw,36px);font-weight:800;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
        .tick{display:inline-grid;place-items:center;width:26px;height:26px;border-radius:50%;background:var(--teal);color:#fff;flex:none}
        .spec{font:700 19px/1.3 var(--display);color:var(--teal-deep);margin-top:6px}
        .degrees{color:var(--ink-soft);margin-top:2px}
        .tagline{margin-top:10px;color:#2b4148}
        .quick{list-style:none;margin:14px 0 0;padding:0;display:flex;flex-wrap:wrap;gap:8px}
        .quick li{display:inline-flex;align-items:center;gap:7px;background:var(--mint);color:var(--teal-deep);font-weight:600;font-size:14.5px;padding:6px 13px;border-radius:999px}
        .quick svg{width:17px;height:17px}
        .actions{display:flex;gap:10px;flex-wrap:wrap;align-self:flex-end;padding-bottom:2px}

        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;font:600 15.5px var(--body);padding:0 20px;min-height:44px;border-radius:10px;border:0;cursor:pointer;text-decoration:none;transition:background .15s;white-space:nowrap}
        .btn svg{width:19px;height:19px;flex:none}
        .btn-primary{background:var(--teal);color:#fff}.btn-primary:hover{background:var(--teal-deep)}
        .btn-soft{background:#e6edee;color:var(--ink)}.btn-soft:hover{background:#d8e3e4}

        /* ---------- Tabs (sticky) ---------- */
        .tabs{position:sticky;top:0;z-index:40;background:rgba(255,255,255,.96);backdrop-filter:blur(8px);border-top:1px solid var(--line);border-radius:0 0 16px 16px}
        @media (max-width:1039px){.tabs{border-radius:0}}
        .tabs-in{display:flex;gap:4px;padding:0 14px;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none;height:var(--tabs-h);align-items:stretch}
        .tabs-in::-webkit-scrollbar{display:none}
        .tabs a{display:inline-flex;align-items:center;padding:0 18px;text-decoration:none;font-weight:600;color:var(--ink-soft);white-space:nowrap;border-bottom:3px solid transparent;margin-top:3px;transition:color .15s}
        .tabs a:hover{color:var(--ink);background:#f2f6f6}
        .tabs a.on{color:var(--teal-deep);border-bottom-color:var(--teal)}

        /* ---------- Body layout ---------- */
        .grid{display:grid;grid-template-columns:1fr;gap:14px;padding:0 0 calc(24px + env(safe-area-inset-bottom,0px))}
        @media (max-width:1039px){.grid{padding:0 12px 24px}}
        @media (min-width:880px){.grid{grid-template-columns:340px 1fr;align-items:start}}
        .col{display:grid;gap:14px;min-width:0}
        @media (min-width:880px) and (min-height:780px){.sticky{position:sticky;top:calc(var(--tabs-h) + 12px)}}

        .card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:22px;scroll-margin-top:calc(var(--tabs-h) + 10px)}
        .card h2{font-size:21px;font-weight:800;margin-bottom:14px}
        .about{white-space:pre-line;color:#2a4047;overflow-wrap:anywhere}

        .facts{list-style:none;margin:0;padding:0;display:grid;gap:14px}
        .facts li{display:grid;grid-template-columns:36px 1fr;gap:12px;align-items:center}
        .facts .fi{width:36px;height:36px;border-radius:50%;background:var(--mint);color:var(--teal-deep);display:grid;place-items:center}
        .facts small{display:block;font-size:12.5px;text-transform:uppercase;letter-spacing:.07em;color:var(--ink-soft);font-weight:600;line-height:1.3}
        .facts b{display:block;font-weight:600;font-size:16.5px;line-height:1.35;overflow-wrap:anywhere}
        .facts a{color:var(--teal-deep);text-decoration:none}
        .facts .ok{display:inline-flex;align-items:center;gap:5px;color:var(--teal-deep);font-size:13.5px;font-weight:600}

        .chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
        .chip{background:var(--mint);color:var(--teal-deep);font-weight:600;font-size:15px;padding:6px 14px;border-radius:999px}

        .timeline{list-style:none;margin:0;padding:0 0 0 4px;display:grid;gap:20px}
        .timeline li{display:grid;grid-template-columns:44px 1fr;gap:14px}
        .timeline .dot{width:44px;height:44px;border-radius:12px;background:var(--mint);color:var(--teal-deep);display:grid;place-items:center}
        .timeline strong{display:block;font:700 17.5px/1.3 var(--display);letter-spacing:-.01em}
        .timeline span{display:block;color:var(--ink-soft);overflow-wrap:anywhere}
        .timeline em{font-style:normal;font-size:14px;font-weight:600;color:var(--teal-deep)}

        .chamber{border:1px solid var(--line);border-radius:12px;padding:18px;background:#f7fafa}
        .chamber + .chamber{margin-top:12px}
        .chamber h3{font-size:19px;font-weight:700}
        .chamber .hosp{color:var(--ink-soft);margin-bottom:12px}
        .rows{display:grid;gap:8px}
        .rows div{display:grid;grid-template-columns:24px 1fr;gap:10px}
        .rows svg{width:19px;height:19px;color:var(--teal);margin-top:3px}
        .rows span,.rows a{overflow-wrap:anywhere}
        .rows a{color:var(--teal-deep);font-weight:600;text-decoration:none}
        .chamber .btns{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}

        .linkbox{background:#f1f6f6;border-radius:10px;padding:11px 13px;font-size:14px;color:var(--ink-soft);overflow-wrap:anywhere;margin-bottom:12px}
        .share-row{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
        .empty{color:var(--ink-soft)}

        footer{padding:6px 18px calc(28px + env(safe-area-inset-bottom,0px));color:var(--ink-soft);font-size:14px;text-align:center}
        footer p + p{margin-top:6px}
        footer a{color:var(--teal-deep)}

        /* ---------- Mobile bottom bar ---------- */
        .dock{display:none}
        @media (max-width:879px){
            .dock{display:grid;grid-auto-flow:column;grid-auto-columns:1fr;gap:10px;position:fixed;left:0;right:0;bottom:0;z-index:50;background:rgba(255,255,255,.97);backdrop-filter:blur(8px);border-top:1px solid var(--line);padding:10px 12px calc(10px + env(safe-area-inset-bottom,0px))}
            body{padding-bottom:calc(74px + env(safe-area-inset-bottom,0px))}
        }

        /* ---------- Tablet ---------- */
        @media (max-width:879px){
            .head-main{padding:0 20px 20px;gap:20px}
            .avatar{width:140px;height:140px;margin-top:-70px}
            .actions{display:none}
        }
        /* ---------- Phone ---------- */
        @media (max-width:640px){
            body{font-size:16px}
            .head-main{flex-direction:column;align-items:center;text-align:center;gap:0;padding:0 16px 18px}
            .avatar{width:128px;height:128px;margin-top:-64px;border-width:5px}
            .id{padding-top:12px}
            .id h1{justify-content:center}
            .quick{justify-content:center}
            .card{padding:18px}
            .card h2{font-size:20px}
            .tabs a{padding:0 14px}
            .share-row{grid-template-columns:1fr}
            .timeline li{grid-template-columns:40px 1fr;gap:12px}
            .timeline .dot{width:40px;height:40px}
        }
        @media (max-width:360px){.btn{padding:0 12px;font-size:14.5px}.id h1{font-size:24px}}
        @media (prefers-reduced-motion:reduce){html{scroll-behavior:auto}*{transition:none!important}}
        @media print{.cover-bar,.tabs,.actions,.dock,.share{display:none!important}.cover{height:60px}body{padding:0}}
    </style>
</head>
<body>

@if ($isPreview)
    <div class="preview">Preview only. This profile is not visible to the public{{ $profile->is_published ? ' until your account is approved.' : ' because it is hidden. Publish it from Public Profile > Manage.' }}</div>
@endif

<div class="page">

    <div class="cover" role="img" aria-label="Profile cover">
        <div class="cover-bar">
            <a href="{{ url('/') }}" class="brand">
                <i aria-hidden="true"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></i>
                {{ config('app.name', 'EasyDoc') }}
            </a>
        </div>
    </div>

    <header class="head">
        <div class="head-main">
            <div class="avatar">
                @if ($photo)
                    <img src="{{ $photo }}" alt="Photo of {{ $name }}" width="172" height="172">
                @else
                    <div class="ph" aria-hidden="true">{{ $initials }}</div>
                @endif
            </div>

            <div class="id">
                <h1>
                    {{ $name }}
                    <span class="tick" title="BMDC verified by {{ config('app.name', 'EasyDoc') }}" aria-label="BMDC verified">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    </span>
                </h1>
                <p class="spec">{{ $doctor->speciality }}</p>
                <p class="degrees">{{ $doctor->qualification }}@if ($doctor->designation) &middot; {{ $doctor->designation }}@endif</p>
                <p class="tagline">{{ $profile->tagline }}</p>

                <ul class="quick">
                    <li>{!! $ico['id'] !!} BMDC {{ $doctor->bmdc_number }}</li>
                    @if ($doctor->experienceLabel())<li>{!! $ico['work'] !!} {{ $doctor->experienceLabel() }}</li>@endif
                    @if ($profile->consultation_fee !== null)<li>{!! $ico['fee'] !!} &#2547;{{ number_format($profile->consultation_fee) }}</li>@endif
                </ul>
            </div>

            <div class="actions">
                @if ($phone)<a class="btn btn-primary" href="tel:{{ $phone }}">{!! $ico['phone'] !!} Call</a>@endif
                @if ($chambers->isNotEmpty())<a class="btn {{ $phone ? 'btn-soft' : 'btn-primary' }}" href="#chambers">{!! $ico['pin'] !!} Chambers</a>@endif
                <button type="button" class="btn btn-soft" data-share="{{ $url }}" data-title="{{ $name }}">{!! $ico['share'] !!} Share</button>
            </div>
        </div>

        <nav class="tabs" aria-label="Profile sections">
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
            <section class="card sticky" aria-label="Quick facts">
                <h2>Intro</h2>
                <ul class="facts">
                    <li>
                        <span class="fi">{!! $ico['id'] !!}</span>
                        <div><small>BMDC registration</small><b>{{ $doctor->bmdc_number }}</b>
                            <span class="ok"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg> Verified by {{ config('app.name', 'EasyDoc') }}</span></div>
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

            <section class="card" id="about">
                <h2>About</h2>
                <p class="about">{{ $profile->about }}</p>
                @if (count($expertise))
                    <div class="chips" aria-label="Areas of expertise">
                        @foreach ($expertise as $item)<span class="chip">{{ $item }}</span>@endforeach
                    </div>
                @endif
            </section>

            <section class="card" id="education">
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
            <section class="card" id="experience">
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

            <section class="card" id="chambers">
                <h2>Chambers</h2>
                @if ($chambers->isEmpty())
                    <p class="empty">No active chamber is listed right now.</p>
                @else
                    @foreach ($chambers as $c)
                        <article class="chamber">
                            <h3>{{ $c->chamber_name }}</h3>
                            <p class="hosp">{{ $c->hospital_name }}</p>
                            <div class="rows">
                                <div>{!! $ico['pin'] !!}<span>{{ $c->address }}</span></div>
                                <div>{!! $ico['cal'] !!}<span>{{ $c->visiting_days }}</span></div>
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

            <section class="card share" id="share">
                <h2>Share this profile</h2>
                <div class="linkbox">{{ $url }}</div>
                <div class="share-row">
                    <button type="button" class="btn btn-primary" data-copy="{{ $url }}">{!! $ico['link'] !!} Copy link</button>
                    <a class="btn btn-soft" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($name . ' - ' . $url) }}">WhatsApp</a>
                    <a class="btn btn-soft" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}">Facebook</a>
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
    }
})();
</script>
</body>
</html>