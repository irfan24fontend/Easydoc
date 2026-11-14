<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $status }} · {{ $title }} | EasyDoc</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f1f5f7; color: #173236; font-family: Inter, "Noto Sans Bengali", sans-serif; }
        .error-card { width: min(100%, 560px); padding: clamp(28px, 6vw, 52px); border: 1px solid #dce7e8; border-radius: 22px; background: #fff; box-shadow: 0 18px 55px rgba(16, 38, 43, .1); text-align: center; }
        .brand { display: inline-flex; align-items: center; gap: 10px; color: #0c7771; font-size: 1rem; font-weight: 800; }
        .brand-icon { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 12px; background: #e4f4f1; font-size: 1.25rem; }
        .status { margin: 28px 0 8px; color: #0c8c84; font-size: clamp(3.5rem, 12vw, 5rem); font-weight: 800; letter-spacing: -.07em; line-height: 1; }
        h1 { margin: 0; font-size: clamp(1.4rem, 5vw, 1.9rem); font-weight: 800; }
        p { margin: 12px 0 0; color: #64748b; line-height: 1.7; }
        .actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; margin-top: 28px; }
        .button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 18px; border: 1px solid #d7e2e4; border-radius: 11px; background: #fff; color: #29474a; font: inherit; font-size: .9rem; font-weight: 700; text-decoration: none; cursor: pointer; }
        .button.primary { border-color: #0c8c84; background: #0c8c84; color: #fff; }
        .button:hover { filter: brightness(.97); }
        @media (max-width: 480px) { .actions { flex-direction: column; } .button { width: 100%; } }
    </style>
</head>
<body>
    <main class="error-card" role="main">
        <div class="brand"><span class="brand-icon" aria-hidden="true">✚</span><span>EasyDoc</span></div>
        <div class="status" aria-label="{{ __('Error code') }} {{ $status }}">{{ $status }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <div class="actions">
            <button class="button" type="button" onclick="history.length > 1 ? history.back() : window.location.assign('/')">{{ __('Go back') }}</button>
            <a class="button primary" href="{{ url('/') }}">{{ __('Go to home') }}</a>
        </div>
    </main>
</body>
</html>
