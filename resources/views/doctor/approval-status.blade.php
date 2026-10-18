<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Application status | {{ config('app.name', 'EasyDoc') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Hind+Siliguri:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#10262b; --ink-soft:#46606a; --teal:#0c8c84; --teal-deep:#0a6b65; --mint:#e3f3f0; --mist:#f3f7f7; --line:#d3e0e0; --amber:#f4b63f; --danger:#b3261e; --danger-bg:#fdecea;
                --display:"Bricolage Grotesque","Hind Siliguri",system-ui,sans-serif; --body:"Hind Siliguri",system-ui,-apple-system,"Segoe UI",sans-serif; }
        *,*::before,*::after{box-sizing:border-box}
        body{margin:0;font:400 16px/1.6 var(--body);color:var(--ink);background:var(--mist);min-height:100vh;display:grid;place-items:center;padding:24px;-webkit-font-smoothing:antialiased}
        .card{width:min(560px,100%);background:#fff;border:1px solid var(--line);border-radius:24px;padding:40px 36px;box-shadow:0 30px 60px -36px rgba(16,38,43,.35)}
        .icon{width:60px;height:60px;border-radius:18px;display:grid;place-items:center;margin-bottom:22px}
        .icon.pending{background:#fff3d6;color:#8a5a00}.icon.rejected{background:var(--danger-bg);color:var(--danger)}.icon.neutral{background:var(--mint);color:var(--teal-deep)}
        h1{font:800 28px/1.15 var(--display);letter-spacing:-.02em;margin:0 0 10px}
        p{margin:0 0 14px;color:var(--ink-soft)}
        .summary{margin:22px 0;border:1px solid var(--line);border-radius:14px;overflow:hidden}
        .summary div{display:flex;justify-content:space-between;gap:16px;padding:11px 16px;border-bottom:1px solid var(--line);font-size:15px}
        .summary div:last-child{border-bottom:0}
        .summary span:first-child{color:var(--ink-soft)}.summary span:last-child{font-weight:600;text-align:right}
        .reason{background:var(--danger-bg);color:#7a1a14;border-radius:12px;padding:14px 16px;margin:18px 0;font-size:15px}
        .reason strong{display:block;margin-bottom:4px}
        .btn{display:inline-flex;align-items:center;justify-content:center;font:600 16px var(--body);padding:12px 24px;border-radius:999px;border:2px solid var(--ink);background:transparent;color:var(--ink);cursor:pointer;min-height:46px;text-decoration:none}
        .btn:hover{background:var(--ink);color:#fff}
        .row{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px}
        .help{font-size:14.5px;margin-top:22px;padding-top:18px;border-top:1px solid var(--line)}
        :focus-visible{outline:3px solid var(--amber);outline-offset:2px}
    </style>
</head>
<body>
<main class="card">
    @php $company = config('easydoc.company'); @endphp

    @if (! $doctor)
        <div class="icon neutral" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
        </div>
        <h1>Your registration is incomplete</h1>
        <p>We could not find the doctor details for this account. Please contact support so we can complete your registration.</p>

    @elseif ($doctor->isRejected())
        <div class="icon rejected" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
        </div>
        <h1>Your application was not approved</h1>
        <p>We were unable to approve your registration at this time.</p>
        @if ($doctor->rejection_reason)
            <div class="reason"><strong>Reason from the reviewer</strong>{{ $doctor->rejection_reason }}</div>
        @endif
        <p>If you think this is a mistake, or you can provide the missing information, please contact support.</p>

    @else
        <div class="icon pending" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </div>
        <h1>Your application is under review</h1>
        <p>Thank you, {{ auth()->user()->name }}. An administrator is verifying your BMDC registration. You can use {{ config('app.name', 'EasyDoc') }} as soon as your account is approved.</p>
        <p>Please check back by logging in again later.</p>
    @endif

    @if ($doctor)
        <div class="summary" aria-label="Submitted details">
            <div><span>BMDC number</span><span>{{ $doctor->bmdc_number ?? '—' }}</span></div>
            <div><span>Speciality</span><span>{{ $doctor->speciality ?? '—' }}</span></div>
            <div><span>Submitted</span><span>{{ $doctor->created_at?->format('d M Y') }}</span></div>
        </div>
    @endif

    <div class="row">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn">Log out</button>
        </form>
        <a class="btn" href="{{ url('/') }}">Back to home</a>
    </div>

    @if (! empty($company['phone']) || ! empty($company['email']))
        <p class="help">Need help? Contact support:
            @if (! empty($company['phone'])) <strong>{{ $company['phone'] }}</strong> @endif
            @if (! empty($company['email'])) &middot; {{ $company['email'] }} @endif
        </p>
    @endif
</main>
</body>
</html>
