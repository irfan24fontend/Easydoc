@extends('layouts.doctor')

@section('title', $patient->name . ' — Patient')

@push('styles')
<style>
    .glass-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(15,23,42,.04); }
    .pid-badge { display: inline-block; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-weight: 700; font-size: .78rem; padding: .2rem .55rem; border-radius: 6px; background: #eef2ff; color: #4338ca; border: 1px solid #e0e7ff; white-space: nowrap; }
    .avatar-lg { width: 64px; height: 64px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; flex-shrink: 0; }
    .av-male { background: #e0e7ff; color: #3730a3; }
    .av-female { background: #fce7f3; color: #9d174d; }
    .av-other { background: #e2e8f0; color: #334155; }

    .info-list { list-style: none; padding: 0; margin: 0; }
    .info-list li { display: flex; justify-content: space-between; gap: 12px; padding: .5rem 0; border-bottom: 1px dashed #eef0f4; font-size: .85rem; }
    .info-list li:last-child { border-bottom: 0; }
    .info-list .k { color: #64748b; font-weight: 600; }
    .info-list .v { color: #0f172a; font-weight: 600; text-align: right; }

    .mini-stat { background: #f8fafc; border: 1px solid #eef0f4; border-radius: 12px; padding: .7rem .5rem; text-align: center; }
    .mini-stat .n { font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .mini-stat .l { font-size: .68rem; color: #64748b; font-weight: 600; margin-top: 3px; }

    .vital-box { background: #f8fafc; border: 1px solid #eef0f4; border-radius: 12px; padding: .65rem .8rem; }
    .vital-box .k { font-size: .66rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; }
    .vital-box .v { font-size: 1rem; font-weight: 800; color: #0f172a; }

    .pending-card { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: .8rem 1rem; }

    .visit-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; margin-bottom: 1rem; overflow: hidden; }
    .visit-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: .8rem 1.1rem; background: #f8fafc; border-bottom: 1px solid #eef0f4; }
    .visit-body { padding: 1rem 1.1rem; }
    .vlabel { font-size: .66rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }
    .vtext { font-size: .86rem; color: #1e293b; white-space: pre-line; }
    .type-badge { font-size: .68rem; font-weight: 700; padding: .25rem .6rem; border-radius: 999px; white-space: nowrap; display: inline-block; }
    .type-full { background: #e0e7ff; color: #3730a3; }
    .type-inv { background: #ccfbf1; color: #115e59; }
    .state-pending { background: #fef3c7; color: #92400e; }
    .state-done { background: #dcfce7; color: #166534; }
    .med-row { display: flex; gap: 10px; align-items: baseline; padding: .35rem 0; border-bottom: 1px dashed #eef0f4; font-size: .84rem; }
    .med-row:last-child { border-bottom: 0; }
    .med-row .dose { font-weight: 700; color: #3730a3; background: #eef2ff; border-radius: 6px; padding: .05rem .45rem; font-size: .76rem; white-space: nowrap; }

    .btn-act { font-size: .72rem; font-weight: 700; padding: .38rem .7rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; border: 1px solid transparent; transition: all .15s; }
    .btn-act.view { color: #475569; background: #f1f5f9; border-color: #e2e8f0; }
    .btn-act.view:hover { background: #475569; color: #fff; }
    .btn-act.print { color: #4f46e5; background: #eeebff; border-color: #e0e7ff; }
    .btn-act.print:hover { background: #4f46e5; color: #fff; }
    .btn-act.edit { color: #b45309; background: #fef3c7; border-color: #fde68a; }
    .btn-act.edit:hover { background: #b45309; color: #fff; }
    .btn-act.cont { color: #fff; background: #f59e0b; }
    .btn-act.cont:hover { background: #d97706; color: #fff; }
    .chart-wrap { position: relative; height: 240px; }
</style>
@endpush

@section('content')
@php
    $tz = config('easydoc.timezone', 'Asia/Dhaka');
    $avClass = $patient->gender === 'Female' ? 'av-female' : ($patient->gender === 'Male' ? 'av-male' : 'av-other');
    $fmtKg = $patient->weight ? rtrim(rtrim(number_format((float) $patient->weight, 1), '0'), '.') . ' kg' : '—';
    $first = $stats['first'] ? \Carbon\Carbon::parse($stats['first']) : null;
    $last = $stats['last'] ? \Carbon\Carbon::parse($stats['last']) : null;
    $hasChart = count(array_filter($vitalsChart['sys'])) + count(array_filter($vitalsChart['pulse'])) + count(array_filter($vitalsChart['temp'])) >= 2
                && count($vitalsChart['labels']) >= 2;
@endphp

<div class="py-2">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <a href="{{ route('doctor.patients.index') }}" class="text-decoration-none small fw-semibold text-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Patient List
        </a>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('doctor.patients.edit', $patient) }}" class="btn btn-outline-secondary btn-sm fw-semibold"><i class="fa-solid fa-pen me-1"></i> তথ্য সম্পাদনা</a>
            <a href="{{ route('doctor.prescriptions.create', ['patient' => $patient->id]) }}" class="btn btn-primary btn-sm fw-bold px-3" style="background:#4f46e5;border-color:#4f46e5;">
                <i class="fa-solid fa-file-medical me-1"></i> নতুন Prescription
            </a>
        </div>
    </div>

    <div class="row g-4">

        {{-- ================= LEFT: Profile ================= --}}
        <div class="col-lg-4">
            <div class="glass-card p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar-lg {{ $avClass }}">{{ $patient->initials }}</div>
                    <div style="min-width:0;">
                        <h5 class="fw-bold mb-1 text-break">{{ $patient->name }}</h5>
                        <span class="pid-badge">{{ $patient->patient_code }}</span>
                    </div>
                </div>

                <ul class="info-list">
                    <li><span class="k"><i class="fa-solid fa-phone me-1"></i> মোবাইল</span><span class="v">{{ $patient->mobile }}</span></li>
                    <li><span class="k">বয়স</span><span class="v">{{ $patient->age !== null ? $patient->age . ' বছর' : '—' }}</span></li>
                    <li><span class="k">লিঙ্গ</span><span class="v">{{ $patient->gender ?: '—' }}</span></li>
                    <li><span class="k">ওজন</span><span class="v">{{ $fmtKg }}</span></li>
                    <li><span class="k">Blood Group</span><span class="v">{{ $patient->blood_group ?: '—' }}</span></li>
                    <li><span class="k">ঠিকানা</span><span class="v text-break">{{ $patient->address ?: '—' }}</span></li>
                    <li><span class="k">প্রথম নিবন্ধন</span><span class="v">{{ $patient->created_at?->timezone($tz)->format('d M Y') }}</span></li>
                </ul>

                <div class="row g-2 mt-3">
                    <div class="col-4"><div class="mini-stat"><div class="n">{{ $stats['visits'] }}</div><div class="l">মোট ভিজিট</div></div></div>
                    <div class="col-4"><div class="mini-stat"><div class="n">{{ $stats['full'] }}</div><div class="l">Prescription</div></div></div>
                    <div class="col-4"><div class="mini-stat"><div class="n">{{ $stats['investigation'] }}</div><div class="l">Investigation</div></div></div>
                </div>

                <div class="small text-muted mt-3">
                    @if($first)প্রথম ভিজিট: <b class="text-dark">{{ $first->format('d M Y') }}</b><br>@endif
                    @if($last)শেষ ভিজিট: <b class="text-dark">{{ $last->format('d M Y') }}</b> ({{ $last->diffForHumans() }})@else এখনো কোনো ভিজিট নেই।@endif
                </div>
            </div>

            @if($nextFollowup)
                <div class="glass-card p-3 mb-4 d-flex align-items-center gap-3">
                    <div class="avatar-lg" style="width:44px;height:44px;border-radius:12px;background:#dcfce7;color:#16a34a;font-size:1rem;"><i class="fa-solid fa-calendar-check"></i></div>
                    <div>
                        <div class="small text-muted fw-semibold">পরবর্তী Follow-up</div>
                        <div class="fw-bold">{{ $nextFollowup->followup_date->format('d M Y') }}
                            <span class="text-muted fw-normal small">({{ $nextFollowup->followup_date->isToday() ? 'আজ' : $nextFollowup->followup_date->diffForHumans() }})</span>
                        </div>
                    </div>
                </div>
            @endif

            @if($lastVitals)
                <div class="glass-card p-3 mb-4">
                    <div class="small fw-bold text-secondary text-uppercase mb-2">
                        সর্বশেষ Vitals <span class="fw-normal text-muted">· {{ $lastVitals->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-4"><div class="vital-box"><div class="k">BP</div><div class="v">{{ $lastVitals->bp ?: '—' }}</div></div></div>
                        <div class="col-4"><div class="vital-box"><div class="k">Pulse</div><div class="v">{{ $lastVitals->pulse ?: '—' }}</div></div></div>
                        <div class="col-4"><div class="vital-box"><div class="k">Temp °F</div><div class="v">{{ $lastVitals->temperature ?: '—' }}</div></div></div>
                    </div>
                </div>
            @endif

            @if($serials->isNotEmpty())
                <div class="glass-card p-3">
                    <div class="small fw-bold text-secondary text-uppercase mb-2"><i class="fa-solid fa-ticket me-1"></i> সিরিয়াল ইতিহাস</div>
                    @foreach($serials as $s)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom small" style="border-color:#eef0f4 !important;">
                            <div>
                                <b>#{{ $s->serial_number }}</b>
                                <span class="text-muted ms-1">{{ $s->serial_date->format('d M Y') }}</span>
                                @if($s->reason)<div class="text-muted text-truncate" style="max-width:180px;">{{ $s->reason }}</div>@endif
                            </div>
                            <span class="badge rounded-pill {{ $s->badgeClass() }}">{{ $s->statusLabel() }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ================= RIGHT: History ================= --}}
        <div class="col-lg-8">

            @foreach($pending as $pp)
                <div class="pending-card mb-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div style="min-width:0;">
                        <div class="fw-bold" style="color:#92400e;"><i class="fa-solid fa-hourglass-half me-1"></i> Report-এর অপেক্ষায় · {{ $pp->created_at->format('d M Y') }}</div>
                        <div class="small text-truncate" style="color:#92400e;max-width:520px;">{{ $pp->investigations }}</div>
                    </div>
                    <a href="{{ route('doctor.prescriptions.create', ['from' => $pp->id]) }}" class="btn-act cont"><i class="fa-solid fa-flask-vial"></i> Continue with Reports</a>
                </div>
            @endforeach

            @if($hasChart)
                <div class="glass-card p-3 p-lg-4 mb-4">
                    <div class="fw-bold mb-2"><i class="fa-solid fa-heart-pulse text-danger me-1"></i> Vitals-এর ধারা <span class="small text-muted fw-normal">(শেষ {{ count($vitalsChart['labels']) }}টি ভিজিট)</span></div>
                    <div class="chart-wrap"><canvas id="vitalsChart"></canvas></div>
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i> ভিজিটের ইতিহাস</h6>
                <span class="small text-muted">মোট {{ $prescriptions->total() }}টি</span>
            </div>

            @forelse($prescriptions as $rx)
                @php
                    $isInv = $rx->type === 'investigation_only';
                    $awaiting = $isInv && $rx->children->isEmpty();
                @endphp
                <div class="visit-card">
                    <div class="visit-head">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <b>{{ $rx->created_at->timezone($tz)->format('d M Y') }}</b>
                            <span class="small text-muted">{{ $rx->created_at->timezone($tz)->format('h:i A') }}</span>
                            <span class="type-badge {{ $isInv ? 'type-inv' : 'type-full' }}">{{ $isInv ? 'Investigation Advice' : 'Full Prescription' }}</span>
                            @if($isInv)
                                <span class="type-badge {{ $awaiting ? 'state-pending' : 'state-done' }}">{{ $awaiting ? 'Report-এর অপেক্ষায়' : 'Report দেখা হয়েছে' }}</span>
                            @endif
                            @if($rx->chamber)<span class="small text-muted"><i class="fa-solid fa-hospital me-1"></i>{{ $rx->chamber->chamber_name }}</span>@endif
                        </div>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('doctor.prescriptions.show', $rx->id) }}" class="btn-act view"><i class="fa-solid fa-eye"></i> View</a>
                            <a href="{{ route('doctor.prescriptions.print', $rx->id) }}" class="btn-act print"><i class="fa-solid fa-print"></i> Print</a>
                            <a href="{{ route('doctor.prescriptions.edit', $rx->id) }}" class="btn-act edit"><i class="fa-solid fa-pen"></i> Edit</a>
                            @if($awaiting)
                                <a href="{{ route('doctor.prescriptions.create', ['from' => $rx->id]) }}" class="btn-act cont"><i class="fa-solid fa-flask-vial"></i> Continue</a>
                            @endif
                        </div>
                    </div>

                    <div class="visit-body">
                        <div class="row g-3">
                            @if($rx->bp || $rx->pulse || $rx->temperature)
                                <div class="col-12">
                                    <div class="vlabel">Vitals</div>
                                    <div class="vtext">
                                        @if($rx->bp)BP: <b>{{ $rx->bp }}</b>@endif
                                        @if($rx->pulse) &nbsp;·&nbsp; Pulse: <b>{{ $rx->pulse }}</b>@endif
                                        @if($rx->temperature) &nbsp;·&nbsp; Temp: <b>{{ $rx->temperature }}°F</b>@endif
                                    </div>
                                </div>
                            @endif
                            @if($rx->clinical_complaints)
                                <div class="col-md-6"><div class="vlabel">Complaints</div><div class="vtext">{{ $rx->clinical_complaints }}</div></div>
                            @endif
                            @if($rx->diagnosis)
                                <div class="col-md-6"><div class="vlabel">Diagnosis</div><div class="vtext fw-semibold">{{ $rx->diagnosis }}</div></div>
                            @endif
                            @if($rx->risk_factors)
                                <div class="col-md-6"><div class="vlabel">Risk Factors</div><div class="vtext">{{ $rx->risk_factors }}</div></div>
                            @endif
                            @if($rx->investigations)
                                <div class="col-md-6"><div class="vlabel">Investigations</div><div class="vtext">{{ $rx->investigations }}</div></div>
                            @endif
                            @if($rx->investigation_findings)
                                <div class="col-12"><div class="vlabel">Findings</div><div class="vtext">{{ $rx->investigation_findings }}</div></div>
                            @endif

                            @if($rx->medicines->isNotEmpty())
                                <div class="col-12">
                                    <div class="vlabel">ঔষধ ({{ $rx->medicines->count() }})</div>
                                    @foreach($rx->medicines as $pm)
                                        <div class="med-row">
                                            <span class="fw-semibold">💊 {{ $pm->displayName() }}</span>
                                            <span class="dose">{{ $pm->doseLabel() }}</span>
                                            <span class="text-muted">{{ $pm->duration }}@if($pm->instruction) · {{ $pm->instruction }}@endif</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($rx->advice)
                                <div class="col-md-8"><div class="vlabel">Advice</div><div class="vtext">{{ $rx->advice }}</div></div>
                            @endif
                            @if($rx->followup_date)
                                <div class="col-md-4"><div class="vlabel">Follow-up</div><div class="vtext fw-semibold">{{ $rx->followup_date->format('d M Y') }}</div></div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="glass-card text-center text-muted py-5">
                    <i class="fa-solid fa-file-circle-xmark d-block mb-2" style="font-size:2rem;color:#cbd5e1;"></i>
                    এই রোগীর এখনো কোনো প্রেসক্রিপশন নেই।
                    <div class="mt-3">
                        <a href="{{ route('doctor.prescriptions.create', ['patient' => $patient->id]) }}" class="btn btn-primary btn-sm fw-bold" style="background:#4f46e5;border-color:#4f46e5;">
                            <i class="fa-solid fa-file-medical me-1"></i> প্রথম Prescription লিখুন
                        </a>
                    </div>
                </div>
            @endforelse

            @if($prescriptions->hasPages())
                <div class="mt-3">{{ $prescriptions->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@if($hasChart)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const d = @json($vitalsChart);
        const ds = (label, data, color, axis, dash) => ({
            label, data, borderColor: color, backgroundColor: color, yAxisID: axis, tension: .3,
            borderWidth: 2, pointRadius: 3, spanGaps: true, borderDash: dash || [],
        });

        const sets = [];
        if (d.sys.some(v => v !== null)) { sets.push(ds('BP (Sys)', d.sys, '#dc2626', 'y')); sets.push(ds('BP (Dia)', d.dia, '#f97316', 'y')); }
        if (d.pulse.some(v => v !== null)) sets.push(ds('Pulse', d.pulse, '#4f46e5', 'y'));
        if (d.temp.some(v => v !== null)) sets.push(ds('Temp °F', d.temp, '#0d9488', 'y2', [5, 4]));

        const hasTemp = d.temp.some(v => v !== null);

        new Chart(document.getElementById('vitalsChart'), {
            type: 'line',
            data: { labels: d.labels, datasets: sets },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
                scales: {
                    y: { beginAtZero: false, grid: { color: '#f1f5f9' } },
                    y2: { display: hasTemp, position: 'right', grid: { drawOnChartArea: false }, suggestedMin: 96, suggestedMax: 104 },
                    x: { grid: { display: false } },
                },
            },
        });
    })();
</script>
@endpush
@endif