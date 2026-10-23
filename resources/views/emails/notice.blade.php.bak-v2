@php
    $tones = [
        'success' => ['#15803d', '#dcfce7'],
        'warning' => ['#b45309', '#fef3c7'],
        'danger'  => ['#b91c1c', '#fee2e2'],
        'info'    => ['#4338ca', '#e0e7ff'],
    ];
    [$fg, $bg] = $tones[$tone ?? 'info'] ?? $tones['info'];

    $appName  = config('app.name', 'EasyDoc');
    $lines    = $lines ?? [];
    $details  = $details ?? [];
    $button   = $button ?? null;
    $appUrl   = $appUrl ?? config('app.url');
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $title ?? $appName }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Roboto,Helvetica,Arial,'Noto Sans Bengali',sans-serif;color:#0f172a;">

    {{-- inbox preview text --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $preheader ?? ($title ?? '') }}</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">

                    {{-- Card --}}
                    <tr>
                        <td style="background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid #e5e9f0;">

                            {{-- Header --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background:#4f46e5;background-image:linear-gradient(135deg,#4f46e5 0%,#6d28d9 60%,#7c3aed 100%);padding:26px 30px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size:22px;font-weight:800;letter-spacing:-0.02em;color:#ffffff;">
                                                    <span style="display:inline-block;width:34px;height:34px;line-height:34px;text-align:center;border-radius:10px;background:rgba(255,255,255,0.18);font-size:16px;margin-right:8px;vertical-align:middle;">&#9877;</span>
                                                    <span style="vertical-align:middle;">{{ $appName }}</span>
                                                </td>
                                                @if(!empty($badge))
                                                    <td align="right">
                                                        <span style="display:inline-block;background:{{ $bg }};color:{{ $fg }};font-size:11px;font-weight:800;letter-spacing:.04em;padding:5px 12px;border-radius:999px;">{{ $badge }}</span>
                                                    </td>
                                                @endif
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Body --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:30px 30px 8px;">
                                        @if(!empty($title))
                                            <h1 style="margin:0 0 14px;font-size:21px;line-height:1.3;font-weight:800;color:#0f172a;">{{ $title }}</h1>
                                        @endif

                                        @if(!empty($greeting))
                                            <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#334155;">{{ $greeting }}</p>
                                        @endif

                                        @foreach($lines as $line)
                                            <p style="margin:0 0 12px;font-size:15px;line-height:1.65;color:#475569;">{{ $line }}</p>
                                        @endforeach
                                    </td>
                                </tr>

                                {{-- Details --}}
                                @if(!empty($details))
                                    <tr>
                                        <td style="padding:6px 30px 8px;">
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e8ebf1;border-radius:14px;">
                                                @foreach($details as $label => $value)
                                                    @if($value !== null && $value !== '')
                                                        <tr>
                                                            <td style="padding:11px 16px;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em;width:38%;vertical-align:top;{{ $loop->last ? '' : 'border-bottom:1px solid #eef0f4;' }}">{{ $label }}</td>
                                                            <td style="padding:11px 16px;font-size:14px;font-weight:600;color:#0f172a;vertical-align:top;{{ $loop->last ? '' : 'border-bottom:1px solid #eef0f4;' }}">{{ $value }}</td>
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
                                        <td align="center" style="padding:22px 30px 8px;">
                                            <a href="{{ $button['url'] }}"
                                               style="display:inline-block;background:#4f46e5;background-image:linear-gradient(135deg,#4f46e5,#7c3aed);color:#ffffff;text-decoration:none;font-size:15px;font-weight:800;padding:13px 30px;border-radius:12px;">{{ $button['text'] ?? 'Open' }}</a>
                                        </td>
                                    </tr>
                                @endif

                                @if(!empty($footnote))
                                    <tr>
                                        <td style="padding:18px 30px 4px;">
                                            <p style="margin:0;font-size:13px;line-height:1.6;color:#94a3b8;">{{ $footnote }}</p>
                                        </td>
                                    </tr>
                                @endif

                                <tr><td style="height:26px;line-height:26px;font-size:1px;">&nbsp;</td></tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:18px 10px 0;">
                            <p style="margin:0 0 4px;font-size:12px;color:#94a3b8;">এই ইমেইলটি {{ $appName }} থেকে স্বয়ংক্রিয়ভাবে পাঠানো হয়েছে। এতে সরাসরি রিপ্লাই করবেন না।</p>
                            <p style="margin:0;font-size:12px;color:#94a3b8;"><a href="{{ $appUrl }}" style="color:#6366f1;text-decoration:none;">{{ $appUrl }}</a></p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
