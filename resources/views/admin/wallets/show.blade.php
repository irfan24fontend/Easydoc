@php
    $doctor    = $invoice->doctor;
    $company   = [
        'name'    => config('easydoc.company.name', 'EasyDoc'),
        'tagline' => config('easydoc.company.tagline', 'Doctor & Prescription Management'),
        'address' => config('easydoc.company.address', ''),
        'phone'   => config('easydoc.company.phone', ''),
        'email'   => config('easydoc.company.email', ''),
    ];
    $money = fn ($v) => '৳' . number_format((float) $v, 2);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        :root { --ink:#0f172a; --muted:#64748b; --line:#e2e8f0; --brand:#4f46e5; }
        * { box-sizing:border-box; }
        body { margin:0; background:#e5e7eb; color:var(--ink); font-family:system-ui,-apple-system,"Segoe UI",Roboto,"Noto Sans Bengali",sans-serif; font-size:14px; }

        .toolbar { position:sticky; top:0; z-index:10; background:#0f172a; color:#e2e8f0; padding:10px 16px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .toolbar .title { flex:1; font-weight:600; }
        .btn { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:8px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.08); color:#e2e8f0; font-size:13px; font-weight:600; text-decoration:none; cursor:pointer; font-family:inherit; }
        .btn:hover { background:rgba(255,255,255,.16); }
        .btn-primary { background:var(--brand); border-color:var(--brand); color:#fff; }
        .flash { max-width:210mm; margin:12px auto 0; padding:10px 14px; background:#dcfce7; color:#166534; border-radius:8px; }

        .sheet { width:210mm; min-height:297mm; margin:20px auto; background:#fff; padding:16mm 16mm 12mm; box-shadow:0 4px 24px rgba(15,23,42,.18); position:relative; }

        .head { display:flex; justify-content:space-between; gap:20px; border-bottom:3px solid var(--brand); padding-bottom:14px; }
        .brand { display:flex; gap:12px; align-items:center; }
        .logo { width:46px; height:46px; border-radius:10px; background:var(--brand); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px; }
        .brand h1 { margin:0; font-size:20px; }
        .brand p { margin:2px 0 0; color:var(--muted); font-size:12px; }
        .inv-title { text-align:right; }
        .inv-title h2 { margin:0; font-size:26px; letter-spacing:.08em; color:var(--brand); }
        .inv-title .no { font-weight:700; margin-top:4px; }
        .badge { display:inline-block; margin-top:6px; padding:3px 10px; border-radius:999px; background:#dcfce7; color:#166534; font-size:11px; font-weight:700; letter-spacing:.05em; }

        .meta { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin:22px 0; }
        .box h4 { margin:0 0 6px; font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); }
        .box p { margin:2px 0; }
        .box .strong { font-weight:700; font-size:15px; }

        table.items { width:100%; border-collapse:collapse; margin-top:6px; }
        table.items th { text-align:left; background:#f1f5f9; font-size:11px; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); padding:10px 12px; }
        table.items td { padding:14px 12px; border-bottom:1px solid var(--line); vertical-align:top; }
        table.items th.r, table.items td.r { text-align:right; }
        .desc small { display:block; color:var(--muted); margin-top:3px; }

        .totals { margin:18px 0 0 auto; width:300px; }
        .totals .row { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px dashed var(--line); }
        .totals .row.grand { border-bottom:0; border-top:2px solid var(--ink); margin-top:6px; padding-top:10px; font-size:16px; font-weight:800; }

        .note { margin-top:22px; padding:12px 14px; background:#f8fafc; border:1px solid var(--line); border-radius:8px; font-size:13px; }
        .sign { display:flex; justify-content:space-between; margin-top:70px; }
        .sign div { width:200px; text-align:center; border-top:1px solid var(--ink); padding-top:6px; font-size:12px; color:var(--muted); }
        .foot { position:absolute; left:16mm; right:16mm; bottom:10mm; text-align:center; font-size:11px; color:var(--muted); border-top:1px solid var(--line); padding-top:8px; }

        @page { size:A4; margin:0; }
        @media print {
            body { background:#fff; }
            .toolbar, .flash { display:none !important; }
            .sheet { margin:0; box-shadow:none; width:100%; min-height:100vh; }
            * { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <a href="{{ route('admin.wallets.index') }}" class="btn">← Wallets</a>
        <a href="{{ route('admin.invoices.index') }}" class="btn">All Invoices</a>
        <div class="title">Invoice {{ $invoice->invoice_no }}</div>
        <button type="button" class="btn btn-primary" onclick="window.print()">🖨 Print / Save as PDF</button>
    </div>

    @if(session('success'))
        <div class="flash">{{ session('success') }}</div>
    @endif

    <div class="sheet">
        <div class="head">
            <div class="brand">
                <div class="logo">ED</div>
                <div>
                    <h1>{{ $company['name'] }}</h1>
                    <p>{{ $company['tagline'] }}</p>
                    @if($company['address'])<p>{{ $company['address'] }}</p>@endif
                    @if($company['phone'] || $company['email'])
                        <p>{{ $company['phone'] }}{{ $company['phone'] && $company['email'] ? ' · ' : '' }}{{ $company['email'] }}</p>
                    @endif
                </div>
            </div>
            <div class="inv-title">
                <h2>INVOICE</h2>
                <div class="no">{{ $invoice->invoice_no }}</div>
                <div class="badge">WALLET CREDITED</div>
            </div>
        </div>

        <div class="meta">
            <div class="box">
                <h4>Billed To</h4>
                <p class="strong">Dr. {{ $doctor->user->name ?? '—' }}</p>
                @if(!empty($doctor->qualification))<p>{{ $doctor->qualification }}</p>@endif
                @if(!empty($doctor->bmdc_number))<p>BMDC: {{ $doctor->bmdc_number }}</p>@endif
                @if(!empty($doctor->user->email))<p>{{ $doctor->user->email }}</p>@endif
            </div>
            <div class="box" style="text-align:right;">
                <h4>Invoice Details</h4>
                <p><b>Date:</b> {{ $invoice->issued_at->format('d M Y, h:i A') }}</p>
                <p><b>Payment Method:</b> {{ $invoice->payment_method ?: '—' }}</p>
                @if($invoice->reference)<p><b>Reference:</b> {{ $invoice->reference }}</p>@endif
                <p><b>Issued By:</b> {{ $invoice->issuer->name ?? 'Super Admin' }}</p>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Description</th>
                    <th class="r" style="width:150px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td class="desc">
                        Wallet Recharge
                        <small>Prescription wallet-e balance jog kora hoyeche</small>
                    </td>
                    <td class="r">{{ $money($invoice->amount) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <div class="row"><span>Previous Balance</span><span>{{ $money($invoice->balance_before) }}</span></div>
            <div class="row"><span>Recharge Amount</span><span>+ {{ $money($invoice->amount) }}</span></div>
            <div class="row grand"><span>New Balance</span><span>{{ $money($invoice->balance_after) }}</span></div>
        </div>

        @if($invoice->note)
            <div class="note"><b>Note:</b> {{ $invoice->note }}</div>
        @endif

        <div class="sign">
            <div>Received By (Doctor)</div>
            <div>Authorized Signature</div>
        </div>

        <div class="foot">
            This is a computer-generated invoice. · {{ $company['name'] }} · Invoice {{ $invoice->invoice_no }}
        </div>
    </div>

</body>
</html>