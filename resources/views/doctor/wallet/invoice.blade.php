@php
    $company = [
        'name'    => config('easydoc.company.name', 'EasyDoc'),
        'tagline' => config('easydoc.company.tagline', 'Doctor & Prescription Management'),
        'address' => config('easydoc.company.address', ''),
        'phone'   => config('easydoc.company.phone', ''),
        'email'   => config('easydoc.company.email', ''),
    ];
    $money = fn ($v) => '৳' . number_format((float) $v, 2);
    $user  = $doctor->user;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#0f172a; --muted:#64748b; --line:#e2e8f0; --brand:#4f46e5; --paid:#15803d; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:'Inter',system-ui,sans-serif; background:#eef1f6; color:var(--ink); font-size:14px; -webkit-font-smoothing:antialiased; }

        .toolbar { max-width:210mm; margin:0 auto; padding:16px 12px 10px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
        .toolbar h1 { margin:0; font-size:17px; font-weight:800; }
        .toolbar p { margin:2px 0 0; color:var(--muted); font-size:12.5px; }
        .btn { display:inline-flex; align-items:center; gap:7px; font:600 14px 'Inter',sans-serif; padding:10px 18px; border-radius:10px; border:1px solid var(--line); background:#fff; color:var(--ink); text-decoration:none; cursor:pointer; }
        .btn.primary { background:var(--brand); border-color:var(--brand); color:#fff; }

        .sheet { position:relative; overflow:hidden; width:210mm; max-width:100%; min-height:297mm; margin:0 auto 24px; background:#fff; padding:16mm 16mm 12mm; border-radius:10px; box-shadow:0 6px 28px rgba(15,23,42,.12); }
        .stamp { position:absolute; right:14mm; top:62mm; transform:rotate(-14deg); border:4px solid var(--paid); color:var(--paid); font-weight:800; font-size:38px; letter-spacing:.14em; padding:4px 22px; border-radius:10px; opacity:.16; pointer-events:none; }

        .head { display:flex; justify-content:space-between; gap:20px; border-bottom:3px solid var(--brand); padding-bottom:14px; flex-wrap:wrap; }
        .brand { display:flex; gap:12px; align-items:center; }
        .logo { width:46px; height:46px; border-radius:10px; background:var(--brand); color:#fff; display:grid; place-items:center; font-weight:800; font-size:18px; }
        .brand h2 { margin:0; font-size:20px; font-weight:800; }
        .brand p { margin:2px 0 0; color:var(--muted); font-size:12px; }
        .title { text-align:right; }
        .title h3 { margin:0; font-size:26px; font-weight:800; letter-spacing:.08em; color:var(--brand); }
        .title .no { font-weight:700; margin-top:4px; }
        .paid-badge { display:inline-block; margin-top:6px; padding:3px 12px; border-radius:999px; background:#dcfce7; color:#166534; font-size:11px; font-weight:800; letter-spacing:.08em; }

        .meta { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin:22px 0; }
        .box h4 { margin:0 0 6px; font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); font-weight:700; }
        .box p { margin:2px 0; }
        .box .strong { font-weight:700; font-size:15px; }

        table { width:100%; border-collapse:collapse; margin-top:6px; }
        th { text-align:left; background:#f1f5f9; font-size:11px; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); padding:10px 12px; }
        td { padding:14px 12px; border-bottom:1px solid var(--line); vertical-align:top; }
        .r { text-align:right; }
        td small { display:block; color:var(--muted); margin-top:3px; }

        .totals { margin:18px 0 0 auto; width:300px; max-width:100%; }
        .totals .line { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px dashed var(--line); }
        .totals .grand { border-bottom:0; border-top:2px solid var(--ink); margin-top:6px; padding-top:10px; font-size:16px; font-weight:800; }

        .note { margin-top:22px; padding:12px 14px; background:#f8fafc; border:1px solid var(--line); border-radius:8px; font-size:13px; }
        .foot { margin-top:60px; text-align:center; font-size:11px; color:var(--muted); border-top:1px solid var(--line); padding-top:8px; }

        @page { size: A4; margin: 0; }
        @media print {
            body { background:#fff; }
            .toolbar { display:none !important; }
            .sheet { width:100%; min-height:100vh; margin:0; box-shadow:none; border-radius:0; }
            * { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        }
        @media (max-width: 640px) {
            .sheet { padding:18px 14px; min-height:0; }
            .meta { grid-template-columns:1fr; gap:14px; }
            .title { text-align:left; }
            .stamp { font-size:28px; top:70mm; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <div>
        <h1>Invoice {{ $invoice->invoice_no }}</h1>
        <p>Press "Download PDF", then choose "Save as PDF" as the printer.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="{{ route('doctor.wallet.invoices') }}" class="btn">All invoices</a>
        <button type="button" class="btn primary" onclick="window.print()">Download PDF</button>
    </div>
</div>

<div class="sheet">
    <div class="stamp">PAID</div>

    <div class="head">
        <div class="brand">
            <div class="logo">ED</div>
            <div>
                <h2>{{ $company['name'] }}</h2>
                <p>{{ $company['tagline'] }}</p>
                @if($company['address'])<p>{{ $company['address'] }}</p>@endif
                @if($company['phone'] || $company['email'])
                    <p>{{ $company['phone'] }}{{ $company['phone'] && $company['email'] ? ' · ' : '' }}{{ $company['email'] }}</p>
                @endif
            </div>
        </div>
        <div class="title">
            <h3>INVOICE</h3>
            <div class="no">{{ $invoice->invoice_no }}</div>
            <div class="paid-badge">PAID</div>
        </div>
    </div>

    <div class="meta">
        <div class="box">
            <h4>Billed to</h4>
            <p class="strong">{{ $doctor->displayName() }}</p>
            @if($doctor->qualification)<p>{{ $doctor->qualification }}</p>@endif
            @if($doctor->doctor_code)<p>Code: {{ $doctor->doctor_code }}</p>@endif
            @if($doctor->bmdc_number)<p>BMDC: {{ $doctor->bmdc_number }}</p>@endif
            @if($user?->email)<p>{{ $user->email }}</p>@endif
            @if($user?->phone)<p>{{ $user->phone }}</p>@endif
        </div>
        <div class="box" style="text-align:right">
            <h4>Payment details</h4>
            <p><b>Date:</b> {{ $invoice->issued_at->format('d M Y, h:i A') }}</p>
            <p><b>Method:</b> {{ $invoice->payment_method ?: '—' }}</p>
            @if($invoice->reference)<p><b>TrxID:</b> {{ $invoice->reference }}</p>@endif
            <p><b>Status:</b> Paid</p>
        </div>
    </div>

    <table>
        <thead><tr><th style="width:50px">#</th><th>Description</th><th class="r" style="width:150px">Amount</th></tr></thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Wallet recharge<small>Balance added to your {{ $company['name'] }} wallet</small></td>
                <td class="r">{{ $money($invoice->amount) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="totals">
        <div class="line"><span>Previous balance</span><span>{{ $money($invoice->balance_before) }}</span></div>
        <div class="line"><span>Amount paid</span><span>+ {{ $money($invoice->amount) }}</span></div>
        <div class="line grand"><span>New balance</span><span>{{ $money($invoice->balance_after) }}</span></div>
    </div>

    @if($invoice->note)
        <div class="note"><b>Note:</b> {{ $invoice->note }}</div>
    @endif

    <div class="foot">
        This is a computer-generated invoice and needs no signature. &middot; {{ $company['name'] }}
    </div>
</div>

</body>
</html>
