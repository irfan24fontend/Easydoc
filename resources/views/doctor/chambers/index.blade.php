@extends('layouts.doctor')

@section('title', 'My Chambers')

@push('styles')
<style>
    :root { --ch-brand: #4f46e5; --ch-ink: #0f172a; --ch-muted: #64748b; --ch-line: #e5e9f0; }

    /* ---------- Hero ---------- */
    .ch-hero {
        position: relative; overflow: hidden; border-radius: 22px; padding: 1.6rem 1.8rem; color: #fff;
        background: radial-gradient(1200px 300px at 90% -20%, rgba(255,255,255,.18), transparent 60%),
                    linear-gradient(135deg, #4f46e5 0%, #6d28d9 55%, #7c3aed 100%);
        box-shadow: 0 14px 34px rgba(79,70,229,.28);
    }
    .ch-hero::after { content: ""; position: absolute; right: -60px; bottom: -80px; width: 240px; height: 240px; border-radius: 50%; background: rgba(255,255,255,.07); }
    .ch-hero h1 { font-size: 1.55rem; font-weight: 800; letter-spacing: -.02em; margin: 0; }
    .ch-hero p { margin: .3rem 0 0; opacity: .85; font-size: .88rem; max-width: 560px; }

    .now-box { background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.25); backdrop-filter: blur(6px); border-radius: 16px; padding: .85rem 1.1rem; min-width: 250px; position: relative; z-index: 1; }
    .now-box .lbl { font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 700; opacity: .8; }
    .now-box .nm { font-weight: 800; font-size: 1.02rem; line-height: 1.25; }
    .now-box.off { background: rgba(254,226,226,.18); border-color: rgba(254,202,202,.5); }

    .live-dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #4ade80; margin-right: 6px; box-shadow: 0 0 0 0 rgba(74,222,128,.7); animation: livePulse 1.8s infinite; }
    .live-dot.red { background: #f87171; box-shadow: none; animation: none; }
    @keyframes livePulse { 0% { box-shadow: 0 0 0 0 rgba(74,222,128,.6); } 70% { box-shadow: 0 0 0 9px rgba(74,222,128,0); } 100% { box-shadow: 0 0 0 0 rgba(74,222,128,0); } }

    /* ---------- Stats ---------- */
    .ch-stat { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; padding: .95rem 1.1rem; display: flex; align-items: center; gap: 13px; height: 100%; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .ch-stat .ico { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0; }
    .ch-stat .num { font-size: 1.4rem; font-weight: 800; color: var(--ch-ink); line-height: 1.05; }
    .ch-stat .lbl { font-size: .74rem; color: var(--ch-muted); font-weight: 600; }

    /* ---------- Toolbar ---------- */
    .ch-toolbar { background: #fff; border: 1px solid var(--ch-line); border-radius: 16px; padding: .75rem; display: flex; flex-wrap: wrap; gap: .6rem; align-items: center; }
    .ch-search { position: relative; flex: 1 1 260px; }
    .ch-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .85rem; }
    .ch-search input { width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: .55rem .9rem .55rem 2.3rem; font-size: .88rem; background: #f8fafc; transition: all .15s; }
    .ch-search input:focus { outline: none; background: #fff; border-color: var(--ch-brand); box-shadow: 0 0 0 4px rgba(79,70,229,.1); }

    .ch-tabs { display: flex; gap: .35rem; flex-wrap: wrap; }
    .ch-tab { border: 1px solid #e2e8f0; background: #fff; color: #475569; font-size: .78rem; font-weight: 700; padding: .45rem .85rem; border-radius: 999px; display: inline-flex; align-items: center; gap: 7px; cursor: pointer; transition: all .15s; }
    .ch-tab:hover { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
    .ch-tab .c { background: #f1f5f9; color: #475569; border-radius: 999px; padding: 0 .5rem; font-size: .7rem; }
    .ch-tab.active { background: var(--ch-brand); border-color: var(--ch-brand); color: #fff; box-shadow: 0 4px 12px rgba(79,70,229,.25); }
    .ch-tab.active .c { background: rgba(255,255,255,.25); color: #fff; }

    /* ---------- Cards ---------- */
    .ch-card {
        position: relative; background: #fff; border: 1px solid var(--ch-line); border-radius: 20px; overflow: hidden; height: 100%;
        display: flex; flex-direction: column; box-shadow: 0 1px 3px rgba(15,23,42,.05);
        transition: box-shadow .2s, transform .2s, border-color .2s;
        animation: cardIn .45s ease both; animation-delay: calc(var(--i, 0) * 60ms);
    }
    @keyframes cardIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
    .ch-card:hover { box-shadow: 0 16px 32px rgba(15,23,42,.1); transform: translateY(-2px); }
    .ch-card::before { content: ""; position: absolute; inset: 0 0 auto 0; height: 4px; background: #cbd5e1; }
    .ch-card.working { border-color: #86efac; box-shadow: 0 0 0 3px rgba(34,197,94,.12), 0 10px 26px rgba(15,23,42,.07); }
    .ch-card.working::before { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .ch-card.off::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .ch-card.locked { background: #fafafa; }
    .ch-card.locked::before { background: linear-gradient(90deg, #ef4444, #f87171); }
    .ch-card.locked .ch-body { opacity: .72; }
    .ch-card.is-busy { pointer-events: none; }
    .ch-card.is-busy::after { content: ""; position: absolute; inset: 0; background: rgba(255,255,255,.65); backdrop-filter: blur(1px); }
    .ch-spin { position: absolute; z-index: 2; top: 50%; left: 50%; width: 34px; height: 34px; margin: -17px 0 0 -17px; border-radius: 50%; border: 3px solid #c7d2fe; border-top-color: var(--ch-brand); animation: spin .7s linear infinite; display: none; }
    .ch-card.is-busy .ch-spin { display: block; }
    @keyframes spin { to { transform: rotate(360deg); } }

    .ch-top { display: flex; align-items: center; gap: 12px; padding: 1.25rem 1.25rem 1rem; }
    .ch-icon { width: 46px; height: 46px; border-radius: 14px; background: linear-gradient(135deg,#4f46e5,#7c3aed); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 6px 14px rgba(79,70,229,.25); }
    .ch-card.locked .ch-icon { background: #cbd5e1; box-shadow: none; }
    .ch-card.off .ch-icon { background: linear-gradient(135deg,#94a3b8,#64748b); box-shadow: none; }
    .ch-top h5 { margin: 0; font-size: 1.02rem; font-weight: 800; color: var(--ch-ink); }
    .ch-top .sub { font-size: .78rem; color: var(--ch-muted); }

    .ch-flag { margin-left: auto; font-size: .66rem; font-weight: 800; letter-spacing: .03em; padding: .28rem .65rem; border-radius: 999px; white-space: nowrap; display: inline-flex; align-items: center; gap: 5px; }
    .ch-flag.working { background: #dcfce7; color: #15803d; }
    .ch-flag.off { background: #fef3c7; color: #b45309; }
    .ch-flag.locked { background: #fee2e2; color: #b91c1c; }
    .ch-flag .live-dot { margin: 0; width: 7px; height: 7px; }

    .ch-body { padding: 0 1.25rem 1rem; flex: 1; }
    .ch-info { list-style: none; padding: 0; margin: 0; }
    .ch-info li { display: flex; align-items: flex-start; gap: 11px; font-size: .84rem; color: #334155; padding: .38rem 0; }
    .ch-info li > i { width: 28px; height: 28px; border-radius: 9px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; font-size: .78rem; flex-shrink: 0; }
    .ch-info .txt { min-width: 0; flex: 1; padding-top: 3px; word-break: break-word; }
    .ch-info a { color: inherit; text-decoration: none; }
    .ch-info a:hover { color: var(--ch-brand); }
    .mini-act { border: 0; background: #eef2ff; color: #4338ca; width: 26px; height: 26px; border-radius: 8px; font-size: .72rem; display: inline-flex; align-items: center; justify-content: center; margin-left: 6px; cursor: pointer; transition: all .15s; }
    .mini-act:hover { background: var(--ch-brand); color: #fff; }

    .day-chip { display: inline-block; font-size: .72rem; font-weight: 700; padding: .18rem .55rem; border-radius: 999px; background: #eef2ff; color: #4338ca; margin: 0 4px 4px 0; }
    .time-chip { display: inline-block; font-size: .72rem; font-weight: 700; padding: .18rem .55rem; border-radius: 999px; background: #ecfeff; color: #0e7490; }
    .tpl-badge { font-size: .72rem; font-weight: 700; background: #f5f3ff; color: #6d28d9; padding: .2rem .55rem; border-radius: 8px; }

    /* ---------- Footer / control ---------- */
    .ch-foot { border-top: 1px solid #f1f5f9; background: #f8fafc; padding: .95rem 1.25rem; }
    .ch-row { display: flex; justify-content: space-between; align-items: center; gap: 10px; font-size: .8rem; color: #475569; }
    .ch-row + .ch-row { margin-top: .55rem; }
    .ch-row .k { font-weight: 600; }
    .pill { font-size: .7rem; font-weight: 700; padding: .25rem .65rem; border-radius: 999px; display: inline-flex; align-items: center; gap: 5px; }
    .pill .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .pill.on { background: #dcfce7; color: #15803d; }
    .pill.bad { background: #fee2e2; color: #b91c1c; }

    .sw-wrap { display: inline-flex; align-items: center; gap: 10px; }
    .sw-label { font-size: .76rem; font-weight: 800; min-width: 54px; text-align: right; color: #64748b; }
    .sw-label.on { color: #15803d; }
    .sw { position: relative; display: inline-block; width: 52px; height: 28px; margin: 0; cursor: pointer; flex-shrink: 0; }
    .sw input { opacity: 0; width: 100%; height: 100%; position: absolute; inset: 0; margin: 0; cursor: pointer; z-index: 2; }
    .sw .track { position: absolute; inset: 0; background: #cbd5e1; border-radius: 999px; transition: .25s; }
    .sw .track::before { content: ""; position: absolute; width: 22px; height: 22px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .25s; box-shadow: 0 1px 4px rgba(0,0,0,.3); }
    .sw input:checked + .track { background: linear-gradient(135deg,#22c55e,#16a34a); }
    .sw input:checked + .track::before { transform: translateX(24px); }
    .sw input:focus-visible + .track { box-shadow: 0 0 0 4px rgba(79,70,229,.25); }
    .sw.disabled { cursor: not-allowed; opacity: .45; }
    .sw.disabled input { cursor: not-allowed; }

    .ch-lockmsg { margin-top: .65rem; font-size: .74rem; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: .45rem .65rem; }

    /* ---------- Rules / empty ---------- */
    .ch-rules { background: #fff; border: 1px solid var(--ch-line); border-radius: 16px; }
    .ch-rules summary { cursor: pointer; padding: .8rem 1.1rem; font-weight: 700; font-size: .85rem; color: #3730a3; list-style: none; display: flex; align-items: center; gap: 8px; }
    .ch-rules summary::-webkit-details-marker { display: none; }
    .ch-rules summary .chev { margin-left: auto; transition: transform .2s; color: #94a3b8; }
    .ch-rules[open] summary .chev { transform: rotate(180deg); }
    .ch-rules ul { margin: 0; padding: 0 1.1rem 1rem 2.3rem; font-size: .84rem; color: #475569; }
    .ch-rules li { margin-bottom: .25rem; }

    .ch-empty { text-align: center; padding: 3.5rem 1rem; background: #fff; border: 2px dashed #e2e8f0; border-radius: 20px; }
    .ch-empty i { font-size: 2.6rem; color: #cbd5e1; margin-bottom: .6rem; }

    .ch-toast { position: fixed; right: 20px; bottom: 20px; z-index: 2000; background: #0f172a; color: #fff; font-size: .82rem; font-weight: 600; padding: .6rem 1rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,.25); opacity: 0; transform: translateY(8px); transition: all .25s; pointer-events: none; }
    .ch-toast.show { opacity: 1; transform: none; }

    #chConfirm .modal-content { border: 0; border-radius: 22px; overflow: hidden; }
    .cf-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.45rem; margin: 0 auto; background: #fef3c7; color: #d97706; }
    .cf-icon.green { background: #dcfce7; color: #16a34a; }

    @media (prefers-reduced-motion: reduce) { .ch-card, .live-dot { animation: none; } }
</style>
@endpush

@section('content')
@php
    $total      = $chambers->count();
    $lockedCnt  = $chambers->filter(fn ($c) => !$c->status)->count();
    $enabledCnt = $total - $lockedCnt;
    $workingCnt = $chambers->filter(fn ($c) => $c->status && $c->doctor_active)->count();
    $offCnt     = $enabledCnt - $workingCnt;
@endphp

<div class="py-2">
    <div class="container-fluid px-0">

        {{-- ================= Hero ================= --}}
        <div class="ch-hero mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 position-relative" style="z-index:1;">
                <div>
                    <h1>আমার চেম্বার <span class="fw-normal opacity-75" style="font-size:1rem;">/ My Chambers</span></h1>
                    <p>Super Admin যে চেম্বারগুলো যোগ করেছেন সেগুলো এখানে দেখুন। যে চেম্বারে প্রেসক্রিপশন লিখবেন, সেটির সুইচ চালু করুন।</p>
                </div>

                <div class="now-box {{ $working ? '' : 'off' }}">
                    <div class="lbl">এখন প্রেসক্রিপশন লেখা হচ্ছে</div>
                    @if($working)
                        <div class="nm"><span class="live-dot"></span>{{ $working->chamber_name }}</div>
                        <div class="small opacity-75 text-truncate" style="max-width:260px;">{{ $working->hospital_name }}</div>
                    @else
                        <div class="nm"><span class="live-dot red"></span>কোনো চেম্বার চালু নেই</div>
                        <div class="small opacity-75">
                            {{ $enabledCnt > 0 ? 'নিচ থেকে একটি চেম্বার চালু করুন' : 'Super Admin-এর কাছ থেকে চেম্বার Active করিয়ে নিন' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================= Stats ================= --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="ch-stat">
                    <div class="ico" style="background:#eef2ff;color:#4f46e5;"><i class="fa-solid fa-hospital"></i></div>
                    <div><div class="num">{{ $total }}</div><div class="lbl">মোট চেম্বার</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="ch-stat">
                    <div class="ico" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-circle-check"></i></div>
                    <div><div class="num">{{ $workingCnt }}</div><div class="lbl">চালু আছে</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="ch-stat">
                    <div class="ico" style="background:#fef3c7;color:#b45309;"><i class="fa-solid fa-user-shield"></i></div>
                    <div><div class="num">{{ $enabledCnt }}</div><div class="lbl">Super Admin Active</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="ch-stat">
                    <div class="ico" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-lock"></i></div>
                    <div><div class="num">{{ $lockedCnt }}</div><div class="lbl">লক করা</div></div>
                </div>
            </div>
        </div>

        {{-- Warning when nothing is on --}}
        @if(!$working && $chambers->isNotEmpty())
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start gap-2" style="border-radius:14px;">
                <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                <div>
                    <b>এখন কোনো চেম্বার চালু নেই</b>, তাই প্রেসক্রিপশন Save করা যাবে না।
                    @if($enabledCnt > 0)
                        নিচের যেকোনো একটি চেম্বারের সুইচ চালু করুন।
                    @else
                        Super Admin-এর কাছ থেকে একটি চেম্বার Active করিয়ে নিন।
                    @endif
                </div>
            </div>
        @endif

        {{-- Rules --}}
        <details class="ch-rules mb-4">
            <summary><i class="fa-solid fa-circle-info"></i> কীভাবে কাজ করে <i class="fa-solid fa-chevron-down chev small"></i></summary>
            <ul>
                <li>চেম্বার যোগ করা এবং তা <b>Active/Inactive</b> রাখার অনুমতি দেওয়া Super Admin-এর কাজ।</li>
                <li>Super Admin যে চেম্বার Active রেখেছেন, সেটি আপনি নিজে <b>চালু বা বন্ধ</b> করতে পারবেন।</li>
                <li>Super Admin Inactive করলে চেম্বারটি লক হয়ে যায়, আপনি চালু করতে পারবেন না।</li>
                <li>একসাথে শুধু <b>একটি</b> চেম্বার চালু থাকে। অন্যটি চালু করলে আগেরটি বন্ধ হয়ে যায়।</li>
            </ul>
        </details>

        @if($chambers->isEmpty())
            <div class="ch-empty">
                <i class="fa-solid fa-hospital-user"></i>
                <h5 class="fw-bold mt-2">এখনো কোনো চেম্বার যোগ করা হয়নি</h5>
                <p class="text-muted mb-0">Super Admin আপনার জন্য চেম্বার যোগ করলে এখানে দেখতে পাবেন।</p>
            </div>
        @else

            {{-- ================= Toolbar ================= --}}
            <div class="ch-toolbar mb-4">
                <div class="ch-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="chSearch" placeholder="চেম্বার, হাসপাতাল বা ঠিকানা দিয়ে খুঁজুন..." autocomplete="off">
                </div>
                <div class="ch-tabs" id="chTabs">
                    <button type="button" class="ch-tab active" data-filter="all">সব <span class="c">{{ $total }}</span></button>
                    <button type="button" class="ch-tab" data-filter="working"><i class="fa-solid fa-circle-check"></i> চালু <span class="c">{{ $workingCnt }}</span></button>
                    <button type="button" class="ch-tab" data-filter="off"><i class="fa-solid fa-power-off"></i> বন্ধ <span class="c">{{ $offCnt }}</span></button>
                    <button type="button" class="ch-tab" data-filter="locked"><i class="fa-solid fa-lock"></i> লক <span class="c">{{ $lockedCnt }}</span></button>
                </div>
            </div>

            {{-- ================= Cards ================= --}}
            <div class="row g-4" id="chGrid">
                @foreach($chambers as $chamber)
                    @php
                        $adminOn = (bool) $chamber->status;
                        $docOn   = (bool) $chamber->doctor_active;
                        $isWork  = $adminOn && $docOn;
                        $state   = $isWork ? 'working' : ($adminOn ? 'off' : 'locked');
                        $days    = collect(preg_split('/\s*[,\/|·،]+\s*/u', (string) $chamber->visiting_days))->map('trim')->filter()->values();
                        $search  = mb_strtolower(trim($chamber->chamber_name . ' ' . $chamber->hospital_name . ' ' . $chamber->address . ' ' . $chamber->phone));
                    @endphp
                    <div class="col-md-6 col-xl-4 ch-item" data-state="{{ $state }}" data-search="{{ $search }}">
                        <div class="ch-card {{ $state }}" style="--i: {{ $loop->index }};">
                            <div class="ch-spin"></div>

                            <div class="ch-top">
                                <div class="ch-icon"><i class="fa-solid fa-hospital"></i></div>
                                <div style="min-width:0;">
                                    <h5 class="text-truncate">{{ $chamber->chamber_name }}</h5>
                                    <div class="sub text-truncate">{{ $chamber->hospital_name }}</div>
                                </div>
                                @if($isWork)
                                    <span class="ch-flag working"><span class="live-dot"></span>চালু আছে</span>
                                @elseif($adminOn)
                                    <span class="ch-flag off"><i class="fa-solid fa-power-off"></i>বন্ধ</span>
                                @else
                                    <span class="ch-flag locked"><i class="fa-solid fa-lock"></i>লক</span>
                                @endif
                            </div>

                            <div class="ch-body">
                                <ul class="ch-info">
                                    @if($chamber->phone)
                                        <li>
                                            <i class="fa-solid fa-phone"></i>
                                            <span class="txt">
                                                <a href="tel:{{ preg_replace('/\s+/', '', $chamber->phone) }}">{{ $chamber->phone }}</a>
                                                <button type="button" class="mini-act copy-btn" data-copy="{{ $chamber->phone }}" title="কপি করুন"><i class="fa-regular fa-copy"></i></button>
                                            </span>
                                        </li>
                                    @endif
                                    @if($chamber->email)
                                        <li><i class="fa-solid fa-envelope"></i><span class="txt"><a href="mailto:{{ $chamber->email }}">{{ $chamber->email }}</a></span></li>
                                    @endif
                                    @if($chamber->address)
                                        <li>
                                            <i class="fa-solid fa-location-dot"></i>
                                            <span class="txt">
                                                {{ $chamber->address }}
                                                <a class="mini-act" target="_blank" rel="noopener" title="ম্যাপে দেখুন"
                                                   href="https://www.google.com/maps/search/?api=1&query={{ urlencode($chamber->address) }}"><i class="fa-solid fa-map"></i></a>
                                            </span>
                                        </li>
                                    @endif
                                    @if($days->isNotEmpty() || $chamber->visiting_time)
                                        <li>
                                            <i class="fa-solid fa-calendar-days"></i>
                                            <span class="txt">
                                                @foreach($days as $d)<span class="day-chip">{{ $d }}</span>@endforeach
                                                @if($chamber->visiting_time)<span class="time-chip"><i class="fa-regular fa-clock me-1"></i>{{ $chamber->visiting_time }}</span>@endif
                                            </span>
                                        </li>
                                    @endif
                                    <li>
                                        <i class="fa-solid fa-file-lines"></i>
                                        <span class="txt">
                                            @if($chamber->template)
                                                <span class="tpl-badge">{{ $chamber->template->name }}</span>
                                            @else
                                                <span class="text-muted">টেমপ্লেট অ্যাসাইন করা হয়নি</span>
                                            @endif
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <div class="ch-foot">
                                <div class="ch-row">
                                    <span class="k"><i class="fa-solid fa-user-shield me-1 text-muted"></i> Super Admin</span>
                                    @if($adminOn)
                                        <span class="pill on"><span class="dot"></span> Active</span>
                                    @else
                                        <span class="pill bad"><span class="dot"></span> Inactive</span>
                                    @endif
                                </div>

                                <div class="ch-row">
                                    <span class="k"><i class="fa-solid fa-power-off me-1 text-muted"></i> আপনার সুইচ</span>

                                    <form method="POST" action="{{ route('doctor.chambers.toggle', $chamber->id) }}"
                                          class="toggle-form sw-wrap" data-name="{{ $chamber->chamber_name }}">
                                        @csrf
                                        @method('PATCH')
                                        <span class="sw-label {{ $isWork ? 'on' : '' }}">{{ $isWork ? 'চালু' : 'বন্ধ' }}</span>
                                        <label class="sw {{ $adminOn ? '' : 'disabled' }}" title="{{ $adminOn ? ($docOn ? 'বন্ধ করুন' : 'চালু করুন') : 'Super Admin Inactive করে রেখেছেন' }}">
                                            <input type="checkbox" role="switch" aria-label="{{ $chamber->chamber_name }} চালু/বন্ধ"
                                                   {{ $isWork ? 'checked' : '' }} {{ $adminOn ? '' : 'disabled' }}>
                                            <span class="track"></span>
                                        </label>
                                    </form>
                                </div>

                                @unless($adminOn)
                                    <div class="ch-lockmsg"><i class="fa-solid fa-lock me-1"></i> Super Admin এই চেম্বার Inactive করে রেখেছেন। চালু করতে তাঁর সাথে যোগাযোগ করুন।</div>
                                @endunless
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="ch-empty d-none" id="chNoMatch">
                <i class="fa-solid fa-magnifying-glass"></i>
                <h6 class="fw-bold mt-2 mb-1">কোনো চেম্বার মেলেনি</h6>
                <p class="text-muted small mb-0">অন্য শব্দ দিয়ে খুঁজুন বা ফিল্টার বদলান।</p>
            </div>
        @endif
    </div>
</div>

<div class="ch-toast" id="chToast"></div>

{{-- Confirm popup --}}
<div class="modal fade" id="chConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 430px;">
        <div class="modal-content shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="cf-icon" id="cfIcon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <h5 class="fw-bold mt-3 mb-2" id="cfTitle">নিশ্চিত করুন</h5>
                <p class="text-muted mb-0" id="cfMessage" style="font-size:.9rem;"></p>
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-light border fw-semibold flex-fill" id="cfCancel">না</button>
                    <button type="button" class="btn btn-primary fw-bold flex-fill" id="cfOk" style="background:#4f46e5;border-color:#4f46e5;">হ্যাঁ</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const WORKING = @json($working?->chamber_name);

    /* ---------- Confirm modal ---------- */
    const modalEl = document.getElementById('chConfirm');
    const modal = new bootstrap.Modal(modalEl);

    function ask(title, message, okText, tone) {
        return new Promise(function (resolve) {
            document.getElementById('cfTitle').textContent = title;
            document.getElementById('cfMessage').textContent = message;
            document.getElementById('cfOk').textContent = okText || 'হ্যাঁ';
            const icon = document.getElementById('cfIcon');
            icon.classList.toggle('green', tone === 'green');
            icon.innerHTML = tone === 'green' ? '<i class="fa-solid fa-power-off"></i>' : '<i class="fa-solid fa-triangle-exclamation"></i>';

            let result = false;
            document.getElementById('cfOk').onclick = function () { result = true; modal.hide(); };
            document.getElementById('cfCancel').onclick = function () { result = false; modal.hide(); };
            const done = function () { modalEl.removeEventListener('hidden.bs.modal', done); resolve(result); };
            modalEl.addEventListener('hidden.bs.modal', done);
            modal.show();
        });
    }

    /* ---------- Toggle switches ---------- */
    document.querySelectorAll('.toggle-form').forEach(function (form) {
        const box = form.querySelector('input[type="checkbox"]');
        if (!box || box.disabled) return;

        box.addEventListener('change', async function () {
            const turningOn = box.checked;
            const name = form.dataset.name;
            let ok = true;

            if (!turningOn) {
                ok = await ask('"' + name + '" বন্ধ করবেন?',
                    'বন্ধ করলে প্রেসক্রিপশন লেখার জন্য কোনো চেম্বার চালু থাকবে না, যতক্ষণ না অন্য একটি চেম্বার চালু করছেন।',
                    'হ্যাঁ, বন্ধ করুন');
            } else if (WORKING && WORKING !== name) {
                ok = await ask('"' + name + '" চালু করবেন?',
                    'এটি চালু করলে বর্তমান চেম্বার "' + WORKING + '" বন্ধ হয়ে যাবে। (একসাথে একটি চেম্বারই চালু থাকে)',
                    'হ্যাঁ, চালু করুন', 'green');
            }

            if (!ok) { box.checked = !turningOn; return; }

            // Loading state: card-e spinner, shob switch lock
            form.closest('.ch-card').classList.add('is-busy');
            // Shudhu checkbox-gulo lock hobe. Hidden _method / _token input disable korle PATCH ar CSRF jay na (405 error ashe).
            document.querySelectorAll('.toggle-form input[type="checkbox"]').forEach(function (i) { i.disabled = true; });
            form.submit();
        });
    });

    /* ---------- Search + filter tabs ---------- */
    const items = Array.from(document.querySelectorAll('.ch-item'));
    const searchEl = document.getElementById('chSearch');
    const tabs = Array.from(document.querySelectorAll('.ch-tab'));
    const noMatch = document.getElementById('chNoMatch');
    let filter = 'all';

    function applyFilter() {
        const q = (searchEl ? searchEl.value : '').trim().toLowerCase();
        let shown = 0;
        items.forEach(function (el) {
            const okState = filter === 'all' || el.dataset.state === filter;
            const okText = q === '' || el.dataset.search.indexOf(q) !== -1;
            const show = okState && okText;
            el.classList.toggle('d-none', !show);
            if (show) shown++;
        });
        if (noMatch) noMatch.classList.toggle('d-none', shown !== 0);
    }

    if (searchEl) searchEl.addEventListener('input', applyFilter);
    tabs.forEach(function (t) {
        t.addEventListener('click', function () {
            filter = t.dataset.filter;
            tabs.forEach(function (x) { x.classList.toggle('active', x === t); });
            applyFilter();
        });
    });

    /* ---------- Copy phone ---------- */
    const toast = document.getElementById('chToast');
    let toastTimer;
    function showToast(msg) {
        toast.textContent = msg;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toast.classList.remove('show'); }, 1800);
    }

    document.querySelectorAll('.copy-btn').forEach(function (b) {
        b.addEventListener('click', async function () {
            const text = b.dataset.copy || '';
            try {
                await navigator.clipboard.writeText(text);
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = text; document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); } catch (err) {}
                document.body.removeChild(ta);
            }
            showToast('কপি হয়েছে: ' + text);
        });
    });
})();
</script>
@endpush