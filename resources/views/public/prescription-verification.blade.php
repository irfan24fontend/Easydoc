<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="referrer" content="no-referrer">
    <title>Prescription verification | EasyDoc</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f1f5f9; color: #0f172a; font: 15px/1.55 system-ui, -apple-system, "Segoe UI", sans-serif; }
        .wrap { width: min(760px, calc(100% - 28px)); margin: 32px auto; }
        .brand { color: #0f766e; font-weight: 800; letter-spacing: .02em; margin: 0 0 14px; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 18px; box-shadow: 0 12px 36px #0f172a12; overflow: hidden; }
        .status { padding: 24px; background: #ecfdf5; border-bottom: 1px solid #bbf7d0; }
        .status.invalid { background: #fff7ed; border-color: #fed7aa; }
        .status h1 { margin: 0 0 5px; font-size: clamp(21px, 5vw, 28px); }
        .status p { margin: 0; color: #475569; }
        .content { padding: 22px 24px 26px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .field { min-width: 0; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
        .label { display: block; margin-bottom: 3px; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .value { overflow-wrap: anywhere; font-weight: 600; }
        .section { margin-top: 22px; }
        .section h2 { margin: 0 0 10px; font-size: 17px; }
        .text { padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 12px; white-space: normal; overflow-wrap: anywhere; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 9px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: top; }
        th { color: #475569; background: #f8fafc; }
        .foot { padding: 0 24px 22px; color: #64748b; font-size: 12px; }
        @media (max-width: 560px) { .wrap { margin: 18px auto; } .content { padding: 16px; } .status { padding: 20px 16px; } .grid { grid-template-columns: 1fr; gap: 9px; } .foot { padding: 0 16px 18px; } }
    </style>
</head>
<body>
<main class="wrap">
    <p class="brand">EasyDoc · Prescription verification</p>
    <article class="card">
        @if($prescription)
            @php($doctor = $prescription->doctor)
            <header class="status">
                <h1>✓ Verified prescription record</h1>
                <p>This prescription matches a record stored in EasyDoc. Check the details below against the printed copy.</p>
            </header>
            <div class="content">
                <div class="grid">
                    <div class="field"><span class="label">Prescription ID</span><span class="value">#{{ $prescription->id }}</span></div>
                    <div class="field"><span class="label">Issued</span><span class="value">{{ $prescription->created_at?->format('d M Y') }}</span></div>
                    <div class="field"><span class="label">Patient</span><span class="value">{{ $prescription->patient?->name ?? '—' }}</span></div>
                    <div class="field"><span class="label">Patient ID</span><span class="value">{{ $prescription->patient?->patient_code ?? '—' }}</span></div>
                    <div class="field"><span class="label">Age / Gender</span><span class="value">{{ $prescription->patient?->age ?? '—' }} / {{ $prescription->patient?->gender ?? '—' }}</span></div>
                    <div class="field"><span class="label">Weight / Blood group</span><span class="value">{{ $prescription->patient?->weight ? $prescription->patient->weight . ' kg' : '—' }} / {{ $prescription->patient?->blood_group ?? '—' }}</span></div>
                    <div class="field"><span class="label">Doctor</span><span class="value">{{ $doctor?->user?->name ?? '—' }}</span></div>
                    <div class="field"><span class="label">BMDC</span><span class="value">{{ $doctor?->bmdc_number ?? '—' }}</span></div>
                    <div class="field"><span class="label">Chamber</span><span class="value">{{ $prescription->chamber?->chamber_name ?? '—' }}</span></div>
                    <div class="field"><span class="label">Prescription type</span><span class="value">{{ $prescription->isInvestigationOnly() ? 'Investigation advice' : 'Full prescription' }}</span></div>
                    <div class="field"><span class="label">BP / Pulse / Temperature</span><span class="value">{{ $prescription->bp ?? '—' }} / {{ $prescription->pulse ?? '—' }} / {{ $prescription->temperature ?? '—' }}</span></div>
                    <div class="field"><span class="label">Follow-up</span><span class="value">{{ $prescription->followup_date?->format('d M Y') ?? '—' }}</span></div>
                </div>

                @foreach([
                    'Clinical complaints' => $prescription->clinical_complaints,
                    'Diagnosis' => $prescription->diagnosis,
                    'Investigations' => $prescription->investigations,
                    'Investigation findings' => $prescription->investigation_findings,
                    'Advice' => $prescription->advice,
                ] as $heading => $value)
                    @if(filled($value))
                        <section class="section"><h2>{{ $heading }}</h2><div class="text">{!! nl2br(e($value)) !!}</div></section>
                    @endif
                @endforeach

                @if($prescription->medicines->isNotEmpty())
                    <section class="section">
                        <h2>Medicines</h2>
                        <div class="text" style="padding:0;overflow-x:auto">
                            <table>
                                <thead><tr><th>#</th><th>Medicine</th><th>Dose</th><th>Duration</th><th>Instruction</th></tr></thead>
                                <tbody>
                                @foreach($prescription->medicines as $index => $medicine)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $medicine->displayName() }}</td>
                                        <td>{{ $medicine->doseLabel() }}</td>
                                        <td>{{ $medicine->duration }}</td>
                                        <td>{{ $medicine->instruction }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </div>
            <footer class="foot">A prescription updated after printing gets a new verification code. If any detail differs from the paper copy, contact the issuing chamber.</footer>
        @else
            <header class="status invalid">
                <h1>Could not verify this prescription</h1>
                <p>No matching prescription record was found. Do not rely on this copy until the issuing chamber confirms it.</p>
            </header>
        @endif
    </article>
</main>
</body>
</html>
