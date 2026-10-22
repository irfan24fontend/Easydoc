@php
    /* ---------- Paper size ---------- */
    $paperKey = strtoupper(trim($paperSize ?? 'A4'));
    $dimensions = [               // [width mm, height mm]
        'A4'     => [210, 297],
        'A5'     => [148, 210],
        'LETTER' => [216, 279],
        'LEGAL'  => [216, 356],
    ];
    if (!isset($dimensions[$paperKey])) {
        $paperKey = 'A4';
    }
    [$wMm, $hMm] = $dimensions[$paperKey];
    $wPx = (int) round($wMm * 96 / 25.4);
    $hPx = (int) round($hMm * 96 / 25.4);

    /* ---------- Mode ---------- */
    $isPublic        = (bool) ($isPublic ?? false);     // true = patient er public link
    $isInvestigation = ($prescription->type ?? null) === 'investigation_only';
    $patientName     = $prescription->patient->name ?? 'Patient';

    /* Template CSS theke phone-width media query bad (paper fixed size, auto-zoom hoy) */
    $cleanCss = preg_replace('/@media[^{]*max-width[^{]*\{(?:[^{}]*\{[^{}]*\})*\s*\}/i', '', (string) ($css ?? ''));

    /* ---------- JS config ---------- */
    $cfg = [
        'isPublic' => $isPublic,
        'wMm' => $wMm, 'hMm' => $hMm, 'wPx' => $wPx, 'hPx' => $hPx,
        'file' => 'prescription-' . $prescription->id,
        'proxy' => route('prescription.img'),
        'shareCreate' => $isPublic ? null : route('prescription.share.create', $prescription->id),
        'shareRevoke' => $isPublic ? null : route('prescription.share.revoke', $prescription->id),
        'editUrl'   => $isPublic ? null : route('doctor.prescriptions.edit', $prescription->id),
        'createUrl' => $isPublic ? null : route('doctor.prescriptions.create'),
        'indexUrl'  => $isPublic ? null : route('doctor.prescriptions.index'),
    ];
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if($isPublic)
        <meta name="robots" content="noindex, nofollow, noarchive">
    @endif
    <title>Prescription #{{ $prescription->id }} - {{ $patientName }}</title>

    {{-- Bangla font: PC ar phone e ekoi dekhabe (JPG/PDF o thik asbe) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bar-bg: #0f172a;
            --bar-fg: #e2e8f0;
            --bar-muted: #94a3b8;
            --accent: #4f46e5;
            --accent-hover: #4338ca;
            --stage-bg: #e5e7eb;
            --success-bg: #dcfce7;
            --success-fg: #166534;
            --error-bg: #fee2e2;
            --error-fg: #991b1b;
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }

        body {
            background: var(--stage-bg);
            font-family: "Noto Sans Bengali", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
        }

        /* ---------- Toolbar (screen only) ---------- */
        .rx-toolbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--bar-bg);
            color: var(--bar-fg);
            box-shadow: 0 2px 10px rgba(0, 0, 0, .25);
            padding-top: env(safe-area-inset-top, 0px);
        }
        .rx-toolbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }
        .rx-meta { flex: 1 1 220px; min-width: 0; }
        .rx-meta-title { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .rx-meta-sub { font-size: 12px; color: var(--bar-muted); margin-top: 2px; }
        .rx-badge {
            display: inline-block; font-size: 10.5px; font-weight: 600; letter-spacing: .03em; text-transform: uppercase;
            padding: 2px 7px; border-radius: 999px; background: #f59e0b; color: #1f2937; margin-left: 6px; vertical-align: middle;
        }
        .rx-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .rx-actions form { margin: 0; }

        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; font-size: 13px; font-weight: 500; line-height: 1;
            color: var(--bar-fg); background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14); border-radius: 8px;
            text-decoration: none; cursor: pointer; transition: background .15s, border-color .15s;
            font-family: inherit; min-height: 36px;
        }
        .btn:hover { background: rgba(255, 255, 255, .16); }
        .btn[disabled] { opacity: .6; cursor: wait; }
        .btn svg { width: 16px; height: 16px; flex: none; }
        .btn-primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-warn { border-color: rgba(245, 158, 11, .5); color: #fcd34d; }
        .btn-share { background: #0d9488; border-color: #0d9488; color: #fff; }
        .btn-share:hover { background: #0f766e; }
        .btn-jpg { background: #2563eb; border-color: #2563eb; color: #fff; }
        .btn-jpg:hover { background: #1d4ed8; }
        .btn-pdf { background: #dc2626; border-color: #dc2626; color: #fff; }
        .btn-pdf:hover { background: #b91c1c; }
        .btn kbd {
            font-family: inherit; font-size: 10px; padding: 1px 5px; border-radius: 4px;
            background: rgba(255, 255, 255, .15); color: inherit; opacity: .85;
        }

        .rx-zoom { display: inline-flex; align-items: center; border: 1px solid rgba(255, 255, 255, .14); border-radius: 8px; overflow: hidden; }
        .rx-zoom button { background: transparent; color: var(--bar-fg); border: 0; padding: 8px 11px; font-size: 13px; cursor: pointer; font-family: inherit; }
        .rx-zoom button:hover { background: rgba(255, 255, 255, .12); }
        .rx-zoom span { font-size: 12px; min-width: 46px; text-align: center; color: var(--bar-muted); }
        .divider { width: 1px; height: 24px; background: rgba(255, 255, 255, .14); }

        /* ---------- Flash ---------- */
        .rx-flash { max-width: 1200px; margin: 12px auto 0; padding: 0 16px; }
        .rx-flash div { padding: 10px 14px; border-radius: 8px; font-size: 13.5px; }
        .rx-flash .ok { background: var(--success-bg); color: var(--success-fg); }
        .rx-flash .err { background: var(--error-bg); color: var(--error-fg); }

        /* ---------- Paper ---------- */
        .rx-stage { padding: 24px 12px 60px; display: flex; justify-content: center; overflow-x: auto; zoom: 1; }
        .rx-sheet {
            width: {{ $wMm }}mm;
            min-height: {{ $hMm }}mm;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 23, 42, .18);
            flex: none;
            position: relative;
        }

        /* ---------- Toast ---------- */
        .rx-toast {
            position: fixed; left: 50%; bottom: calc(18px + env(safe-area-inset-bottom, 0px));
            transform: translateX(-50%) translateY(20px); opacity: 0; pointer-events: none;
            background: #0f172a; color: #fff; font-size: 13px; padding: 10px 16px; border-radius: 999px;
            box-shadow: 0 6px 24px rgba(0,0,0,.3); transition: .25s; z-index: 200; max-width: 92vw; text-align: center;
        }
        .rx-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .rx-toast.err { background: #b91c1c; }

        /* ---------- Share modal ---------- */
        .rx-modal { position: fixed; inset: 0; z-index: 150; background: rgba(15, 23, 42, .55); display: none; align-items: center; justify-content: center; padding: 16px; }
        .rx-modal.open { display: flex; }
        .rx-card { background: #fff; color: #0f172a; border-radius: 14px; width: 100%; max-width: 460px; padding: 18px; box-shadow: 0 20px 60px rgba(0,0,0,.35); }
        .rx-card h3 { margin: 0 0 4px; font-size: 17px; }
        .rx-card p { margin: 0 0 12px; font-size: 13px; color: #64748b; line-height: 1.5; }
        .rx-linkrow { display: flex; gap: 8px; margin-bottom: 12px; }
        .rx-linkrow input { flex: 1; min-width: 0; padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 16px; font-family: inherit; }
        .rx-card .b { padding: 11px 14px; border: 0; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; color: #fff; background: #0f766e; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; font-family: inherit; }
        .rx-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 12px; }
        .rx-grid .b { width: 100%; }
        .b-wa { background: #16a34a !important; } .b-sms { background: #7c3aed !important; } .b-nat { background: #0891b2 !important; }
        .rx-foot { display: flex; justify-content: space-between; gap: 8px; flex-wrap: wrap; }
        .b-danger { background: #fff !important; color: #b91c1c !important; border: 1px solid #fecaca !important; }
        .b-close { background: #e2e8f0 !important; color: #0f172a !important; }

        /* ---------- Print ---------- */
        @page { size: {{ $paperKey }}; margin: 0; }

        @media print {
            html, body { background: #fff !important; min-height: 0; }
            .rx-toolbar, .rx-flash, .rx-toast, .rx-modal { display: none !important; }
            .rx-stage { padding: 0 !important; display: block; overflow: visible; zoom: 1 !important; }
            .rx-sheet { width: 100% !important; min-height: 0 !important; box-shadow: none !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }

        @media (max-width: 640px) {
            .rx-toolbar-inner { padding: 8px 10px; gap: 8px; }
            .btn { padding: 8px 10px; }
            .btn .lbl { display: none; }
            .btn.keep .lbl { display: inline; }
            .btn kbd { display: none; }
            .divider { display: none; }
            .rx-stage { padding: 12px 6px 80px; }
        }
    </style>

    {{-- Template CSS (doctor/chamber specific) --}}
    <style id="tpl-css">
        {!! $cleanCss !!}
    </style>
</head>
<body>

    {{-- ===== Toolbar ===== --}}
    <header class="rx-toolbar">
        <div class="rx-toolbar-inner">

            @unless($isPublic)
                <a href="{{ route('doctor.prescriptions.index') }}" class="btn" title="Back to list (Esc)">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.7 5.3a1 1 0 010 1.4L9.4 10l3.3 3.3a1 1 0 11-1.4 1.4l-4-4a1 1 0 010-1.4l4-4a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                    <span class="lbl">Back</span>
                </a>
            @endunless

            <div class="rx-meta">
                <div class="rx-meta-title">
                    {{ $isPublic ? 'Prescription' : $patientName }}
                    @if($isInvestigation)
                        <span class="rx-badge">Investigation Advice</span>
                    @endif
                </div>
                <div class="rx-meta-sub">
                    @if($isPublic)
                        {{ $patientName }} &middot; {{ $prescription->created_at->format('d M Y') }}
                    @else
                        Rx #{{ $prescription->id }}
                        &middot; {{ $prescription->created_at->format('d M Y, h:i A') }}
                        &middot; {{ $paperKey }}
                    @endif
                </div>
            </div>

            <div class="rx-actions">

                <div class="rx-zoom" role="group" aria-label="Zoom">
                    <button type="button" id="zoomOut" title="Zoom out">&minus;</button>
                    <span id="zoomLabel">100%</span>
                    <button type="button" id="zoomIn" title="Zoom in">+</button>
                    <button type="button" id="zoomFit" title="Fit to screen">Fit</button>
                </div>

                <span class="divider"></span>

                @unless($isPublic)
                    <button type="button" class="btn btn-share keep" id="btnShare" title="Share link">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M15 8a3 3 0 10-2.98-3.35L7.9 6.7a3 3 0 100 4.6l4.12 2.05a3 3 0 10.7-1.4L8.6 9.9a3 3 0 000-.8l4.12-2.05A3 3 0 0015 8z"/></svg>
                        <span class="lbl">Share</span>
                    </button>
                @endunless

                <button type="button" class="btn btn-jpg keep" data-dl="jpg" title="Download JPG">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v7.6l2.3-2.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 111.4-1.4L9 11.6V4a1 1 0 011-1zM4 15a1 1 0 011 1v1h10v-1a1 1 0 112 0v1a2 2 0 01-2 2H5a2 2 0 01-2-2v-1a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    <span class="lbl">JPG</span>
                </button>

                <button type="button" class="btn btn-pdf keep" data-dl="pdf" title="Download PDF">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v7.6l2.3-2.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 111.4-1.4L9 11.6V4a1 1 0 011-1zM4 15a1 1 0 011 1v1h10v-1a1 1 0 112 0v1a2 2 0 01-2 2H5a2 2 0 01-2-2v-1a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    <span class="lbl">PDF</span>
                </button>

                @unless($isPublic)
                    <span class="divider"></span>

                    <a href="{{ route('doctor.prescriptions.show', $prescription->id) }}" class="btn" title="View details">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.5 10C1.7 6.4 5.5 4 10 4s8.3 2.4 9.5 6c-1.2 3.6-5 6-9.5 6S1.7 13.6.5 10zM10 14a4 4 0 100-8 4 4 0 000 8z" clip-rule="evenodd"/></svg>
                        <span class="lbl">Details</span>
                    </a>

                    <a href="{{ route('doctor.prescriptions.edit', $prescription->id) }}" class="btn" title="Edit (E)">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.6 2.6a2 2 0 112.8 2.8l-9 9-3.7.9.9-3.7 9-9z"/></svg>
                        <span class="lbl">Edit</span> <kbd>E</kbd>
                    </a>

                    @if($isInvestigation)
                        <a href="{{ route('doctor.prescriptions.create', ['from' => $prescription->id]) }}" class="btn btn-warn" title="Create follow-up with reports">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                            <span class="lbl">Follow-up (Report)</span>
                        </a>
                    @endif

                    <a href="{{ route('doctor.prescriptions.create') }}" class="btn" title="New prescription (N)">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                        <span class="lbl">New Rx</span> <kbd>N</kbd>
                    </a>

                    <form method="POST"
                          action="{{ route('doctor.prescriptions.reprint', $prescription->id) }}"
                          onsubmit="return confirm('Reprint this prescription? Reprint rules/charges may apply.');">
                        @csrf
                        <button type="submit" class="btn" title="Reprint">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.1A7 7 0 1117 10a1 1 0 11-2 0 5 5 0 10-1.6 3.7 1 1 0 111.3 1.5A7 7 0 015 12.9V15a1 1 0 11-2 0V3a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                            <span class="lbl">Reprint</span>
                        </button>
                    </form>
                @endunless

                <button type="button" class="btn btn-primary" onclick="window.print()" title="Print (P)">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v2H2.5A1.5 1.5 0 001 7.5v5A1.5 1.5 0 002.5 14H3v2a2 2 0 002 2h10a2 2 0 002-2v-2h.5a1.5 1.5 0 001.5-1.5v-5A1.5 1.5 0 0017.5 6H17V4a2 2 0 00-2-2H5zm0 2h10v2H5V4zm0 9h10v3H5v-3z" clip-rule="evenodd"/></svg>
                    <span class="lbl">Print</span> @unless($isPublic)<kbd>P</kbd>@endunless
                </button>
            </div>
        </div>
    </header>

    {{-- ===== Flash messages ===== --}}
    @if(session('success') || session('error'))
        <div class="rx-flash">
            @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="err">{{ session('error') }}</div>@endif
        </div>
    @endif

    {{-- ===== Prescription paper ===== --}}
    <main class="rx-stage" id="stage">
        <div class="rx-sheet" id="sheet">
            {!! $html !!}
        </div>
    </main>

    {{-- ===== Share modal (doctor only) ===== --}}
    @unless($isPublic)
        <div class="rx-modal" id="shareModal" role="dialog" aria-modal="true" aria-labelledby="shareTitle">
            <div class="rx-card">
                <h3 id="shareTitle">Prescription link share korun</h3>
                <p>Ei link ye-i pabe, login charai prescription dekhte ar JPG/PDF download korte parbe. Dorkar hole "Link bondho" diye bondho korte paren.</p>
                <div class="rx-linkrow">
                    <input type="text" id="shareUrl" readonly>
                    <button type="button" class="b" id="copyBtn">Copy</button>
                </div>
                <div class="rx-grid">
                    <a class="b b-wa" id="shareWa" target="_blank" rel="noopener">WhatsApp</a>
                    <a class="b b-sms" id="shareSms">SMS</a>
                    <button type="button" class="b b-nat" id="shareNative" style="display:none">Share</button>
                </div>
                <div class="rx-foot">
                    <button type="button" class="b b-danger" id="revokeBtn">Link bondho</button>
                    <button type="button" class="b b-close" id="closeShare">Close</button>
                </div>
            </div>
        </div>
    @endunless

    <div class="rx-toast" id="toast" role="status" aria-live="polite"></div>

    <script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
    (function () {
        var CFG = @json($cfg);
        var stage = document.getElementById('stage');
        var sheet = document.getElementById('sheet');
        var label = document.getElementById('zoomLabel');
        var toastEl = document.getElementById('toast');
        var zoom = 1;

        /* ---------------- Toast ---------------- */
        var toastTimer;
        function toast(msg, isErr, keep) {
            toastEl.textContent = msg;
            toastEl.className = 'rx-toast show' + (isErr ? ' err' : '');
            clearTimeout(toastTimer);
            if (!keep) toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, isErr ? 6000 : 2200);
        }
        function errMsg(e) { return (e && e.message) || (e && e.type) || String(e); }

        /* ---------------- Zoom / Fit ---------------- */
        function apply() { stage.style.zoom = zoom; label.textContent = Math.round(zoom * 100) + '%'; }
        function fit() {
            stage.style.zoom = 1;
            var available = window.innerWidth - 16;
            zoom = Math.min(1, available / sheet.offsetWidth);
            apply();
        }
        document.getElementById('zoomIn').onclick  = function () { zoom = Math.min(2, zoom + 0.1); apply(); };
        document.getElementById('zoomOut').onclick = function () { zoom = Math.max(0.3, zoom - 0.1); apply(); };
        document.getElementById('zoomFit').onclick = fit;
        if (window.innerWidth < sheet.offsetWidth + 24) { fit(); }

        /* ---------------- Keyboard (doctor only) ---------------- */
        if (!CFG.isPublic) {
            document.addEventListener('keydown', function (e) {
                if (e.ctrlKey || e.metaKey || e.altKey) return;
                var tag = (e.target.tagName || '').toLowerCase();
                if (tag === 'input' || tag === 'textarea' || e.target.isContentEditable) return;
                var modal = document.getElementById('shareModal');
                if (modal && modal.classList.contains('open')) {
                    if (e.key === 'Escape') modal.classList.remove('open');
                    return;
                }
                switch (e.key.toLowerCase()) {
                    case 'p': e.preventDefault(); window.print(); break;
                    case 'e': window.location.href = CFG.editUrl; break;
                    case 'n': window.location.href = CFG.createUrl; break;
                    case 'escape': window.location.href = CFG.indexUrl; break;
                }
            });
        }

        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
        }

        /* ---------------- Share (doctor only) ---------------- */
        if (!CFG.isPublic) {
            var modal = document.getElementById('shareModal');
            var urlInput = document.getElementById('shareUrl');
            var shareUrl = null;
            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            function setLinks(url) {
                shareUrl = url; urlInput.value = url;
                var text = 'আপনার প্রেসক্রিপশন দেখতে ও ডাউনলোড করতে এই লিংকে যান: ' + url;
                document.getElementById('shareWa').href = 'https://wa.me/?text=' + encodeURIComponent(text);
                document.getElementById('shareSms').href = 'sms:?&body=' + encodeURIComponent(text);
                if (navigator.share) document.getElementById('shareNative').style.display = '';
            }
            function post(url) {
                return fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                    .then(function (r) { if (!r.ok) throw new Error('Server error ' + r.status); return r.json(); });
            }

            document.getElementById('btnShare').onclick = function () {
                if (shareUrl) { modal.classList.add('open'); return; }
                var b = this; b.disabled = true; toast('Link toiri hocche...', false, true);
                post(CFG.shareCreate).then(function (d) {
                    setLinks(d.url); modal.classList.add('open'); toastEl.classList.remove('show');
                }).catch(function (e) { toast('Link toiri hoyni: ' + errMsg(e), true); })
                  .then(function () { b.disabled = false; });
            };
            document.getElementById('closeShare').onclick = function () { modal.classList.remove('open'); };
            modal.addEventListener('click', function (e) { if (e.target === modal) modal.classList.remove('open'); });

            document.getElementById('copyBtn').onclick = function () {
                var btn = this;
                (navigator.clipboard ? navigator.clipboard.writeText(urlInput.value) : Promise.reject())
                    .catch(function () { urlInput.focus(); urlInput.select(); document.execCommand('copy'); })
                    .then(function () { btn.textContent = 'Copied \u2713'; setTimeout(function () { btn.textContent = 'Copy'; }, 1800); });
            };
            document.getElementById('shareNative').onclick = function () {
                navigator.share({ title: 'Prescription', text: 'আপনার প্রেসক্রিপশন', url: shareUrl }).catch(function () {});
            };
            document.getElementById('revokeBtn').onclick = function () {
                if (!confirm('Link bondho korle ager link ar kaj korbe na. Nishchit?')) return;
                post(CFG.shareRevoke).then(function () {
                    shareUrl = null; urlInput.value = ''; modal.classList.remove('open'); toast('Link bondho kora hoyeche');
                }).catch(function (e) { toast('Bondho hoyni: ' + errMsg(e), true); });
            };
        }

        /* ---------------- JPG / PDF download ---------------- */
        var TARGET_W = Math.round(CFG.wMm / 25.4 * 300);          // 300 DPI (A4 = 2480px)
        var GIF = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
        function wait(ms) { return new Promise(function (r) { setTimeout(r, ms); }); }

        function proxify(root) {          // remote image gulo same-origin kore (CORS er jonno)
            root.querySelectorAll('img').forEach(function (img) {
                try {
                    var u = new URL(img.getAttribute('src') || '', location.href);
                    if (u.origin !== location.origin && u.protocol === 'https:') {
                        img.removeAttribute('srcset');
                        img.setAttribute('src', CFG.proxy + '?u=' + encodeURIComponent(u.href));
                    }
                } catch (e) {}
            });
        }
        function imgsReady(root) {
            return Promise.all(Array.prototype.map.call(root.querySelectorAll('img'), function (i) {
                return i.complete ? Promise.resolve() : new Promise(function (r) { i.onload = i.onerror = r; });
            }));
        }

        function snap() {
            var sx = window.scrollX, sy = window.scrollY;
            // Asol page e hat na diye, offscreen (zoom charai) clone e exact paper size e render
            var host = document.createElement('div');
            host.setAttribute('aria-hidden', 'true');
            host.style.cssText = 'position:fixed;left:-20000px;top:0;width:' + CFG.wPx + 'px;pointer-events:none;';
            var clone = sheet.cloneNode(true);
            clone.removeAttribute('id');
            clone.style.cssText += ';width:' + CFG.wPx + 'px;min-height:' + CFG.hPx + 'px;box-shadow:none;margin:0;';
            host.appendChild(clone);
            document.body.appendChild(host);
            proxify(clone);

            var done = function () { if (host.parentNode) host.parentNode.removeChild(host); window.scrollTo(sx, sy); };
            var opts = function (ratio, skipFonts) {
                return { pixelRatio: ratio, backgroundColor: '#ffffff', cacheBust: false, imagePlaceholder: GIF,
                         skipFonts: !!skipFonts, width: CFG.wPx, height: Math.max(clone.scrollHeight, CFG.hPx), style: { margin: '0' } };
            };
            var attempts = [[TARGET_W / CFG.wPx, false], [2, false], [1.5, true], [1, true]];
            var lastErr;
            function tryAt(i) {
                if (i >= attempts.length) return Promise.reject(lastErr || new Error('render failed'));
                return htmlToImage.toCanvas(clone, opts(attempts[i][0], attempts[i][1]))
                    .catch(function (e) { lastErr = e; return wait(200).then(function () { return tryAt(i + 1); }); });
            }
            var fonts = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
            return fonts.then(function () { return imgsReady(clone); })
                .then(function () { return wait(250); })
                .then(function () { return htmlToImage.toCanvas(clone, opts(1, true)).catch(function () {}); })   // warm-up
                .then(function () { return tryAt(0); })
                .then(function (c) { done(); return c; }, function (e) { done(); throw e; });
        }

        function pageCanvas(c) {          // paper aspect e white-background canvas
            var out = document.createElement('canvas');
            out.width = c.width; out.height = Math.round(c.width * CFG.hMm / CFG.wMm);
            var g = out.getContext('2d'); g.fillStyle = '#fff'; g.fillRect(0, 0, out.width, out.height);
            var r = Math.min(out.width / c.width, out.height / c.height);
            g.drawImage(c, (out.width - c.width * r) / 2, 0, c.width * r, c.height * r);
            return out;
        }

        function saveJpg(c) {
            return new Promise(function (resolve, reject) {
                pageCanvas(c).toBlob(function (blob) {
                    if (!blob) return reject(new Error('Image toiri hoyni (phone memory kom hote pare)'));
                    var u = URL.createObjectURL(blob), a = document.createElement('a');
                    a.download = CFG.file + '-' + Math.round(CFG.wMm) + 'x' + Math.round(CFG.hMm) + '.jpg';
                    a.href = u; a.style.display = 'none';
                    document.body.appendChild(a); a.click();
                    setTimeout(function () { a.remove(); URL.revokeObjectURL(u); }, 4000);
                    resolve();
                }, 'image/jpeg', 0.92);
            });
        }
        function savePdf(c) {
            var pw = CFG.wMm, ph = CFG.hMm;
            var pdf = new window.jspdf.jsPDF('p', 'mm', [pw, ph]);
            var h = c.height * pw / c.width, img = c.toDataURL('image/jpeg', 0.92), y = 0;
            pdf.addImage(img, 'JPEG', 0, y, pw, h);
            var left = h - ph;
            while (left > 0.5) { y -= ph; pdf.addPage(); pdf.addImage(img, 'JPEG', 0, y, pw, h); left -= ph; }
            pdf.save(CFG.file + '.pdf');
        }

        document.querySelectorAll('[data-dl]').forEach(function (btn) {
            btn.addEventListener('click', function (ev) {
                ev.preventDefault();
                var type = btn.getAttribute('data-dl');
                var all = document.querySelectorAll('[data-dl]');
                all.forEach(function (b) { b.disabled = true; });
                toast('Toiri hocche... ekto opekkha korun', false, true);
                Promise.resolve().then(function () {
                    if (typeof htmlToImage === 'undefined') throw new Error('Library load hoyni - internet check korun');
                    if (type === 'pdf' && !(window.jspdf && window.jspdf.jsPDF)) throw new Error('PDF library load hoyni - internet check korun');
                    return snap();
                }).then(function (c) {
                    return type === 'jpg' ? saveJpg(c) : savePdf(c);
                }).then(function () { toast('Download shuru hoyeche \u2713'); },
                        function (e) { toast('Download hoyni: ' + errMsg(e), true); })
                  .then(function () { all.forEach(function (b) { b.disabled = false; }); });
            });
        });
    })();
    </script>
</body>
</html>