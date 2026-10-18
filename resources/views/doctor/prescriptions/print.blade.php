@php
    $paperKey = strtoupper(trim($paperSize ?? 'A4'));
    $dimensions = [
        'A4'     => ['210mm', '297mm'],
        'A5'     => ['148mm', '210mm'],
        'LETTER' => ['216mm', '279mm'],
        'LEGAL'  => ['216mm', '356mm'],
    ];
    if (!isset($dimensions[$paperKey])) {
        $paperKey = 'A4';
    }
    [$paperW, $paperH] = $dimensions[$paperKey];

    $isInvestigation = ($prescription->type ?? null) === 'investigation_only';
    $patientName     = $prescription->patient->name ?? 'Patient';
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prescription #{{ $prescription->id }} - {{ $patientName }}</title>

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
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Noto Sans Bengali", sans-serif;
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
        .rx-meta-title {
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .rx-meta-sub { font-size: 12px; color: var(--bar-muted); margin-top: 2px; }

        .rx-badge {
            display: inline-block;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: .03em;
            text-transform: uppercase;
            padding: 2px 7px;
            border-radius: 999px;
            background: #f59e0b;
            color: #1f2937;
            margin-left: 6px;
            vertical-align: middle;
        }

        .rx-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .rx-actions form { margin: 0; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            line-height: 1;
            color: var(--bar-fg);
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s, border-color .15s;
            font-family: inherit;
        }
        .btn:hover { background: rgba(255, 255, 255, .16); }
        .btn svg { width: 16px; height: 16px; flex: none; }

        .btn-primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        .btn-primary:hover { background: var(--accent-hover); }

        .btn-warn { border-color: rgba(245, 158, 11, .5); color: #fcd34d; }

        .btn kbd {
            font-family: inherit;
            font-size: 10px;
            padding: 1px 5px;
            border-radius: 4px;
            background: rgba(255, 255, 255, .15);
            color: inherit;
            opacity: .85;
        }

        .rx-zoom {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 8px;
            overflow: hidden;
        }
        .rx-zoom button {
            background: transparent;
            color: var(--bar-fg);
            border: 0;
            padding: 8px 11px;
            font-size: 13px;
            cursor: pointer;
            font-family: inherit;
        }
        .rx-zoom button:hover { background: rgba(255, 255, 255, .12); }
        .rx-zoom span { font-size: 12px; min-width: 46px; text-align: center; color: var(--bar-muted); }

        .divider { width: 1px; height: 24px; background: rgba(255, 255, 255, .14); }

        /* ---------- Flash ---------- */
        .rx-flash {
            max-width: 1200px;
            margin: 12px auto 0;
            padding: 0 16px;
        }
        .rx-flash div {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13.5px;
        }
        .rx-flash .ok { background: var(--success-bg); color: var(--success-fg); }
        .rx-flash .err { background: var(--error-bg); color: var(--error-fg); }

        /* ---------- Paper ---------- */
        .rx-stage {
            padding: 24px 12px 60px;
            display: flex;
            justify-content: center;
            overflow-x: auto;
            zoom: 1;
        }

        .rx-sheet {
            width: {{ $paperW }};
            min-height: {{ $paperH }};
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 23, 42, .18);
            flex: none;
            position: relative;
        }

        /* ---------- Print ---------- */
        @page {
            size: {{ $paperKey }};
            margin: 0;
        }

        @media print {
            html, body { background: #fff !important; min-height: 0; }
            .rx-toolbar, .rx-flash { display: none !important; }
            .rx-stage {
                padding: 0 !important;
                display: block;
                overflow: visible;
                zoom: 1 !important;
            }
            .rx-sheet {
                width: 100% !important;
                min-height: 0 !important;
                box-shadow: none !important;
            }
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        @media (max-width: 640px) {
            .rx-toolbar-inner { padding: 8px 10px; }
            .btn { padding: 8px 10px; }
            .btn .lbl { display: none; }
            .btn kbd { display: none; }
            .divider { display: none; }
        }
    </style>

    {{-- Template CSS (doctor/chamber specific) --}}
    <style>
        {!! $css !!}
    </style>
</head>
<body>

    {{-- ===== Toolbar ===== --}}
    <header class="rx-toolbar">
        <div class="rx-toolbar-inner">

            <a href="{{ route('doctor.prescriptions.index') }}" class="btn" title="Back to list (Esc)">
                <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.7 5.3a1 1 0 010 1.4L9.4 10l3.3 3.3a1 1 0 11-1.4 1.4l-4-4a1 1 0 010-1.4l4-4a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                <span class="lbl">Back</span>
            </a>

            <div class="rx-meta">
                <div class="rx-meta-title">
                    {{ $patientName }}
                    @if($isInvestigation)
                        <span class="rx-badge">Investigation Advice</span>
                    @endif
                </div>
                <div class="rx-meta-sub">
                    Rx #{{ $prescription->id }}
                    &middot; {{ $prescription->created_at->format('d M Y, h:i A') }}
                    &middot; {{ $paperKey }}
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

                <button type="button" class="btn btn-primary" onclick="window.print()" title="Print (P)">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v2H2.5A1.5 1.5 0 001 7.5v5A1.5 1.5 0 002.5 14H3v2a2 2 0 002 2h10a2 2 0 002-2v-2h.5a1.5 1.5 0 001.5-1.5v-5A1.5 1.5 0 0017.5 6H17V4a2 2 0 00-2-2H5zm0 2h10v2H5V4zm0 9h10v3H5v-3z" clip-rule="evenodd"/></svg>
                    <span class="lbl">Print</span> <kbd>P</kbd>
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

    <script>
        (function () {
            var stage = document.getElementById('stage');
            var sheet = document.getElementById('sheet');
            var label = document.getElementById('zoomLabel');
            var zoom = 1;

            function apply() {
                stage.style.zoom = zoom;
                label.textContent = Math.round(zoom * 100) + '%';
            }

            function fit() {
                stage.style.zoom = 1;
                var available = window.innerWidth - 24;
                var needed = sheet.offsetWidth;
                zoom = Math.min(1, available / needed);
                apply();
            }

            document.getElementById('zoomIn').onclick  = function () { zoom = Math.min(2, zoom + 0.1); apply(); };
            document.getElementById('zoomOut').onclick = function () { zoom = Math.max(0.3, zoom - 0.1); apply(); };
            document.getElementById('zoomFit').onclick = fit;

            // Auto-fit on small screens
            if (window.innerWidth < sheet.offsetWidth + 24) { fit(); }

            // Keyboard shortcuts (ignored while typing or with modifiers)
            document.addEventListener('keydown', function (e) {
                if (e.ctrlKey || e.metaKey || e.altKey) return;
                var tag = (e.target.tagName || '').toLowerCase();
                if (tag === 'input' || tag === 'textarea' || e.target.isContentEditable) return;

                switch (e.key.toLowerCase()) {
                    case 'p': e.preventDefault(); window.print(); break;
                    case 'e': window.location.href = @json(route('doctor.prescriptions.edit', $prescription->id)); break;
                    case 'n': window.location.href = @json(route('doctor.prescriptions.create')); break;
                    case 'escape': window.location.href = @json(route('doctor.prescriptions.index')); break;
                }
            });

            // Optional: /print?autoprint=1 opens the print dialog right away
            if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
                window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
            }
        })();
    </script>
</body>
</html>