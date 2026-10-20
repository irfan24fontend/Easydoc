@extends('layouts.doctor')

@section('title', 'Dashboard')

@push('styles')
<style>
    :root { --dx-brand: #4f46e5; --dx-ink: #0f172a; --dx-muted: #64748b; --dx-line: #e8ebf1; }

    /* ---------- Hero ---------- */
    .dx-hero {
        position: relative; overflow: hidden; border-radius: 22px; padding: 1.7rem 1.9rem; color: #fff;
        background: radial-gradient(1200px 320px at 92% -25%, rgba(255,255,255,.2), transparent 60%),
                    linear-gradient(135deg, #4f46e5 0%, #6d28d9 55%, #7c3aed 100%);
        box-shadow: 0 14px 34px rgba(79,70,229,.28);
    }
    .dx-hero::after { content: ""; position: absolute; right: -70px; bottom: -90px; width: 260px; height: 260px; border-radius: 50%; background: rgba(255,255,255,.07); }
    .dx-hero-inner { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.2rem; }
    .dx-date { font-size: .76rem; font-weight: 700; letter-spacing: .04em; opacity: .8; display: inline-flex; align-items: center; gap: 6px; }
    .dx-hero h1 { font-size: 1.6rem; font-weight: 800; letter-spacing: -.02em; margin: .25rem 0 0; }
    .dx-hero p { margin: .3rem 0 0; opacity: .85; font-size: .9rem; }
    .dx-cta { display: flex; flex-wrap: wrap; gap: .6rem; margin-top: 1.1rem; }
    .dx-btn { display: inline-flex; align-items: center; gap: 8px; border-radius: 12px; padding: .6rem 1.1rem; font-size: .85rem; font-weight: 800; text-decoration: none; transition: transform .15s, box-shadow .15s, background .15s; }
    .dx-btn.primary { background: #fff; color: #4338ca; box-shadow: 0 8px 18px rgba(15,23,42,.2); }
    .dx-btn.primary:hover { color: #3730a3; transform: translateY(-1px); box-shadow: 0 12px 24px rgba(15,23,42,.25); }
    .dx-btn.ghost { background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.3); color: #fff; }
    .dx-btn.ghost:hover { background: rgba(255,255,255,.24); color: #fff; }

    .dx-now { background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.25); backdrop-filter: blur(6px); border-radius: 16px; padding: .9rem 1.15rem; min-width: 260px; }
    .dx-now.off { background: rgba(254,226,226,.18); border-color: rgba(254,202,202,.5); }
    .dx-now .lbl { font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 700; opacity: .8; }
    .dx-now .nm { font-weight: 800; font-size: 1.05rem; line-height: 1.25; margin-top: .15rem; }
    .dx-now .sub { font-size: .78rem; opacity: .8; }
    .dx-dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #4ade80; margin-right: 6px; animation: dxPulse 1.8s infinite; }
    .dx-dot.red { background: #f87171; animation: none; }
    @keyframes dxPulse { 0% { box-shadow: 0 0 0 0 rgba(74,222,128,.6); } 70% { box-shadow: 0 0 0 9px rgba(74,222,128,0); } 100% { box-shadow: 0 0 0 0 rgba(74,222,128,0); } }

    /* ---------- Alert ---------- */
    .dx-alert { display: flex; align-items: center; gap: 12px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 14px; padding: .8rem 1rem; font-size: .86rem; }
    .dx-alert i { font-size: 1.05rem; color: #d97706; }
    .dx-alert a { margin-left: auto; white-space: nowrap; font-weight: 800; color: #92400e; text-decoration: none; background: #fef3c7; border-radius: 9px; padding: .35rem .8rem; }
    .dx-alert a:hover { background: #fde68a; }

    /* ---------- Stat cards ---------- */
    .dx-stat { background: #fff; border: 1px solid var(--dx-line); border-radius: 18px; padding: 1.1rem 1.2rem; height: 100%; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.04); transition: box-shadow .2s, transform .2s; }
    .dx-stat:hover { box-shadow: 0 12px 26px rgba(15,23,42,.08); transform: translateY(-2px); }
    .dx-stat .ico { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .dx-stat .num { font-size: 1.45rem; font-weight: 800; color: var(--dx-ink); line-height: 1.1; }
    .dx-stat .num.sm { font-size: 1.02rem; }
    .dx-stat .lbl { font-size: .76rem; color: var(--dx-muted); font-weight: 600; margin-top: 2px; }
    .dx-stat .min-w { min-width: 0; }

    /* ---------- Cards ---------- */
    .dx-card { background: #fff; border: 1px solid var(--dx-line); border-radius: 20px; padding: 1.35rem 1.4rem; box-shadow: 0 1px 3px rgba(15,23,42,.04); }
    .dx-card-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .6rem; margin-bottom: 1rem; }
    .dx-card-head h2 { margin: 0; font-size: .98rem; font-weight: 800; color: var(--dx-ink); display: flex; align-items: center; gap: 9px; }
    .dx-card-head h2 .hi { width: 32px; height: 32px; border-radius: 10px; background: #eef2ff; color: var(--dx-brand); display: inline-flex; align-items: center; justify-content: center; font-size: .85rem; }
    .dx-card-head .sub { font-size: .76rem; color: var(--dx-muted); font-weight: 500; }
    .dx-link { font-size: .78rem; font-weight: 800; color: var(--dx-brand); text-decoration: none; background: #eef2ff; border-radius: 10px; padding: .4rem .8rem; }
    .dx-link:hover { background: #e0e7ff; color: #4338ca; }

    /* ---------- Queue ---------- */
    .dx-progress { height: 8px; border-radius: 999px; background: #eef2ff; overflow: hidden; }
    .dx-progress > span { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg, #4f46e5, #7c3aed); transition: width .6s ease; }
    .dx-progress-meta { display: flex; justify-content: space-between; font-size: .76rem; color: var(--dx-muted); font-weight: 600; margin: .45rem 0 1rem; }

    .dx-counts { display: grid; grid-template-columns: repeat(4, 1fr); gap: .6rem; margin-bottom: 1.1rem; }
    .dx-count { border: 1px solid var(--dx-line); border-radius: 14px; padding: .7rem .5rem; text-align: center; background: #f8fafc; }
    .dx-count .n { font-size: 1.3rem; font-weight: 800; color: var(--dx-ink); line-height: 1; }
    .dx-count .l { font-size: .7rem; color: var(--dx-muted); font-weight: 700; margin-top: .25rem; display: inline-flex; align-items: center; gap: 5px; }
    .dx-count .l i { width: 7px; height: 7px; border-radius: 50%; display: inline-block; }

    .dx-row { display: flex; align-items: center; gap: 13px; padding: .75rem .9rem; border: 1px solid var(--dx-line); border-radius: 14px; margin-bottom: .55rem; background: #fff; transition: border-color .15s, box-shadow .15s; }
    .dx-row:hover { border-color: #c7d2fe; box-shadow: 0 6px 16px rgba(79,70,229,.08); }
    .dx-row.live { border-color: #4f46e5; background: #fafaff; box-shadow: 0 0 0 3px rgba(79,70,229,.08); }
    .dx-no { width: 40px; height: 40px; border-radius: 11px; background: #eef2ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }
    .dx-row.live .dx-no { background: #4f46e5; color: #fff; }
    .dx-row .nm { font-weight: 800; color: var(--dx-ink); font-size: .92rem; }
    .dx-row .mt { font-size: .78rem; color: var(--dx-muted); }
    .dx-row .vt { font-size: .76rem; color: #0f766e; font-weight: 700; }
    .dx-go { border: 0; border-radius: 11px; background: var(--dx-brand); color: #fff; font-weight: 800; font-size: .8rem; padding: .5rem .95rem; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; transition: background .15s; }
    .dx-go:hover { background: #4338ca; color: #fff; }
    .dx-empty { text-align: center; padding: 2rem 1rem; color: var(--dx-muted); font-size: .86rem; }
    .dx-empty i { display: block; font-size: 1.9rem; color: #cbd5e1; margin-bottom: .5rem; }

    /* ---------- Chart ---------- */
    .dx-chart { position: relative; height: 270px; }
    .dx-mini { display: grid; grid-template-columns: repeat(3, 1fr); gap: .6rem; margin-bottom: 1rem; }
    .dx-mini div { background: #f8fafc; border: 1px solid var(--dx-line); border-radius: 14px; padding: .65rem .8rem; }
    .dx-mini b { display: block; font-size: 1.15rem; font-weight: 800; color: var(--dx-ink); line-height: 1.1; }
    .dx-mini span { font-size: .72rem; color: var(--dx-muted); font-weight: 600; }

    /* ---------- Quick actions ---------- */
    .dx-qa { display: flex; align-items: center; gap: 12px; padding: .75rem .9rem; border: 1px solid var(--dx-line); border-radius: 14px; text-decoration: none; color: var(--dx-ink); font-weight: 700; font-size: .85rem; margin-bottom: .55rem; transition: all .15s; }
    .dx-qa:hover { border-color: #c7d2fe; background: #fafbff; color: var(--dx-ink); transform: translateX(2px); }
    .dx-qa .ic { width: 38px; height: 38px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }
    .dx-qa .ar { margin-left: auto; color: #cbd5e1; font-size: .75rem; }
    .dx-qa.main { background: linear-gradient(135deg, #4f46e5, #7c3aed); border-color: transparent; color: #fff; box-shadow: 0 10px 22px rgba(79,70,229,.25); }
    .dx-qa.main:hover { color: #fff; background: linear-gradient(135deg, #4338ca, #6d28d9); }
    .dx-qa.main .ic { background: rgba(255,255,255,.18); color: #fff; }
    .dx-qa.main .ar { color: rgba(255,255,255,.7); }

    .dx-chamber dt { font-size: .7rem; color: var(--dx-muted); text-transform: uppercase; letter-spacing: .06em; font-weight: 700; }
    .dx-chamber dd { font-size: .88rem; color: var(--dx-ink); font-weight: 600; margin: 0 0 .7rem; }

    @media (max-width: 575.98px) {
        .dx-hero { padding: 1.3rem 1.2rem; }
        .dx-hero h1 { font-size: 1.3rem; }
        .dx-counts { grid-template-columns: repeat(2, 1fr); }
        .dx-now { min-width: 0; width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) { .dx-dot, .dx-progress > span { animation: none; transition: none; } }
</style>
@endpush

@section('content')
@php
    $tz     = config('easydoc.timezone', 'Asia/Dhaka');
    $now    = now($tz);
    $hour   = (int) $now->format('G');
    $greet  = $hour < 5 ? 'শুভ রাত্রি' : ($hour < 12 ? 'শুভ সকাল' : ($hour < 16 ? 'শুভ দুপুর' : ($hour < 19 ? 'শুভ বিকাল' : 'শুভ সন্ধ্যা')));

    $activeChamber = $stats['active_chamber'] ?? null;

    $qTotal = (int) ($queueCounts['total'] ?? 0);
    $qWait  = (int) ($queueCounts['waiting'] ?? 0);
    $qLive  = (int) ($queueCounts['in_progress'] ?? 0);
    $qDone  = (int) ($queueCounts['done'] ?? 0);
    $qPct   = $qTotal > 0 ? round($qDone / $qTotal * 100) : 0;

    $trend      = collect($patientChart['data'] ?? []);
    $trendTotal = (int) $trend->sum();
    $trendAvg   = $trend->count() ? round($trendTotal / $trend->count(), 1) : 0;
    $trendPeak  = (int) ($trend->max() ?? 0);
@endphp

{{-- ================= Hero ================= --}}
<div class="dx-hero mb-4">
    <div class="dx-hero-inner">
        <div>
            <div class="dx-date"><i class="fa-regular fa-calendar"></i> {{ $now->format('l, d M Y') }}</div>
            <h1>{{ $greet }}, Dr. {{ auth()->user()->name }}</h1>
            <p>আজকের প্র্যাকটিস এক নজরে দেখে নিন।</p>
            <div class="dx-cta">
                <a href="{{ route('doctor.prescriptions.create') }}" class="dx-btn primary"><i class="fa-solid fa-file-medical"></i> নতুন প্রেসক্রিপশন</a>
                <a href="{{ route('doctor.queue.index') }}" class="dx-btn ghost"><i class="fa-solid fa-ticket"></i> সিরিয়াল কিউ</a>
            </div>
        </div>

        <div class="dx-now {{ $activeChamber ? '' : 'off' }}">
            <div class="lbl">এখন চালু চেম্বার</div>
            @if($activeChamber)
                <div class="nm"><span class="dx-dot"></span>{{ $activeChamber->chamber_name }}</div>
                @if($activeChamber->hospital_name)<div class="sub text-truncate" style="max-width:260px;">{{ $activeChamber->hospital_name }}</div>@endif
            @else
                <div class="nm"><span class="dx-dot red"></span>কোনো চেম্বার চালু নেই</div>
                <div class="sub">প্রেসক্রিপশন লিখতে একটি চেম্বার চালু করুন</div>
            @endif
        </div>
    </div>
</div>

@unless($activeChamber)
    <div class="dx-alert mb-4">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div><b>কোনো চেম্বার চালু নেই।</b> চেম্বার চালু না থাকলে প্রেসক্রিপশন Save করা যাবে না।</div>
        <a href="{{ route('doctor.chambers.index') }}">চেম্বার চালু করুন</a>
    </div>
@endunless

{{-- ================= Stats ================= --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="dx-stat">
            <div class="ico" style="background:#ede9fe;color:#7c3aed;"><i class="fa-solid fa-wallet"></i></div>
            <div class="min-w">
                <div class="num">৳{{ number_format($stats['wallet_balance'], 2) }}</div>
                <div class="lbl">Wallet ব্যালেন্স</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="dx-stat">
            <div class="ico" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-circle-check"></i></div>
            <div class="min-w">
                <div class="num sm text-truncate">{{ $activeChamber->chamber_name ?? 'None' }}</div>
                <div class="lbl">Active চেম্বার</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="dx-stat">
            <div class="ico" style="background:#eef2ff;color:#4f46e5;"><i class="fa-solid fa-hospital"></i></div>
            <div class="min-w">
                <div class="num">{{ $stats['total_chambers'] }}</div>
                <div class="lbl">মোট চেম্বার</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="dx-stat">
            <div class="ico" style="background:#ffedd5;color:#ea580c;"><i class="fa-solid fa-ticket"></i></div>
            <div class="min-w">
                <div class="num">{{ $qTotal }}</div>
                <div class="lbl">আজকের সিরিয়াল</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- ================= Left: queue + trend ================= --}}
    <div class="col-xl-8">

        <div class="dx-card mb-4">
            <div class="dx-card-head">
                <h2><span class="hi"><i class="fa-solid fa-ticket"></i></span> আজকের সিরিয়াল
                    <span class="sub">{{ $now->format('d M Y') }}</span>
                </h2>
                <a href="{{ route('doctor.queue.index') }}" class="dx-link">পুরো কিউ দেখুন <i class="fa-solid fa-arrow-right ms-1"></i></a>
            </div>

            <div class="dx-progress" role="progressbar" aria-valuenow="{{ $qPct }}" aria-valuemin="0" aria-valuemax="100" aria-label="আজকের সিরিয়াল অগ্রগতি">
                <span style="width: {{ $qPct }}%;"></span>
            </div>
            <div class="dx-progress-meta">
                <span>{{ $qDone }}/{{ $qTotal }} সম্পন্ন</span>
                <span>{{ $qPct }}%</span>
            </div>

            <div class="dx-counts">
                <div class="dx-count"><div class="n">{{ $qTotal }}</div><div class="l"><i style="background:#6366f1;"></i>মোট</div></div>
                <div class="dx-count"><div class="n">{{ $qWait }}</div><div class="l"><i style="background:#f59e0b;"></i>অপেক্ষায়</div></div>
                <div class="dx-count"><div class="n">{{ $qLive }}</div><div class="l"><i style="background:#22c55e;"></i>চলছে</div></div>
                <div class="dx-count"><div class="n">{{ $qDone }}</div><div class="l"><i style="background:#94a3b8;"></i>সম্পন্ন</div></div>
            </div>

            @forelse($queueSerials as $s)
                @php
                    $vitals = collect([
                        $s->bp ? "BP {$s->bp}" : null,
                        $s->pulse ? "P {$s->pulse}" : null,
                        $s->temperature ? "T {$s->temperature}" : null,
                    ])->filter()->implode(' · ');
                @endphp
                <div class="dx-row {{ $s->status === 'in_progress' ? 'live' : '' }}">
                    <div class="dx-no">{{ $s->serial_number }}</div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="nm text-truncate">{{ $s->patient->name }}</div>
                        <div class="mt text-truncate">
                            {{ $s->patient->mobile }}@if($s->patient->age) · {{ $s->patient->age }} বছর @endif
                            @if($s->reason) · {{ $s->reason }} @endif
                        </div>
                        @if($vitals)<div class="vt">{{ $vitals }}</div>@endif
                    </div>
                    <span class="badge rounded-pill {{ $s->badgeClass() }} d-none d-sm-inline">{{ $s->statusLabel() }}</span>
                    <a href="{{ route('doctor.prescriptions.create', ['serial' => $s->id]) }}" class="dx-go">
                        <i class="fa-solid fa-play"></i> {{ $s->status === 'in_progress' ? 'Resume' : 'Start' }}
                    </a>
                </div>
            @empty
                <div class="dx-empty">
                    <i class="fa-regular fa-calendar-check"></i>
                    আজ কোনো অপেক্ষমাণ সিরিয়াল নেই।
                </div>
            @endforelse
        </div>

        <div class="dx-card">
            <div class="dx-card-head">
                <h2><span class="hi"><i class="fa-solid fa-chart-line"></i></span> রোগী ট্রেন্ড</h2>
                <span class="sub">গত ১৪ দিনে প্রতিদিন কতজন ভিন্ন রোগী দেখা হয়েছে</span>
            </div>

            <div class="dx-mini">
                <div><b>{{ $trendTotal }}</b><span>মোট রোগী</span></div>
                <div><b>{{ $trendAvg }}</b><span>দৈনিক গড়</span></div>
                <div><b>{{ $trendPeak }}</b><span>সর্বোচ্চ (এক দিনে)</span></div>
            </div>

            <div class="dx-chart"><canvas id="patientTrendChart"></canvas></div>
        </div>
    </div>

    {{-- ================= Right: actions + chamber ================= --}}
    <div class="col-xl-4">

        <div class="dx-card mb-4">
            <div class="dx-card-head">
                <h2><span class="hi"><i class="fa-solid fa-bolt"></i></span> Quick Actions</h2>
            </div>

            <a href="{{ route('doctor.prescriptions.create') }}" class="dx-qa main">
                <span class="ic"><i class="fa-solid fa-file-medical"></i></span> নতুন প্রেসক্রিপশন লিখুন
                <i class="fa-solid fa-chevron-right ar"></i>
            </a>
            <a href="{{ route('doctor.prescriptions.index') }}" class="dx-qa">
                <span class="ic" style="background:#eef2ff;color:#4f46e5;"><i class="fa-solid fa-list-ul"></i></span> প্রেসক্রিপশন লিস্ট
                <i class="fa-solid fa-chevron-right ar"></i>
            </a>
            <a href="{{ route('doctor.patients.index') }}" class="dx-qa">
                <span class="ic" style="background:#ecfeff;color:#0e7490;"><i class="fa-solid fa-user-injured"></i></span> রোগীর তালিকা
                <i class="fa-solid fa-chevron-right ar"></i>
            </a>
            <a href="{{ route('doctor.queue.index') }}" class="dx-qa">
                <span class="ic" style="background:#ffedd5;color:#ea580c;"><i class="fa-solid fa-ticket"></i></span> সিরিয়াল কিউ
                <i class="fa-solid fa-chevron-right ar"></i>
            </a>
            <a href="{{ route('doctor.chambers.index') }}" class="dx-qa">
                <span class="ic" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-hospital"></i></span> আমার চেম্বার
                <i class="fa-solid fa-chevron-right ar"></i>
            </a>
            <a href="{{ route('doctor.favorites.index') }}" class="dx-qa mb-0">
                <span class="ic" style="background:#fef3c7;color:#b45309;"><i class="fa-solid fa-star"></i></span> প্রিয় ওষুধ
                <i class="fa-solid fa-chevron-right ar"></i>
            </a>
        </div>

        <div class="dx-card">
            <div class="dx-card-head">
                <h2><span class="hi"><i class="fa-solid fa-hospital"></i></span> চালু চেম্বার</h2>
                <a href="{{ route('doctor.chambers.index') }}" class="dx-link">ম্যানেজ</a>
            </div>
            @if($activeChamber)
                <dl class="dx-chamber mb-0">
                    <dt>চেম্বার</dt>
                    <dd>{{ $activeChamber->chamber_name }}</dd>
                    @if($activeChamber->hospital_name)
                        <dt>হাসপাতাল / ক্লিনিক</dt>
                        <dd>{{ $activeChamber->hospital_name }}</dd>
                    @endif
                    @if($activeChamber->visiting_days)
                        <dt>বসার দিন</dt>
                        <dd>{{ $activeChamber->visiting_days }}</dd>
                    @endif
                    @if($activeChamber->visiting_time)
                        <dt>সময়</dt>
                        <dd class="mb-0">{{ $activeChamber->visiting_time }}</dd>
                    @endif
                </dl>
            @else
                <div class="dx-empty py-3">
                    <i class="fa-solid fa-hospital"></i>
                    কোনো চেম্বার চালু নেই।
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const d = @json($patientChart);
    const el = document.getElementById('patientTrendChart');
    if (!el || !d) return;

    const ctx = el.getContext('2d');
    const grad = ctx.createLinearGradient(0, 0, 0, 270);
    grad.addColorStop(0, 'rgba(79,70,229,.25)');
    grad.addColorStop(1, 'rgba(79,70,229,0)');

    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    new Chart(el, {
        type: 'line',
        data: {
            labels: d.labels,
            datasets: [{
                label: 'রোগী সংখ্যা',
                data: d.data,
                borderColor: '#4f46e5',
                borderWidth: 2.5,
                backgroundColor: grad,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4f46e5',
                pointBorderWidth: 2,
                pointRadius: 3.5,
                pointHoverRadius: 6,
                tension: 0.38,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: reduce ? false : { duration: 700 },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: { label: function (c) { return c.parsed.y + ' জন রোগী'; } }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, color: '#94a3b8' }, grid: { color: '#f1f5f9' }, border: { display: false } },
                x: { ticks: { color: '#94a3b8', maxRotation: 0, autoSkip: true }, grid: { display: false }, border: { display: false } }
            }
        }
    });
})();
</script>
@endpush