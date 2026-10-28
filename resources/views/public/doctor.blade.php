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
    <meta name="theme-color" content="#0c2d57">
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:wght@600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>

    <style>
        :root{
            --navy:#0c2d57; --navy-2:#143d73; --teal:#0f766e;
            --ink:#1f2937; --body:#374151; --mute:#6b7280; --line:#e5e7eb; --bg:#f3f5f8; --card:#fff;
            --serif:"Source Serif 4","Hind Siliguri",Georgia,"Times New Roman",serif;
            --sans:"Inter","Hind Siliguri",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
        }
        *,*::before,*::after{box-sizing:border-box}
        html{scroll-behavior:smooth;-webkit-text-size-adjust:100%}
        body{margin:0;font:400 16px/1.65 var(--sans);color:var(--ink);background:var(--bg);-webkit-font-smoothing:antialiased}
        h1,h2,h3{font-family:var(--serif);margin:0;color:var(--navy);line-height:1.25}
        p{margin:0} a{color:inherit} img{max-width:100%;display:block}
        :focus-visible{outline:3px solid #93c5fd;outline-offset:2px;border-radius:6px}

        .preview{background:#fef3c7;color:#78350f;text-align:center;padding:9px 16px;font-weight:600;font-size:14px;border-bottom:1px solid #fde68a}

        /* top bar */
        .top{background:#fff;border-bottom:1px solid var(--line)}
        .top-in{max-width:1080px;margin:0 auto;padding:13px 20px;display:flex;justify-content:space-between;align-items:center;gap:12px}
        .brand{display:flex;align-items:center;gap:10px;text-decoration:none;font:700 18px var(--serif);color:var(--navy)}
        .brand i{width:26px;height:26px;border-radius:6px;background:var(--navy);display:grid;place-items:center}
        .top small{color:var(--mute);font-size:13px;display:flex;align-items:center;gap:6px}
        .top small svg{color:var(--teal)}

        .page{max-width:1080px;margin:0 auto;padding:20px 20px 60px}

        /* identity card */
        .profile{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:28px;display:grid;grid-template-columns:176px 1fr;gap:28px}
        .photo{width:176px;height:220px;border-radius:10px;overflow:hidden;background:#e8edf3;border:1px solid var(--line)}
        .photo img{width:100%;height:100%;object-fit:cover;object-position:center top}
        .photo .ph{width:100%;height:100%;display:grid;place-items:center;font:700 48px var(--serif);color:var(--navy)}
        .kicker{font-size:12.5px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--teal)}
        .ident h1{font-size:clamp(24px,3.4vw,34px);font-weight:700;margin:6px 0 8px;overflow-wrap:anywhere}
        .creds{font-size:16px;font-weight:500;color:var(--ink)}
        .desig{font-size:15px;color:var(--mute);margin-top:2px}
        .tag{margin-top:12px;font-size:15.5px;color:var(--body);max-width:64ch}
        .keyfacts{grid-column:1/-1;margin:0;padding-top:20px;border-top:1px solid var(--line);display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:18px 24px}
        .keyfacts dt{font-size:11.5px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--mute);margin-bottom:3px}
        .keyfacts dd{margin:0;font-size:15.5px;font-weight:600;color:var(--ink);overflow-wrap:anywhere}
        .vf{display:inline-flex;align-items:center;gap:4px;margin-left:6px;font-size:12px;font-weight:600;color:var(--teal);background:#ecfdf5;border:1px solid #a7f3d0;padding:1px 8px;border-radius:5px;vertical-align:1px}

        /* sticky section nav */
        .subnav{position:sticky;top:0;z-index:30;margin-top:16px;background:rgba(255,255,255,.97);border:1px solid var(--line);border-radius:10px}
        .subnav-in{display:flex;gap:2px;overflow-x:auto;scrollbar-width:none;padding:0 6px}
        .subnav-in::-webkit-scrollbar{display:none}
        .subnav a{flex:none;text-decoration:none;font-size:14.5px;font-weight:600;color:var(--mute);padding:14px 16px;border-bottom:2px solid transparent;margin-bottom:-1px}
        .subnav a:hover{color:var(--navy)}
        .subnav a.on{color:var(--navy);border-bottom-color:var(--navy)}

        /* layout */
        .layout{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:20px;margin-top:20px;align-items:start}
        .main{display:grid;gap:20px;min-width:0}
        .sec{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:26px 28px;scroll-margin-top:72px}
        .sec h2{font-size:21px;padding-bottom:13px;margin-bottom:18px;border-bottom:1px solid var(--line)}
        .about{white-space:pre-line;font-size:16px;line-height:1.85;color:var(--body)}
        .sub{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mute);margin:22px 0 10px}
        .tags{display:flex;flex-wrap:wrap;gap:8px}
        .tags span{font-size:14px;color:var(--body);background:#f9fafb;border:1px solid var(--line);padding:5px 12px;border-radius:6px}

        .entries{list-style:none;margin:0;padding:0}
        .entries li{display:grid;grid-template-columns:1fr auto;gap:2px 16px;padding:15px 0;border-top:1px solid var(--line)}
        .entries li:first-child{border-top:0;padding-top:0}
        .entries li:last-child{padding-bottom:0}
        .entries strong{font-size:16px;font-weight:600;color:var(--ink)}
        .entries span{color:var(--mute);font-size:15px;grid-column:1}
        .entries em{grid-column:2;grid-row:1/span 2;align-self:start;font-style:normal;font-size:14px;font-weight:600;color:var(--mute);white-space:nowrap}

        .chamber{border:1px solid var(--line);border-radius:10px;padding:20px 22px}
        .chamber + .chamber{margin-top:14px}
        .chamber h3{font-size:18px}
        .hosp{color:var(--teal);font-weight:600;font-size:14.5px;margin-top:2px}
        .info{margin:16px 0 0;display:grid;gap:10px}
        .info div{display:grid;grid-template-columns:84px 1fr;gap:12px;font-size:15px}
        .info dt{color:var(--mute);font-size:13.5px;padding-top:1px}
        .info dd{margin:0;color:var(--ink)}
        .info a{color:var(--navy);font-weight:600;text-decoration:none}
        .days{display:flex;flex-wrap:wrap;gap:6px}
        .days b{font-weight:600;font-size:13px;color:var(--body);background:#f3f4f6;border:1px solid var(--line);padding:1px 9px;border-radius:5px}
        .row-btns{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}

        /* buttons */
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;text-decoration:none;font:600 15px var(--sans);padding:11px 18px;border-radius:8px;border:1px solid transparent;transition:background .15s,border-color .15s}
        .btn svg{width:18px;height:18px;flex:none}
        .btn-primary{background:var(--navy);color:#fff}
        .btn-primary:hover{background:var(--navy-2)}
        .btn-line{background:#fff;color:var(--navy);border-color:#cbd5e1}
        .btn-line:hover{background:#f8fafc;border-color:var(--navy)}
        .btn-block{width:100%}

        /* sidebar */
        .side{display:grid;gap:20px;position:sticky;top:76px}
        .card{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:22px}
        .card h2{font-size:18px;margin-bottom:14px}
        .fee{display:flex;justify-content:space-between;align-items:baseline;padding:12px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line);margin-bottom:16px}
        .fee span{font-size:14px;color:var(--mute)}
        .fee b{font:700 24px var(--serif);color:var(--navy)}
        .next{font-size:14.5px;color:var(--body);margin-bottom:16px}
        .next b{display:block;font-size:15.5px;color:var(--ink)}
        .stack{display:grid;gap:10px}
        .contact{list-style:none;margin:0;padding:0;display:grid;gap:12px;font-size:14.5px}
        .contact small{display:block;font-size:11.5px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--mute)}
        .contact a{color:var(--navy);font-weight:600;text-decoration:none;overflow-wrap:anywhere}
        .linkbox{font-size:13px;color:var(--body);background:#f9fafb;border:1px solid var(--line);border-radius:8px;padding:9px 12px;word-break:break-all;margin-bottom:10px}
        .share-row{display:flex;gap:8px;flex-wrap:wrap}
        .share-row .btn{flex:1 1 0;padding:9px 6px;font-size:13.5px;min-width:0;white-space:nowrap}
        .empty{color:var(--mute)}

        footer{margin-top:28px;text-align:center;color:var(--mute);font-size:13px;display:grid;gap:4px}
        footer a{color:var(--navy);font-weight:600;text-decoration:none}

        .mbar{display:none}

        @media (max-width:900px){
            .layout{grid-template-columns:1fr}
            .side{position:static;order:-1}
            .sec{scroll-margin-top:64px}
        }
        @media (max-width:640px){
            .page{padding:14px 12px 96px}
            .top-in{padding:11px 14px} .top small{display:none}
            .profile{grid-template-columns:104px 1fr;gap:14px 16px;padding:18px}
            .photo{width:104px;height:132px}
            .photo .ph{font-size:30px}
            .ident h1{font-size:21px;margin:4px 0 6px}
            .kicker{font-size:11px}
            .creds{font-size:14.5px} .desig{font-size:13.5px}
            .tag{grid-column:1/-1;margin-top:0}
            .ident{display:contents}
            .ident > .head{grid-column:2}
            .keyfacts{grid-template-columns:1fr 1fr;gap:14px 16px;padding-top:16px}
            .sec{padding:20px 18px;border-radius:10px}
            .sec h2{font-size:19px}
            .chamber{padding:16px}
            .info div{grid-template-columns:70px 1fr}
            .entries li{grid-template-columns:1fr}
            .entries em{grid-column:1;grid-row:auto}
            .subnav a{padding:13px 13px;font-size:14px}
            .mbar{display:flex;gap:8px;position:fixed;left:0;right:0;bottom:0;z-index:40;background:#fff;border-top:1px solid var(--line);padding:10px 12px calc(10px + env(safe-area-inset-bottom,0px));box-shadow:0 -4px 14px rgba(15,23,42,.06)}
            .mbar .btn{flex:1 1 0;min-width:0;padding:12px 8px;font-size:14.5px}
        }
    </style>
</head>
<body>

@if ($isPreview)
    <div class="preview">Preview only. This profile is not visible to the public{{ $profile->is_published ? ' until your account is approved.' : ' because it is hidden. Publish it from Public Profile > Manage.' }}</div>
@endif

<div class="top">
    <div class="top-in">
        <a href="{{ url('/') }}" class="brand">
            <i aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></i>
            {{ config('app.name', 'EasyDoc') }}
        </a>
        <small>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3z"/><path d="m9 12 2 2 4-4"/></svg>
            Verified practitioner profile
        </small>
    </div>
</div>

<div class="page">

    <header class="profile">
        <div class="photo">
            @if ($photo)
                <img src="{{ $photo }}" alt="Photo of {{ $name }}" width="176" height="220">
            @else
                <div class="ph" aria-hidden="true">{{ $initials }}</div>
            @endif
        </div>

        <div class="ident">
            <div class="head">
                <p class="kicker">{{ $doctor->speciality }}</p>
                <h1>{{ $name }}</h1>
                <p class="creds">{{ $doctor->qualification }}</p>
                @if ($doctor->designation)<p class="desig">{{ $doctor->designation }}</p>@endif
            </div>
            @if ($profile->tagline)<p class="tag">{{ $profile->tagline }}</p>@endif
        </div>

        <dl class="keyfacts">
            <div>
                <dt>BMDC registration</dt>
                <dd>{{ $doctor->bmdc_number }} <span class="vf"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>Verified</span></dd>
            </div>
            @if ($doctor->experienceLabel())<div><dt>Experience</dt><dd>{{ $doctor->experienceLabel() }}</dd></div>@endif
            @if (count($languages))<div><dt>Languages</dt><dd>{{ implode(', ', $languages) }}</dd></div>@endif
            @if ($profile->consultation_fee !== null)<div><dt>Consultation fee</dt><dd>&#2547;{{ number_format($profile->consultation_fee) }}</dd></div>@endif
        </dl>
    </header>

    <nav class="subnav" aria-label="Profile sections">
        <div class="subnav-in" id="tabsIn">
            <a href="#about" class="on">About</a>
            <a href="#education">Education</a>
            @if (count($workExp))<a href="#experience">Experience</a>@endif
            <a href="#chambers">Chambers</a>
        </div>
    </nav>

    <div class="layout">

        <div class="main">

            <section class="sec" id="about">
                <h2>About</h2>
                <p class="about">{{ $profile->about }}</p>
                @if (count($expertise))
                    <p class="sub">Areas of expertise</p>
                    <div class="tags">@foreach ($expertise as $item)<span>{{ $item }}</span>@endforeach</div>
                @endif
            </section>

            <section class="sec" id="education">
                <h2>Education</h2>
                <ul class="entries">
                    @foreach ($education as $e)
                        <li>
                            <strong>{{ $e['degree'] }}</strong>
                            <span>{{ $e['institution'] }}</span>
                            @if (! empty($e['year']))<em>{{ $e['year'] }}</em>@endif
                        </li>
                    @endforeach
                    @if ($doctor->medical_college && ! collect($education)->contains(fn ($e) => $str::contains($str::lower($e['institution']), $str::lower($doctor->medical_college))))
                        <li><strong>MBBS</strong><span>{{ $doctor->medical_college }}</span></li>
                    @endif
                </ul>
            </section>

            @if (count($workExp))
            <section class="sec" id="experience">
                <h2>Professional experience</h2>
                <ul class="entries">
                    @foreach ($workExp as $w)
                        <li>
                            <strong>{{ $w['position'] }}</strong>
                            <span>{{ $w['organization'] }}</span>
                            @if (! empty($w['from']))<em>{{ $w['from'] }} &ndash; {{ ! empty($w['to']) ? $w['to'] : 'Present' }}</em>@endif
                        </li>
                    @endforeach
                </ul>
            </section>
            @endif

            <section class="sec" id="chambers">
                <h2>Chambers</h2>
                @if ($chambers->isEmpty())
                    <p class="empty">No active chamber is listed right now.</p>
                @else
                    @foreach ($chambers as $c)
                        @php $days = preg_split('/\s*[,،|\/]\s*/u', (string) $c->visiting_days, -1, PREG_SPLIT_NO_EMPTY); @endphp
                        <article class="chamber">
                            <h3>{{ $c->chamber_name }}</h3>
                            <p class="hosp">{{ $c->hospital_name }}</p>
                            <dl class="info">
                                <div><dt>Address</dt><dd>{{ $c->address }}</dd></div>
                                <div><dt>Days</dt><dd>@if (count($days) > 1)<span class="days">@foreach ($days as $d)<b>{{ $d }}</b>@endforeach</span>@else{{ $c->visiting_days }}@endif</dd></div>
                                <div><dt>Time</dt><dd>{{ $c->visiting_time }}</dd></div>
                                @if ($profile->show_contact && $c->phone)
                                    <div><dt>Phone</dt><dd><a href="tel:{{ preg_replace('/\s+/', '', $c->phone) }}">{{ $c->phone }}</a></dd></div>
                                @endif
                            </dl>
                            <div class="row-btns">
                                <a class="btn btn-primary" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($c->address) }}">{!! $ico['nav'] !!} Get directions</a>
                                @if ($profile->show_contact && $c->phone)
                                    <a class="btn btn-line" href="tel:{{ preg_replace('/\s+/', '', $c->phone) }}">{!! $ico['phone'] !!} Call chamber</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                @endif
            </section>
        </div>

        {{-- Sidebar --}}
        <aside class="side">
            <div class="card">
                <h2>Appointment</h2>
                @if ($profile->consultation_fee !== null)
                    <div class="fee"><span>Consultation fee</span><b>&#2547;{{ number_format($profile->consultation_fee) }}</b></div>
                @endif
                @if ($firstChamber)
                    <div class="next"><b>{{ $firstChamber->chamber_name }}</b>{{ $firstChamber->visiting_days }} &middot; {{ $firstChamber->visiting_time }}</div>
                @endif
                <div class="stack">
                    @if ($phone)<a class="btn btn-primary btn-block" href="tel:{{ $phone }}">{!! $ico['phone'] !!} Call {{ $phone }}</a>@endif
                    @if ($firstChamber)<a class="btn {{ $phone ? 'btn-line' : 'btn-primary' }} btn-block" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($firstChamber->address) }}">{!! $ico['nav'] !!} Get directions</a>@endif
                </div>
            </div>

            @if ($email || $profile->website_url || $profile->facebook_url)
            <div class="card">
                <h2>Contact</h2>
                <ul class="contact">
                    @if ($email)<li><small>Email</small><a href="mailto:{{ $email }}">{{ $email }}</a></li>@endif
                    @if ($profile->website_url)<li><small>Website</small><a href="{{ $profile->website_url }}" target="_blank" rel="noopener nofollow">Visit website</a></li>@endif
                    @if ($profile->facebook_url)<li><small>Facebook</small><a href="{{ $profile->facebook_url }}" target="_blank" rel="noopener nofollow">Facebook page</a></li>@endif
                </ul>
            </div>
            @endif

            <div class="card" id="share">
                <h2>Share this profile</h2>
                <div class="linkbox">{{ $url }}</div>
                <div class="share-row">
                    <button type="button" class="btn btn-line" data-copy="{{ $url }}">Copy link</button>
                    <a class="btn btn-line" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($name . ' - ' . $url) }}">WhatsApp</a>
                    <a class="btn btn-line" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}">Facebook</a>
                </div>
            </div>
        </aside>
    </div>

    <footer>
        <p>This page is for general information and is not a substitute for medical advice. In an emergency, go to the nearest hospital.</p>
        <p>Profile powered by <a href="{{ url('/') }}">{{ config('app.name', 'EasyDoc') }}</a>.</p>
    </footer>
</div>

{{-- Mobile action bar --}}
<div class="mbar" aria-label="Quick actions">
    @if ($phone)
        <a class="btn btn-primary" href="tel:{{ $phone }}">{!! $ico['phone'] !!} Call</a>
    @endif
    @if ($firstChamber)
        <a class="btn {{ $phone ? 'btn-line' : 'btn-primary' }}" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($firstChamber->address) }}">{!! $ico['nav'] !!} Directions</a>
    @endif
    <button type="button" class="btn btn-line" data-share="{{ $url }}" data-title="{{ $name }}">{!! $ico['share'] !!} Share</button>
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

    // Highlight the section tab while scrolling
    var bar = document.getElementById('tabsIn');
    var links = [].slice.call(document.querySelectorAll('.subnav a'));
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