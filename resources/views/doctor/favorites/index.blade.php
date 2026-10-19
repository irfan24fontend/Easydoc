@extends('layouts.doctor')

@section('title', 'Favorite Medicines')

@push('styles')
<style>
    .fv { --ink:#0f172a; --muted:#64748b; --line:#e8ecf3; --soft:#f6f8fc; --brand:#4f46e5; --brand-soft:#eef2ff; --amber:#b45309; --amber-soft:#fef3c7; --green:#047857; --green-soft:#d1fae5; --red:#b91c1c; --red-soft:#fee2e2; color:var(--ink); }
    .fv *, .fv *::before, .fv *::after { box-sizing:border-box; }

    /* ---------- Header ---------- */
    .fv-head { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
    .fv-title { display:flex; align-items:center; gap:.85rem; }
    .fv-title .badge-ic { width:46px; height:46px; border-radius:14px; display:grid; place-items:center; color:#fff; font-size:1.15rem; background:linear-gradient(135deg,#6366f1,#7c3aed); box-shadow:0 8px 20px rgba(99,102,241,.3); flex:none; }
    .fv-title h1 { font-size:1.35rem; font-weight:800; letter-spacing:-.02em; margin:0; }
    .fv-title p { margin:.1rem 0 0; font-size:.84rem; color:var(--muted); }
    .fv-head-actions { display:flex; gap:.5rem; flex-wrap:wrap; }
    .fv-btn { display:inline-flex; align-items:center; gap:.45rem; border:1px solid var(--line); background:#fff; color:var(--ink); font-weight:700; font-size:.84rem; padding:.55rem .95rem; border-radius:12px; cursor:pointer; transition:.15s; }
    .fv-btn:hover { background:var(--soft); border-color:#d5dbe7; }
    .fv-btn.primary { background:var(--brand); border-color:var(--brand); color:#fff; box-shadow:0 6px 16px rgba(79,70,229,.28); }
    .fv-btn.primary:hover { background:#4338ca; }

    /* ---------- Stats ---------- */
    .fv-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; margin-bottom:1rem; }
    .fv-stat { background:#fff; border:1px solid var(--line); border-radius:16px; padding:.85rem 1rem; display:flex; align-items:center; gap:.75rem; }
    .fv-stat .ic { width:38px; height:38px; border-radius:12px; display:grid; place-items:center; font-size:.95rem; flex:none; }
    .fv-stat .ic.a { background:var(--amber-soft); color:var(--amber); }
    .fv-stat .ic.b { background:var(--brand-soft); color:var(--brand); }
    .fv-stat .ic.c { background:var(--green-soft); color:var(--green); }
    .fv-stat b { display:block; font-size:1.3rem; line-height:1.1; font-weight:800; }
    .fv-stat span { font-size:.75rem; color:var(--muted); font-weight:600; }

    /* ---------- Ethics strip ---------- */
    .fv-ethic-bar { display:flex; align-items:center; gap:.7rem; background:#fffbeb; border:1px solid #fde68a; border-radius:14px; padding:.65rem .9rem; margin-bottom:1.1rem; font-size:.82rem; color:#78350f; }
    .fv-ethic-bar i.lead { color:#d97706; font-size:1rem; flex:none; }
    .fv-ethic-bar a { margin-left:auto; flex:none; font-weight:800; color:#92400e; text-decoration:none; white-space:nowrap; cursor:pointer; }
    .fv-ethic-bar a:hover { text-decoration:underline; }

    /* ---------- Cards ---------- */
    .fv-card { background:#fff; border:1px solid var(--line); border-radius:20px; padding:1.15rem 1.25rem; box-shadow:0 1px 2px rgba(15,23,42,.03); height:100%; }
    .fv-card-h { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.9rem; }
    .fv-card-h h2 { font-size:1rem; font-weight:800; margin:0; display:flex; align-items:center; gap:.5rem; }
    .fv-card-h h2 i { color:var(--brand); }
    .fv-hint { font-size:.78rem; color:var(--muted); margin:-.4rem 0 .9rem; line-height:1.6; }

    /* ---------- Search / autocomplete ---------- */
    .fv-search { position:relative; }
    .fv-input { width:100%; border:1px solid var(--line); background:var(--soft); border-radius:12px; padding:.62rem .85rem; font-size:.88rem; color:var(--ink); transition:.15s; }
    .fv-input:focus { outline:none; background:#fff; border-color:#a5b4fc; box-shadow:0 0 0 4px rgba(99,102,241,.12); }
    .fv-input.is-invalid { border-color:#f87171; box-shadow:0 0 0 4px rgba(248,113,113,.15); }
    .fv-input-ic { position:relative; }
    .fv-input-ic i { position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:.8rem; pointer-events:none; }
    .fv-input-ic .fv-input { padding-left:2.2rem; }
    .fv-results { position:absolute; left:0; right:0; top:calc(100% + 6px); z-index:1060; background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:0 18px 40px rgba(15,23,42,.16); max-height:280px; overflow-y:auto; display:none; padding:.3rem; }
    .fv-results.show { display:block; }
    .fv-opt { width:100%; text-align:left; border:0; background:transparent; padding:.55rem .7rem; display:flex; justify-content:space-between; align-items:center; gap:.6rem; cursor:pointer; border-radius:10px; }
    .fv-opt:hover, .fv-opt:focus { background:var(--brand-soft); outline:none; }
    .fv-opt .t { font-weight:700; font-size:.88rem; }
    .fv-opt .s { font-size:.74rem; color:var(--muted); }
    .fv-none { padding:.8rem; font-size:.82rem; color:var(--muted); }

    /* ---------- Tags ---------- */
    .fv-tag { display:inline-flex; align-items:center; gap:.3rem; font-size:.68rem; font-weight:800; padding:.2rem .55rem; border-radius:999px; white-space:nowrap; }
    .fv-tag.fav { background:var(--amber-soft); color:var(--amber); }
    .fv-tag.def { background:var(--brand-soft); color:#3730a3; }
    .fv-tag.brand { background:var(--green-soft); color:#065f46; }

    /* ---------- Brands ---------- */
    .fv-brand { display:flex; align-items:center; gap:.7rem; padding:.55rem .6rem; border-radius:14px; }
    .fv-brand:hover { background:var(--soft); }
    .fv-av { width:36px; height:36px; border-radius:11px; display:grid; place-items:center; font-weight:800; font-size:.9rem; flex:none; background:var(--green-soft); color:#065f46; }
    .fv-brand .nm { flex:1; min-width:0; font-weight:700; font-size:.9rem; overflow-wrap:anywhere; }
    .fv-x { width:30px; height:30px; border-radius:9px; border:0; background:transparent; color:#94a3b8; display:grid; place-items:center; cursor:pointer; transition:.15s; }
    .fv-x:hover { background:var(--red-soft); color:var(--red); }

    /* ---------- Medicine list ---------- */
    .fv-tools { display:flex; flex-wrap:wrap; gap:.6rem; align-items:center; margin-bottom:.8rem; }
    .fv-tools .fv-input-ic { flex:1; min-width:180px; }
    .fv-pills { display:flex; gap:.3rem; background:var(--soft); padding:.25rem; border-radius:12px; }
    .fv-pill { border:0; background:transparent; font-size:.78rem; font-weight:700; color:var(--muted); padding:.38rem .75rem; border-radius:9px; cursor:pointer; }
    .fv-pill.active { background:#fff; color:var(--brand); box-shadow:0 1px 3px rgba(15,23,42,.1); }

    .fv-row { display:grid; grid-template-columns:minmax(0,1.3fr) minmax(0,1fr) auto; gap:.9rem; align-items:center; padding:.8rem .7rem; border-radius:14px; border-bottom:1px solid #f1f4f9; }
    .fv-row:last-child { border-bottom:0; }
    .fv-row:hover { background:var(--soft); }
    .fv-med { display:flex; gap:.7rem; align-items:center; min-width:0; }
    .fv-med .av { background:var(--brand-soft); color:var(--brand); }
    .fv-med .nm { font-weight:800; font-size:.92rem; overflow-wrap:anywhere; }
    .fv-med .sub { font-size:.76rem; color:var(--muted); overflow-wrap:anywhere; }
    .fv-tags { display:flex; gap:.3rem; flex-wrap:wrap; margin-top:.3rem; }
    .fv-dose { font-size:.8rem; line-height:1.5; }
    .fv-dose .pill { display:inline-block; background:#f1f5f9; border-radius:8px; padding:.15rem .55rem; font-weight:800; margin-bottom:.15rem; }
    .fv-dose .mut { color:var(--muted); }
    .fv-acts { display:flex; gap:.3rem; }
    .fv-ic-btn { width:34px; height:34px; border-radius:10px; border:1px solid var(--line); background:#fff; color:#475569; display:grid; place-items:center; cursor:pointer; transition:.15s; }
    .fv-ic-btn:hover { background:var(--brand-soft); color:var(--brand); border-color:#c7d2fe; }
    .fv-ic-btn.danger:hover { background:var(--red-soft); color:var(--red); border-color:#fecaca; }

    .fv-empty { text-align:center; padding:2.2rem 1rem; color:var(--muted); font-size:.88rem; }
    .fv-empty .big { width:60px; height:60px; margin:0 auto .8rem; border-radius:18px; background:var(--brand-soft); color:var(--brand); display:grid; place-items:center; font-size:1.5rem; }
    .fv-empty b { display:block; color:var(--ink); font-size:.95rem; margin-bottom:.2rem; }

    /* ---------- Modal ---------- */
    .fv-modal .modal-content { border:0; border-radius:22px; overflow:hidden; }
    .fv-modal .modal-header { border:0; padding:1.2rem 1.4rem .4rem; }
    .fv-modal .modal-title { font-weight:800; font-size:1.1rem; }
    .fv-modal .modal-body { padding:.8rem 1.4rem 1rem; }
    .fv-modal .modal-footer { border:0; padding:.6rem 1.4rem 1.2rem; gap:.5rem; }
    .fv-lbl { display:block; font-size:.78rem; font-weight:800; color:#334155; margin-bottom:.35rem; }
    .fv-lbl small { font-weight:600; color:var(--muted); }
    .fv-picked { display:none; align-items:center; gap:.7rem; background:var(--brand-soft); border:1px solid #c7d2fe; border-radius:14px; padding:.65rem .8rem; }
    .fv-picked.show { display:flex; }
    .fv-picked .t { font-weight:800; font-size:.9rem; }
    .fv-picked .s { font-size:.76rem; color:var(--muted); }

    .fv-sr { position:absolute; opacity:0; pointer-events:none; }
    .fv-picks { display:grid; grid-template-columns:1fr 1fr; gap:.7rem; margin:1rem 0 .2rem; }
    .fv-pick { position:relative; display:block; cursor:pointer; margin:0; }
    .fv-pick .box { display:flex; gap:.7rem; align-items:flex-start; border:1.5px solid var(--line); border-radius:16px; padding:.8rem .85rem; height:100%; transition:.15s; background:#fff; }
    .fv-pick .box .ic { width:34px; height:34px; border-radius:11px; display:grid; place-items:center; flex:none; background:var(--soft); color:#64748b; font-size:.9rem; }
    .fv-pick .box b { display:block; font-size:.86rem; }
    .fv-pick .box small { display:block; font-size:.74rem; line-height:1.5; color:var(--muted); margin-top:.1rem; }
    .fv-pick .box .tick { margin-left:auto; width:20px; height:20px; border-radius:50%; border:1.5px solid #cbd5e1; display:grid; place-items:center; color:transparent; font-size:.6rem; flex:none; }
    .fv-pick input:checked + .box { border-color:var(--brand); background:#f8f9ff; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
    .fv-pick input:checked + .box .ic { background:var(--brand); color:#fff; }
    .fv-pick input:checked + .box .tick { background:var(--brand); border-color:var(--brand); color:#fff; }
    .fv-pick input:focus-visible + .box { outline:2px solid #818cf8; outline-offset:2px; }

    .fv-default-box { display:none; background:var(--soft); border-radius:16px; padding:1rem; margin-top:.8rem; }
    .fv-default-box.show { display:block; }
    .fv-warn { display:flex; gap:.6rem; background:#fffbeb; border:1px solid #fde68a; color:#78350f; border-radius:12px; padding:.6rem .75rem; font-size:.78rem; line-height:1.6; margin-bottom:.9rem; }
    .fv-warn i { color:#d97706; margin-top:.2rem; }
    .fv-warn a { color:#92400e; font-weight:800; cursor:pointer; text-decoration:underline; }
    .fv-chips { display:flex; flex-wrap:wrap; gap:.3rem; margin-top:.45rem; }
    .fv-chip { border:1px solid var(--line); background:#fff; border-radius:999px; padding:.18rem .65rem; font-size:.74rem; cursor:pointer; color:#334155; }
    .fv-chip:hover { background:var(--brand-soft); border-color:#c7d2fe; color:var(--brand); }

    /* ---------- Info modals ---------- */
    .fv-steps { display:grid; gap:.65rem; }
    .fv-step { display:flex; gap:.75rem; padding:.7rem .8rem; border:1px solid var(--line); border-radius:14px; }
    .fv-step .ic { width:34px; height:34px; border-radius:11px; display:grid; place-items:center; flex:none; background:var(--brand-soft); color:var(--brand); font-size:.85rem; }
    .fv-step .ic.red { background:var(--red-soft); color:var(--red); }
    .fv-step b { display:block; font-size:.86rem; margin-bottom:.1rem; }
    .fv-step p { margin:0; font-size:.8rem; line-height:1.65; color:#475569; }
    .fv-ethic-list { margin:0; padding:0; list-style:none; display:grid; gap:.6rem; }
    .fv-ethic-list li { display:flex; gap:.65rem; font-size:.84rem; line-height:1.7; color:#3f1d1d; background:#fff7f7; border:1px solid #fee2e2; border-radius:14px; padding:.65rem .8rem; }
    .fv-ethic-list li i { color:#dc2626; margin-top:.35rem; font-size:.8rem; }

    @media (max-width:991px) { .fv-stats { grid-template-columns:repeat(3,1fr); } }
    @media (max-width:767px) {
        .fv-stats { grid-template-columns:1fr; }
        .fv-row { grid-template-columns:1fr auto; }
        .fv-row .fv-dose { grid-column:1 / -1; order:3; }
        .fv-picks { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')
@php
    $favCount = $rows->where('is_favorite', true)->count();
    $defCount = $rows->where('is_default', true)->count();
@endphp

<div class="fv">

    {{-- ================= Header ================= --}}
    <div class="fv-head">
        <div class="fv-title">
            <span class="badge-ic"><i class="fa-solid fa-star"></i></span>
            <div>
                <h1>Favorite Medicines</h1>
                <p>যে ঔষধ ও ব্র্যান্ড বেশি লেখেন, এখানে সাজিয়ে রাখুন। প্রেসক্রিপশন লেখা হবে আরও দ্রুত।</p>
            </div>
        </div>
        <div class="fv-head-actions">
            <button type="button" class="fv-btn" data-bs-toggle="modal" data-bs-target="#fvHelp"><i class="fa-regular fa-circle-question"></i> কীভাবে কাজ করে?</button>
            <button type="button" class="fv-btn primary" id="fvAddBtn"><i class="fa-solid fa-plus"></i> ঔষধ যোগ করুন</button>
        </div>
    </div>

    {{-- ================= Stats ================= --}}
    <div class="fv-stats">
        <div class="fv-stat"><span class="ic a"><i class="fa-solid fa-star"></i></span><div><b>{{ $favCount }}</b><span>Favorite ঔষধ</span></div></div>
        <div class="fv-stat"><span class="ic b"><i class="fa-solid fa-thumbtack"></i></span><div><b>{{ $defCount }}</b><span>Auto-added ঔষধ</span></div></div>
        <div class="fv-stat"><span class="ic c"><i class="fa-solid fa-industry"></i></span><div><b>{{ $companies->count() }}</b><span>Favorite ব্র্যান্ড</span></div></div>
    </div>

    {{-- ================= Ethics strip ================= --}}
    <div class="fv-ethic-bar" role="note">
        <i class="fa-solid fa-scale-balanced lead"></i>
        <span><b>নৈতিক সতর্কতা:</b> ঔষধ বাছাই হবে শুধু রোগীর প্রয়োজনে, কোনো কোম্পানির সুবিধার ভিত্তিতে নয়। Auto-added ডোজ প্রতিবার রোগী দেখে যাচাই করুন।</span>
        <a data-bs-toggle="modal" data-bs-target="#fvEthic">বিস্তারিত</a>
    </div>

    <div class="row g-3">

        {{-- ================= Brands ================= --}}
        <div class="col-lg-4">
            <div class="fv-card">
                <div class="fv-card-h">
                    <h2><i class="fa-solid fa-industry"></i> Favorite ব্র্যান্ড</h2>
                </div>
                <p class="fv-hint">এই কোম্পানিগুলোর ঔষধ সার্চে সবার আগে দেখাবে। অন্য কোম্পানির ঔষধও নিচে থাকবে, কিছুই লুকানো হয় না।</p>

                <form method="POST" action="{{ route('doctor.favorites.companies.store') }}" autocomplete="off" class="mb-3">
                    @csrf
                    <div class="fv-search">
                        <div class="d-flex gap-2">
                            <div class="fv-input-ic flex-grow-1">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="companyInput" name="company" value="{{ old('company') }}" class="fv-input @error('company') is-invalid @enderror" placeholder="কোম্পানির নাম লিখুন" required>
                            </div>
                            <button class="fv-btn primary" type="submit" title="ফেভারিট ব্র্যান্ডে যোগ করুন"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <div class="fv-results" id="companyResults" role="listbox"></div>
                    </div>
                    @error('company') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    <div class="small text-muted mt-2">লিখতে শুরু করলে তালিকা আসবে। তালিকা থেকে বেছে নিন।</div>
                </form>

                @forelse($companies as $c)
                    <div class="fv-brand">
                        <span class="fv-av">{{ mb_strtoupper(mb_substr($c->company, 0, 1)) }}</span>
                        <span class="nm">{{ $c->company }}</span>
                        <form method="POST" action="{{ route('doctor.favorites.companies.destroy', $c->id) }}" class="m-0" onsubmit="return confirm('এই ব্র্যান্ডটি ফেভারিট তালিকা থেকে বাদ দেবেন?\n\nসার্চে এর ঔষধ আর আগে দেখাবে না। ঔষধ বা কোম্পানি ডাটাবেস থেকে মুছবে না।')">
                            @csrf @method('DELETE')
                            <button type="submit" class="fv-x" aria-label="{{ $c->company }} বাদ দিন" title="ফেভারিট ব্র্যান্ড থেকে বাদ দিন"><i class="fa-solid fa-xmark"></i></button>
                        </form>
                    </div>
                @empty
                    <div class="fv-empty" style="padding:1.4rem .5rem">
                        <div class="big" style="width:48px;height:48px;font-size:1.2rem"><i class="fa-solid fa-industry"></i></div>
                        <b>কোনো ব্র্যান্ড নেই</b>
                        উপরে নাম লিখে আপনার পছন্দের ব্র্যান্ড যোগ করুন।
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ================= Medicines ================= --}}
        <div class="col-lg-8">
            <div class="fv-card">
                <div class="fv-card-h">
                    <h2><i class="fa-solid fa-capsules"></i> আমার ঔষধ</h2>
                </div>

                @if($rows->isEmpty())
                    <div class="fv-empty">
                        <div class="big"><i class="fa-regular fa-star"></i></div>
                        <b>এখনো কোনো ঔষধ নেই</b>
                        যে ঔষধ বারবার লেখেন সেটি যোগ করুন। চাইলে ডোজসহ প্রতিটি নতুন প্রেসক্রিপশনে নিজে থেকে বসিয়ে দিতে পারবেন।
                        <div class="mt-3"><button type="button" class="fv-btn primary" id="fvAddBtn2"><i class="fa-solid fa-plus"></i> প্রথম ঔষধ যোগ করুন</button></div>
                    </div>
                @else
                    <div class="fv-tools">
                        <div class="fv-input-ic">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="fvFilter" class="fv-input" placeholder="আমার তালিকায় খুঁজুন..." autocomplete="off">
                        </div>
                        <div class="fv-pills" role="tablist">
                            <button type="button" class="fv-pill active" data-filter="all">সব ({{ $rows->count() }})</button>
                            <button type="button" class="fv-pill" data-filter="fav">Favorite ({{ $favCount }})</button>
                            <button type="button" class="fv-pill" data-filter="def">Auto-added ({{ $defCount }})</button>
                        </div>
                    </div>

                    <div id="fvList">
                    @foreach($rows as $row)
                        @php $m = $row->medicine; @endphp
                        <div class="fv-row"
                             data-fav="{{ $row->is_favorite ? 1 : 0 }}"
                             data-def="{{ $row->is_default ? 1 : 0 }}"
                             data-text="{{ mb_strtolower($m->brand_name.' '.$m->generic_name.' '.$m->company) }}">
                            <div class="fv-med">
                                <span class="fv-av av">{{ mb_strtoupper(mb_substr($m->brand_name, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <div class="nm">{{ $m->brand_name }}@if($m->strength) <span class="text-muted fw-semibold">{{ $m->strength }}</span>@endif</div>
                                    <div class="sub">{{ $m->generic_name }}@if($m->company) · {{ $m->company }}@endif</div>
                                    <div class="fv-tags">
                                        @if($row->is_favorite)<span class="fv-tag fav"><i class="fa-solid fa-star"></i> Favorite</span>@endif
                                        @if($row->is_default)<span class="fv-tag def"><i class="fa-solid fa-thumbtack"></i> Auto-added</span>@endif
                                    </div>
                                </div>
                            </div>

                            <div class="fv-dose">
                                @if($row->is_default)
                                    <span class="pill">{{ $row->default_dose_text ?: $row->default_dose }}</span>
                                    <div class="mut">{{ $row->default_duration ?: '—' }}@if($row->default_instruction) · {{ $row->default_instruction }}@endif</div>
                                @else
                                    <span class="mut">নিজে থেকে যোগ হয় না</span>
                                @endif
                            </div>

                            <div class="fv-acts">
                                <button type="button" class="fv-ic-btn fv-edit" title="সম্পাদনা (শুধু নতুন প্রেসক্রিপশনে প্রযোজ্য)" aria-label="সম্পাদনা"
                                        data-id="{{ $row->id }}"
                                        data-label="{{ $m->brand_name }}{{ $m->strength ? ' ('.$m->strength.')' : '' }}"
                                        data-sub="{{ $m->generic_name }}{{ $m->company ? ' · '.$m->company : '' }}"
                                        data-form="{{ $m->dosage_form }}"
                                        data-fav="{{ $row->is_favorite ? 1 : 0 }}"
                                        data-default="{{ $row->is_default ? 1 : 0 }}"
                                        data-dose="{{ $row->default_dose }}"
                                        data-dose-text="{{ $row->default_dose_text }}"
                                        data-duration="{{ $row->default_duration }}"
                                        data-instruction="{{ $row->default_instruction }}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form method="POST" action="{{ route('doctor.favorites.medicines.destroy', $row->id) }}" class="m-0" onsubmit="return confirm('এই ঔষধটি ফেভারিট ও Auto-added তালিকা থেকে বাদ দেবেন?\n\nসেভ করা ডোজ, মেয়াদ ও নির্দেশনা মুছে যাবে। ঔষধটি ডাটাবেস থেকে মুছবে না, পরে আবার যোগ করতে পারবেন।')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="fv-ic-btn danger" title="তালিকা থেকে বাদ দিন" aria-label="বাদ দিন"><i class="fa-solid fa-trash-can"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    </div>
                    <div class="fv-empty" id="fvNoMatch" style="display:none"><b>কিছু পাওয়া যায়নি</b>অন্য নামে খুঁজুন বা ফিল্টার বদলান।</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ================= Add / Edit modal ================= --}}
<div class="modal fade fv-modal fv" id="fvModal" tabindex="-1" aria-labelledby="fvModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:640px">
        <form class="modal-content" method="POST" id="fvForm" action="{{ route('doctor.favorites.medicines.store') }}">
            @csrf
            <input type="hidden" name="_method" id="fvMethod" value="PUT" disabled>
            <input type="hidden" name="_fv" id="fvMode" value="{{ old('_fv', 'add') }}">
            <input type="hidden" name="_fv_id" id="fvEditId" value="{{ old('_fv_id') }}">
            <input type="hidden" name="_fv_label" id="fvLabelField" value="{{ old('_fv_label') }}">
            <input type="hidden" name="_fv_sub" id="fvSubField" value="{{ old('_fv_sub') }}">
            <input type="hidden" name="_fv_form" id="fvFormField" value="{{ old('_fv_form') }}">
            <input type="hidden" name="medicine_id" id="fvMedicineId" value="{{ old('medicine_id') }}">

            <div class="modal-header">
                <h5 class="modal-title" id="fvModalTitle">ঔষধ যোগ করুন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ করুন"></button>
            </div>

            <div class="modal-body">
                @if($errors->any() && old('_fv'))
                    <div class="alert alert-danger py-2 small">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif

                {{-- 1. Medicine --}}
                <div id="fvPickWrap">
                    <label class="fv-lbl" for="fvSearch">১. ঔষধ বাছুন <small>(ব্র্যান্ড বা জেনেরিক নাম)</small></label>
                    <div class="fv-search">
                        <div class="fv-input-ic">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="fvSearch" class="fv-input" placeholder="যেমন: Napa, Seclo" autocomplete="off">
                        </div>
                        <div class="fv-results" id="fvResults" role="listbox"></div>
                    </div>
                    <div class="small text-muted mt-2">নিচে আসা তালিকা থেকে ক্লিক করে বাছুন, নাহলে Save হবে না।</div>
                </div>
                <div class="fv-picked" id="fvPicked"></div>

                {{-- 2. What to do --}}
                <label class="fv-lbl mt-3 mb-0">২. কী করতে চান?</label>
                <div class="fv-picks">
                    <label class="fv-pick">
                        <input type="hidden" name="is_favorite" value="0">
                        <input class="fv-sr" type="checkbox" id="fvFav" name="is_favorite" value="1" @checked(old('_fv') ? old('is_favorite') : true)>
                        <span class="box">
                            <span class="ic"><i class="fa-solid fa-star"></i></span>
                            <span><b>Favorite</b><small>সার্চে সবার আগে দেখাবে। প্রেসক্রিপশনে নিজে থেকে বসবে না।</small></span>
                            <span class="tick"><i class="fa-solid fa-check"></i></span>
                        </span>
                    </label>
                    <label class="fv-pick">
                        <input type="hidden" name="is_default" value="0">
                        <input class="fv-sr" type="checkbox" id="fvDefault" name="is_default" value="1" @checked(old('is_default'))>
                        <span class="box">
                            <span class="ic"><i class="fa-solid fa-thumbtack"></i></span>
                            <span><b>Auto-add</b><small>প্রতিটি নতুন প্রেসক্রিপশনে ডোজসহ নিজে থেকে বসবে।</small></span>
                            <span class="tick"><i class="fa-solid fa-check"></i></span>
                        </span>
                    </label>
                </div>

                {{-- 3. Default dose --}}
                <div class="fv-default-box" id="fvDefaultBox">
                    <div class="fv-warn" role="alert">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>এই ডোজ সব রোগীর জন্য ঠিক নাও হতে পারে। বয়স, ওজন, গর্ভাবস্থা, কিডনি বা লিভারের অবস্থা ও অ্যালার্জি দেখে প্রতিটি প্রেসক্রিপশনে যাচাই করুন। অ্যান্টিবায়োটিক, স্টেরয়েড, নিয়ন্ত্রিত ও শিশুদের ঔষধ Auto-add না করাই নিরাপদ। <a data-bs-toggle="modal" data-bs-target="#fvEthic">বিস্তারিত</a></span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6" id="fvDoseWrap">
                            <label class="fv-lbl" for="fvDose">ডোজ <small>(সকাল+দুপুর+রাত)</small></label>
                            <input type="text" class="fv-input" id="fvDose" name="default_dose" value="{{ old('default_dose') }}" placeholder="1+0+1" maxlength="40">
                            <div class="fv-chips" data-chips="fvDose">
                                @foreach(['1+0+0','0+1+0','0+0+1','1+0+1','1+1+0','0+1+1','1+1+1','2+0+2','0.5+0+0.5'] as $d)
                                    <button type="button" class="fv-chip" data-value="{{ $d }}">{{ $d }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6" id="fvDoseTextWrap">
                            <label class="fv-lbl" for="fvDoseText">ডোজ লিখে <small>(সিরাপ, ড্রপ)</small></label>
                            <input type="text" class="fv-input" id="fvDoseText" name="default_dose_text" value="{{ old('default_dose_text') }}" placeholder="২ চামচ" maxlength="120">
                        </div>
                        <div class="col-md-6">
                            <label class="fv-lbl" for="fvDuration">মেয়াদ <small>(কত দিন)</small></label>
                            <input type="text" class="fv-input" id="fvDuration" name="default_duration" value="{{ old('default_duration') }}" placeholder="৭ দিন" maxlength="120">
                            <div class="fv-chips" data-chips="fvDuration">
                                @foreach(['৩ দিন','৫ দিন','৭ দিন','১০ দিন','১৪ দিন','১ মাস','চলবে','প্রয়োজনে'] as $d)
                                    <button type="button" class="fv-chip" data-value="{{ $d }}">{{ $d }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="fv-lbl" for="fvInstruction">নির্দেশনা <small>(খাওয়ার নিয়ম)</small></label>
                            <input type="text" class="fv-input" id="fvInstruction" name="default_instruction" value="{{ old('default_instruction') }}" placeholder="খাবার পরে" maxlength="255">
                            <div class="fv-chips" data-chips="fvInstruction">
                                @foreach(['খাবার পরে','খাবার আগে','সকালে খালি পেটে','রাতে ঘুমানোর আগে','প্রয়োজনে'] as $d)
                                    <button type="button" class="fv-chip" data-value="{{ $d }}">{{ $d }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="fv-btn" data-bs-dismiss="modal">বাতিল</button>
                <button type="submit" class="fv-btn primary"><i class="fa-solid fa-floppy-disk"></i> সেভ করুন</button>
            </div>
        </form>
    </div>
</div>

{{-- ================= Help modal ================= --}}
<div class="modal fade fv-modal fv" id="fvHelp" tabindex="-1" aria-labelledby="fvHelpTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:600px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fvHelpTitle">কোন বাটনে ক্লিক করলে কী হবে?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ করুন"></button>
            </div>
            <div class="modal-body">
                <div class="fv-steps">
                    <div class="fv-step"><span class="ic"><i class="fa-solid fa-plus"></i></span><div><b>ঔষধ যোগ করুন</b><p>একটি ফর্ম খুলবে। ঔষধ বেছে ঠিক করুন সেটি Favorite হবে, নাকি Auto-add। Save চাপার আগে কিছুই সেভ হয় না।</p></div></div>
                    <div class="fv-step"><span class="ic"><i class="fa-solid fa-star"></i></span><div><b>Favorite</b><p>প্রেসক্রিপশনে ঔষধ সার্চ করলে এটি তালিকার একদম উপরে আসবে। তবে নিজে থেকে প্রেসক্রিপশনে বসবে না।</p></div></div>
                    <div class="fv-step"><span class="ic"><i class="fa-solid fa-thumbtack"></i></span><div><b>Auto-add</b><p>প্রতিটি নতুন প্রেসক্রিপশন খুললেই ঔষধটি সেভ করা ডোজসহ "ডিফল্ট" ট্যাগে বসে থাকবে। কোনো রোগীর দরকার না হলে ওই প্রেসক্রিপশন থেকে মুছে দিতে পারবেন।</p></div></div>
                    <div class="fv-step"><span class="ic"><i class="fa-solid fa-pen"></i></span><div><b>সম্পাদনা (কলম আইকন)</b><p>Favorite, Auto-add বা ডোজ, মেয়াদ, নির্দেশনা বদলানোর ফর্ম খুলবে। Save করলে <b>শুধু ভবিষ্যতের</b> প্রেসক্রিপশনে নতুন মান বসবে। আগের প্রেসক্রিপশন বদলাবে না।</p></div></div>
                    <div class="fv-step"><span class="ic red"><i class="fa-solid fa-trash-can"></i></span><div><b>বাদ দিন (ট্র্যাশ আইকন)</b><p>ঔষধটি Favorite ও Auto-add দুটো থেকেই বাদ যাবে এবং সেভ করা ডোজ মুছবে। ঔষধ ডাটাবেস থেকে মুছে যায় না, পরে আবার যোগ করা যাবে।</p></div></div>
                    <div class="fv-step"><span class="ic"><i class="fa-solid fa-industry"></i></span><div><b>Favorite ব্র্যান্ড যোগ (+)</b><p>কোম্পানি বেছে + চাপলে সার্চে ওই কোম্পানির ঔষধ আগে দেখাবে, সঙ্গে "আপনার ব্র্যান্ড" ব্যাজ থাকবে। একাধিক ব্র্যান্ড যোগ করা যায়।</p></div></div>
                    <div class="fv-step"><span class="ic red"><i class="fa-solid fa-xmark"></i></span><div><b>ব্র্যান্ডের পাশে ✕</b><p>নিশ্চিত করলে ব্র্যান্ডটি ফেভারিট তালিকা থেকে বাদ যাবে। কোম্পানি বা ঔষধ মুছে যাবে না।</p></div></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="fv-btn primary" data-bs-dismiss="modal">বুঝেছি</button></div>
        </div>
    </div>
</div>

{{-- ================= Ethics modal ================= --}}
<div class="modal fade fv-modal fv" id="fvEthic" tabindex="-1" aria-labelledby="fvEthicTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:600px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fvEthicTitle"><i class="fa-solid fa-scale-balanced text-danger me-2"></i>নৈতিক সতর্কতা ও চিকিৎসকের দায়িত্ব</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ করুন"></button>
            </div>
            <div class="modal-body">
                <ul class="fv-ethic-list">
                    <li><i class="fa-solid fa-circle"></i><span><b>ঔষধ বাছাইয়ের ভিত্তি শুধু রোগীর প্রয়োজন।</b> রোগের ধরন, কার্যকারিতা, নিরাপত্তা ও রোগীর আর্থিক সামর্থ্য দেখে ঔষধ লিখুন। কোনো কোম্পানির উপহার, কমিশন, সফর বা অন্য সুবিধা এর ভিত্তি হওয়া উচিত নয়।</span></li>
                    <li><i class="fa-solid fa-circle"></i><span><b>Favorite ব্র্যান্ড শুধু সার্চে আগে দেখানোর সুবিধা।</b> এটি কোনো ঔষধ বা কোম্পানির সুপারিশ নয়। প্রতিটি রোগীর জন্য সেরা ঔষধ প্রতিবার নিজে যাচাই করুন, আর সম্ভব হলে সমমানের কম দামের বিকল্পের কথা রোগীকে জানান।</span></li>
                    <li><i class="fa-solid fa-circle"></i><span><b>Auto-add ডোজ সবার জন্য এক নয়।</b> বয়স, ওজন, গর্ভাবস্থা, স্তন্যদান, কিডনি বা লিভারের অবস্থা, অ্যালার্জি ও চলমান অন্য ঔষধ দেখে প্রতিটি প্রেসক্রিপশনে ঔষধ ও ডোজ যাচাই করুন। দরকার না হলে মুছে দিন বা বদলে নিন।</span></li>
                    <li><i class="fa-solid fa-circle"></i><span><b>ঝুঁকিপূর্ণ ঔষধ Auto-add করা এড়িয়ে চলুন।</b> অ্যান্টিবায়োটিক, স্টেরয়েড, ঘুম বা মানসিক রোগের ঔষধ, নিয়ন্ত্রিত ঔষধ এবং শিশুদের ঔষধ প্রতিবার রোগী দেখে সিদ্ধান্ত নিয়ে লিখুন। ভুল বা অপ্রয়োজনীয় ঔষধ রোগীর ক্ষতি করতে পারে।</span></li>
                    <li><i class="fa-solid fa-circle"></i><span><b>পেশাগত নীতিমালা মেনে চলুন।</b> বাংলাদেশ মেডিকেল অ্যান্ড ডেন্টাল কাউন্সিল (BMDC)-এর নীতিমালা ও দেশের প্রচলিত আইন মেনে প্রেসক্রিপশন লেখা আপনার দায়িত্ব। এই সফটওয়্যারের সুবিধা সেই দায়িত্ব কমায় না।</span></li>
                </ul>
                <p class="mt-3 mb-0 fw-bold small text-danger"><i class="fa-solid fa-user-doctor me-1"></i> প্রেসক্রিপশনে লেখা প্রতিটি ঔষধের চূড়ান্ত সিদ্ধান্ত ও দায় চিকিৎসকের।</p>
            </div>
            <div class="modal-footer"><button type="button" class="fv-btn primary" data-bs-dismiss="modal">বুঝেছি</button></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var MED_URL = @json(route('doctor.favorites.medicine-search'));
    var COMPANY_URL = @json(route('doctor.favorites.company-search'));
    var STORE_URL = @json(route('doctor.favorites.medicines.store'));
    var UPDATE_URL = @json(route('doctor.favorites.medicines.update', ['doctorMedicine' => '__ID__']));
    var REOPEN = @json((bool) ($errors->any() && old('_fv')));

    function $(id) { return document.getElementById(id); }
    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : String(s); return d.innerHTML; }
    function debounce(fn, ms) { var t; return function () { var a = arguments, c = this; clearTimeout(t); t = setTimeout(function () { fn.apply(c, a); }, ms); }; }

    /* ---------- Brand (company) autocomplete ---------- */
    var cInput = $('companyInput'), cBox = $('companyResults');
    if (cInput) {
        cInput.addEventListener('input', debounce(function () {
            var term = cInput.value.trim();
            if (!term) { cBox.classList.remove('show'); return; }
            fetch(COMPANY_URL + '?term=' + encodeURIComponent(term), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (list) {
                    if (!list.length) { cBox.innerHTML = '<div class="fv-none">কোনো কোম্পানি পাওয়া যায়নি।</div>'; cBox.classList.add('show'); return; }
                    cBox.innerHTML = list.map(function (c) {
                        return '<button type="button" class="fv-opt" data-v="' + esc(c) + '"><span class="t">' + esc(c) + '</span></button>';
                    }).join('');
                    cBox.classList.add('show');
                }).catch(function () { cBox.classList.remove('show'); });
        }, 200));
        cBox.addEventListener('click', function (e) {
            var b = e.target.closest('.fv-opt'); if (!b) return;
            cInput.value = b.getAttribute('data-v'); cBox.classList.remove('show');
        });
    }

    /* ---------- Filter my list ---------- */
    var filterInput = $('fvFilter'), list = $('fvList'), noMatch = $('fvNoMatch');
    var activeFilter = 'all';
    function applyFilter() {
        if (!list) return;
        var q = (filterInput.value || '').trim().toLowerCase(), shown = 0;
        list.querySelectorAll('.fv-row').forEach(function (row) {
            var ok = (activeFilter === 'all' || (activeFilter === 'fav' && row.dataset.fav === '1') || (activeFilter === 'def' && row.dataset.def === '1'))
                  && (!q || row.dataset.text.indexOf(q) !== -1);
            row.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        noMatch.style.display = shown ? 'none' : '';
    }
    if (filterInput) {
        filterInput.addEventListener('input', applyFilter);
        document.querySelectorAll('.fv-pill').forEach(function (p) {
            p.addEventListener('click', function () {
                document.querySelectorAll('.fv-pill').forEach(function (x) { x.classList.remove('active'); });
                p.classList.add('active'); activeFilter = p.dataset.filter; applyFilter();
            });
        });
    }

    /* ---------- Add / Edit modal ---------- */
    var modalEl = $('fvModal'), modal = new bootstrap.Modal(modalEl);
    var form = $('fvForm'), methodInput = $('fvMethod');
    var medId = $('fvMedicineId'), search = $('fvSearch'), results = $('fvResults'), picked = $('fvPicked'), pickWrap = $('fvPickWrap');
    var defSwitch = $('fvDefault'), defBox = $('fvDefaultBox');
    var doseWrap = $('fvDoseWrap'), doseTextWrap = $('fvDoseTextWrap');
    var current = { form: '' };

    function syncDefault() { defBox.classList.toggle('show', defSwitch.checked); }
    function syncDoseFields() {
        var syrupLike = current.form === 'Syrup';
        doseWrap.style.display = syrupLike ? 'none' : '';
        doseTextWrap.style.display = syrupLike ? '' : 'none';
    }
    defSwitch.addEventListener('change', syncDefault);

    function showPicked(label, sub) {
        picked.innerHTML = '<span class="fv-av" style="background:#fff;color:#4f46e5">' + esc((label || '?').charAt(0).toUpperCase()) + '</span>' +
            '<div><div class="t">' + esc(label) + '</div>' + (sub ? '<div class="s">' + esc(sub) + '</div>' : '') + '</div>';
        picked.classList.add('show');
        $('fvLabelField').value = label; $('fvSubField').value = sub || ''; $('fvFormField').value = current.form || '';
    }

    function openAdd() {
        form.reset();
        form.action = STORE_URL; methodInput.disabled = true;
        $('fvMode').value = 'add'; $('fvEditId').value = '';
        $('fvModalTitle').textContent = 'ঔষধ যোগ করুন';
        $('fvFav').checked = true; defSwitch.checked = false;
        ['fvDose', 'fvDoseText', 'fvDuration', 'fvInstruction'].forEach(function (id) { $(id).value = ''; });
        medId.value = ''; search.value = ''; current.form = '';
        picked.classList.remove('show'); picked.innerHTML = '';
        pickWrap.style.display = '';
        syncDefault(); syncDoseFields(); modal.show();
    }

    function openEdit(btn) {
        var d = btn.dataset;
        form.action = UPDATE_URL.replace('__ID__', d.id); methodInput.disabled = false;
        $('fvMode').value = 'edit'; $('fvEditId').value = d.id;
        $('fvModalTitle').textContent = 'ঔষধ সম্পাদনা';
        current.form = d.form || '';
        medId.value = ''; pickWrap.style.display = 'none';
        showPicked(d.label, d.sub);
        $('fvFav').checked = d.fav === '1'; defSwitch.checked = d.default === '1';
        $('fvDose').value = d.dose || ''; $('fvDoseText').value = d.doseText || '';
        $('fvDuration').value = d.duration || ''; $('fvInstruction').value = d.instruction || '';
        syncDefault(); syncDoseFields(); modal.show();
    }

    $('fvAddBtn').addEventListener('click', openAdd);
    if ($('fvAddBtn2')) $('fvAddBtn2').addEventListener('click', openAdd);
    document.querySelectorAll('.fv-edit').forEach(function (b) { b.addEventListener('click', function () { openEdit(b); }); });

    /* ---------- Medicine search inside modal ---------- */
    search.addEventListener('input', debounce(function () {
        var term = search.value.trim();
        medId.value = '';
        if (term.length < 2) { results.classList.remove('show'); return; }
        fetch(MED_URL + '?term=' + encodeURIComponent(term), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (items) {
                if (!items.length) { results.innerHTML = '<div class="fv-none">কোনো ঔষধ পাওয়া যায়নি।</div>'; results.classList.add('show'); return; }
                results.innerHTML = items.map(function (m, i) {
                    return '<button type="button" class="fv-opt" data-i="' + i + '"><span><span class="t">' + esc(m.label) + '</span><br><span class="s">' + esc(m.generic_name || '') + (m.company ? ' · ' + esc(m.company) : '') + '</span></span>' +
                        (m.is_favorite_company ? '<span class="fv-tag brand">আপনার ব্র্যান্ড</span>' : '') + '</button>';
                }).join('');
                results.classList.add('show');
                results._list = items;
            }).catch(function () { results.classList.remove('show'); });
    }, 220));

    results.addEventListener('click', function (e) {
        var b = e.target.closest('.fv-opt'); if (!b || !results._list) return;
        var m = results._list[parseInt(b.getAttribute('data-i'), 10)];
        medId.value = m.id; current.form = m.dosage_form || '';
        search.value = m.label; results.classList.remove('show');
        showPicked(m.label, (m.generic_name || '') + (m.company ? ' · ' + m.company : ''));
        syncDoseFields();
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.fv-search')) { results.classList.remove('show'); if (cBox) cBox.classList.remove('show'); }
    });

    /* ---------- Quick chips ---------- */
    modalEl.addEventListener('click', function (e) {
        var chip = e.target.closest('.fv-chip'); if (!chip) return;
        var wrap = chip.closest('[data-chips]');
        $(wrap.getAttribute('data-chips')).value = chip.getAttribute('data-value');
    });

    /* ---------- Must pick a medicine when adding ---------- */
    form.addEventListener('submit', function (e) {
        if ($('fvMode').value === 'add' && !medId.value) {
            e.preventDefault();
            search.classList.add('is-invalid');
            search.focus();
        }
    });
    search.addEventListener('input', function () { search.classList.remove('is-invalid'); });

    /* ---------- Re-open after a validation error ---------- */
    if (REOPEN) {
        var mode = $('fvMode').value;
        current.form = $('fvFormField').value;
        if (mode === 'edit' && $('fvEditId').value) {
            form.action = UPDATE_URL.replace('__ID__', $('fvEditId').value); methodInput.disabled = false;
            pickWrap.style.display = 'none';
            $('fvModalTitle').textContent = 'ঔষধ সম্পাদনা';
        } else if (medId.value) {
            search.value = $('fvLabelField').value;
        }
        if ($('fvLabelField').value) showPicked($('fvLabelField').value, $('fvSubField').value);
        syncDefault(); syncDoseFields(); modal.show();
    }
})();
</script>
@endpush