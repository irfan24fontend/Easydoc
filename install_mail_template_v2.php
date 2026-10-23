<?php
/**
 * EasyDoc: Email template redesign installer
 *
 * Project root e (jekhane `artisan` ache) rekhe chalan:
 *     php install_mail_template_v2.php
 *
 * Ki kore:
 *  - resources/views/emails/notice.blade.php ke notun, beshi professional design diye replace kore
 *  - Shob email (registration, login, chamber, approve/reject) ei template-i use kore, tai
 *    kono controller/service code change lage na
 *  Backup: notice.blade.php.bak-v2. Dobar chalale duplicate kore na.
 */

if (! file_exists(__DIR__ . '/artisan')) {
    fwrite(STDERR, "ERROR: `artisan` file paoa jayni. Laravel project root e rekhe chalan.\n");
    exit(1);
}

$root = __DIR__;
$path = $root . '/resources/views/emails/notice.blade.php';

$content = <<<'MAIL_TPL_EOF'
@php
    $tones = [
        'success' => ['fg' => '#15803d', 'bg' => '#ecfdf3', 'ring' => '#86efac', 'grad1' => '#16a34a', 'grad2' => '#15803d'],
        'warning' => ['fg' => '#b45309', 'bg' => '#fffbeb', 'ring' => '#fcd34d', 'grad1' => '#f59e0b', 'grad2' => '#d97706'],
        'danger'  => ['fg' => '#b91c1c', 'bg' => '#fef2f2', 'ring' => '#fca5a5', 'grad1' => '#ef4444', 'grad2' => '#b91c1c'],
        'info'    => ['fg' => '#4338ca', 'bg' => '#eef2ff', 'ring' => '#c7d2fe', 'grad1' => '#4f46e5', 'grad2' => '#4338ca'],
    ];
    $t = $tones[$tone ?? 'info'] ?? $tones['info'];

    $appName = config('app.name', 'EasyDoc');
    $lines   = $lines ?? [];
    $details = $details ?? [];
    $button  = $button ?? null;
    $appUrl  = $appUrl ?? config('app.url');
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<meta name="x-apple-disable-message-reformatting">
<title>{{ $title ?? $appName }}</title>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<![endif]-->
</head>
<body style="margin:0;padding:0;background:#eef1f6;font-family:'Segoe UI',Roboto,Helvetica,Arial,'Noto Sans Bengali',sans-serif;color:#0f172a;-webkit-font-smoothing:antialiased;">

<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;mso-hide:all;">{{ $preheader ?? ($title ?? '') }}&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f6;">
<tr><td align="center" style="padding:36px 14px;">

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">

    {{-- Wordmark above the card --}}
    <tr>
        <td align="center" style="padding-bottom:18px;">
            <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="font-size:14px;font-weight:800;letter-spacing:.08em;color:#475569;text-transform:uppercase;">
                        &#9877;&nbsp; {{ $appName }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- Card --}}
    <tr>
        <td style="background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.06), 0 18px 45px -20px rgba(15,23,42,.25);">

            {{-- Accent bar --}}
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td height="6" style="height:6px;line-height:6px;font-size:1px;background-color:{{ $t['grad1'] }};background-image:linear-gradient(90deg,{{ $t['grad1'] }},{{ $t['grad2'] }});">&nbsp;</td>
                </tr>
            </table>

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">

                {{-- Badge + title --}}
                <tr>
                    <td style="padding:34px 34px 4px;">
                        @if(!empty($badge))
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:16px;">
                                <tr>
                                    <td style="background:{{ $t['bg'] }};border:1px solid {{ $t['ring'] }};color:{{ $t['fg'] }};font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;padding:5px 12px;border-radius:999px;">
                                        {{ $badge }}
                                    </td>
                                </tr>
                            </table>
                        @endif

                        @if(!empty($title))
                            <h1 style="margin:0 0 16px;font-size:22px;line-height:1.35;font-weight:800;color:#0f172a;letter-spacing:-0.01em;">{{ $title }}</h1>
                        @endif

                        @if(!empty($greeting))
                            <p style="margin:0 0 14px;font-size:15px;line-height:1.65;color:#334155;">{{ $greeting }}</p>
                        @endif

                        @foreach($lines as $line)
                            <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#475569;">{{ $line }}</p>
                        @endforeach
                    </td>
                </tr>

                {{-- Details --}}
                @if(!empty($details))
                    <tr>
                        <td style="padding:4px 34px 6px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #edf0f5;border-radius:14px;">
                                @foreach($details as $label => $value)
                                    @if($value !== null && $value !== '')
                                        <tr>
                                            <td style="padding:12px 18px;font-size:11.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;width:36%;vertical-align:top;white-space:nowrap;{{ $loop->last ? '' : 'border-bottom:1px solid #eef0f4;' }}">{{ $label }}</td>
                                            <td style="padding:12px 18px;font-size:14.5px;font-weight:650;color:#0f172a;vertical-align:top;{{ $loop->last ? '' : 'border-bottom:1px solid #eef0f4;' }}">{{ $value }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @endif

                {{-- Button --}}
                @if(!empty($button['url']))
                    <tr>
                        <td align="left" style="padding:26px 34px 6px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-radius:12px;background-color:{{ $t['grad1'] }};background-image:linear-gradient(135deg,{{ $t['grad1'] }},{{ $t['grad2'] }});">
                                        <a href="{{ $button['url'] }}" style="display:inline-block;padding:13px 28px;font-size:15px;font-weight:800;color:#ffffff;text-decoration:none;border-radius:12px;">{{ $button['text'] ?? 'Open' }} &rarr;</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                @endif

                @if(!empty($footnote))
                    <tr>
                        <td style="padding:22px 34px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #eef0f4;">
                                <tr><td style="padding-top:18px;font-size:13px;line-height:1.6;color:#94a3b8;">{{ $footnote }}</td></tr>
                            </table>
                        </td>
                    </tr>
                @endif

                <tr><td style="height:30px;line-height:30px;font-size:1px;">&nbsp;</td></tr>
            </table>
        </td>
    </tr>

    {{-- Footer --}}
    <tr>
        <td align="center" style="padding:22px 20px 0;">
            <p style="margin:0 0 6px;font-size:12.5px;color:#94a3b8;line-height:1.6;">এই ইমেইলটি {{ $appName }} থেকে স্বয়ংক্রিয়ভাবে পাঠানো হয়েছে। এতে সরাসরি রিপ্লাই করবেন না।</p>
            <p style="margin:0;font-size:12.5px;"><a href="{{ $appUrl }}" style="color:#6366f1;text-decoration:none;font-weight:600;">{{ $appUrl }}</a></p>
        </td>
    </tr>
    <tr><td style="height:24px;line-height:24px;font-size:1px;">&nbsp;</td></tr>
</table>

</td></tr>
</table>
</body>
</html>
MAIL_TPL_EOF;

if (! is_dir(dirname($path))) {
    mkdir(dirname($path), 0755, true);
}

if (file_exists($path)) {
    if (file_get_contents($path) === $content) {
        echo "skip   already notun design
";
        exit(0);
    }
    if (! file_exists($path . '.bak-v2')) {
        copy($path, $path . '.bak-v2');
    }
    echo "update resources/views/emails/notice.blade.php (backup: notice.blade.php.bak-v2)
";
} else {
    echo "create resources/views/emails/notice.blade.php
";
}

file_put_contents($path, $content . "
");

echo "
Shesh. Ekhon:
  php artisan view:clear
  php artisan mail:test freelancerirfan402@gmail.com
";
