@extends('layouts.doctor')

@section('title', 'Dashboard')

@section('content')
<style>
    .page-head h1 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
    .page-head p { color: #64748b; font-size: 0.875rem; margin: 0.25rem 0 0; }

    .stat-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; padding: 1.3rem; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .stat-num { font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1; margin-top: 0.6rem; }
    .stat-label { font-size: 0.78rem; color: #64748b; font-weight: 600; margin-top: 4px; }

    .panel-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; padding: 1.4rem; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .panel-card h6 { font-weight: 800; color: #0f172a; font-size: 0.9rem; margin-bottom: 1rem; }

    .quick-action {
        display: flex; align-items: center; gap: 12px; padding: 0.9rem 1.1rem; border: 1px solid #eef0f4;
        border-radius: 12px; text-decoration: none; color: #0f172a; font-weight: 700; font-size: 0.85rem; margin-bottom: 0.6rem;
    }
    .quick-action:hover { border-color: #4f46e5; background: #fafbff; color: #0f172a; }
    .quick-action i { width: 36px; height: 36px; border-radius: 9px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; }
    .quick-action.disabled { opacity: .5; pointer-events: none; }

    .chart-wrap { position: relative; height: 280px; }

    .q-count { border-radius: 12px; padding: .55rem .8rem; background: #f8fafc; border: 1px solid #eef0f4; text-align: center; }
    .q-count .n { font-size: 1.2rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .q-count .l { font-size: .7rem; color: #64748b; font-weight: 600; }
    .q-row { display: flex; align-items: center; gap: 12px; padding: .7rem .9rem; border: 1px solid #eef0f4; border-radius: 12px; margin-bottom: .55rem; background: #fff; }
    .q-row.live { border-color: #4f46e5; background: #fafaff; box-shadow: 0 0 0 3px rgba(79,70,229,.08); }
    .q-no { width: 38px; height: 38px; border-radius: 10px; background: #eef2ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0; }
    .q-row.live .q-no { background: #4f46e5; color: #fff; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 page-head mb-4">
    <div>
        <h1>স্বাগতম, Dr. {{ auth()->user()->name }} 👋</h1>
        <p>আপনার আজকের প্র্যাকটিস ওভারভিউ এখানে দেখুন।</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fa-solid fa-wallet"></i></div>
            <div class="stat-num">৳{{ number_format($stats['wallet_balance'], 2) }}</div>
            <div class="stat-label">Wallet ব্যালেন্স</div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef2ff; color:#4f46e5;"><i class="fa-solid fa-hospital"></i></div>
            <div class="stat-num">{{ $stats['total_chambers'] }}</div>
            <div class="stat-label">মোট চেম্বার</div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7; color:#16a34a;"><i class="fa-solid fa-circle-check"></i></div>
            <div class="stat-num" style="font-size:1.1rem;">{{ $stats['active_chamber']->chamber_name ?? 'None' }}</div>
            <div class="stat-label">Active চেম্বার</div>
        </div>
    </div>
</div>

{{-- ============ আজকের কিউ (Start সরাসরি এখান থেকে) ============ --}}
<div class="panel-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h6 class="mb-0"><i class="fa-solid fa-ticket text-primary me-1"></i> আজকের সিরিয়াল
            <span class="text-muted fw-normal small ms-1">{{ now(config('easydoc.timezone', 'Asia/Dhaka'))->format('d M Y') }}</span>
        </h6>
        <a href="{{ route('doctor.queue.index') }}" class="btn btn-outline-primary btn-sm fw-bold">
            <i class="fa-solid fa-calendar-days me-1"></i> পুরো কিউ ও তারিখ অনুযায়ী দেখুন
        </a>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-3"><div class="q-count"><div class="n">{{ $queueCounts['total'] }}</div><div class="l">মোট</div></div></div>
        <div class="col-3"><div class="q-count"><div class="n">{{ $queueCounts['waiting'] }}</div><div class="l">অপেক্ষায়</div></div></div>
        <div class="col-3"><div class="q-count"><div class="n">{{ $queueCounts['in_progress'] }}</div><div class="l">চলছে</div></div></div>
        <div class="col-3"><div class="q-count"><div class="n">{{ $queueCounts['done'] }}</div><div class="l">সম্পন্ন</div></div></div>
    </div>

    @forelse($queueSerials as $s)
        @php
            $vitals = collect([
                $s->bp ? "BP {$s->bp}" : null,
                $s->pulse ? "P {$s->pulse}" : null,
                $s->temperature ? "T {$s->temperature}" : null,
            ])->filter()->implode(' · ');
        @endphp
        <div class="q-row {{ $s->status === 'in_progress' ? 'live' : '' }}">
            <div class="q-no">{{ $s->serial_number }}</div>
            <div class="flex-grow-1" style="min-width:0;">
                <div class="fw-bold text-truncate">{{ $s->patient->name }}</div>
                <div class="small text-muted text-truncate">
                    {{ $s->patient->mobile }}@if($s->patient->age) · {{ $s->patient->age }} বছর @endif
                    @if($s->reason) · {{ $s->reason }} @endif
                </div>
                @if($vitals)<div class="small" style="color:#0f766e;font-weight:600;">{{ $vitals }}</div>@endif
            </div>
            <span class="badge rounded-pill {{ $s->badgeClass() }} d-none d-sm-inline">{{ $s->statusLabel() }}</span>
            <a href="{{ route('doctor.prescriptions.create', ['serial' => $s->id]) }}" class="btn btn-primary btn-sm fw-bold px-3">
                <i class="fa-solid fa-play me-1"></i> {{ $s->status === 'in_progress' ? 'Resume' : 'Start' }}
            </a>
        </div>
    @empty
        <div class="text-center text-muted py-4 small">
            <i class="fa-regular fa-calendar-check d-block mb-1" style="font-size:1.5rem;"></i>
            আজ কোনো অপেক্ষমাণ সিরিয়াল নেই।
        </div>
    @endforelse
</div>

<div class="panel-card mb-4">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <h6 class="mb-0"><i class="fa-solid fa-chart-line text-primary me-1"></i> রোগী ট্রেন্ড (গত ১৪ দিন)</h6>
        <span class="small text-secondary">প্রতিদিন কতজন ভিন্ন রোগী দেখা হয়েছে</span>
    </div>
    <div class="chart-wrap">
        <canvas id="patientTrendChart"></canvas>
    </div>
</div>

<div class="panel-card">
    <h6>Quick Actions</h6>
    <a href="{{ route('doctor.prescriptions.create') }}" class="quick-action">
        <i class="fa-solid fa-file-medical"></i> নতুন প্রেসক্রিপশন লিখুন
    </a>
    <a href="{{ route('doctor.prescriptions.index') }}" class="quick-action">
        <i class="fa-solid fa-list-ul"></i> প্রেসক্রিপশন লিস্ট দেখুন
    </a>
</div>

<script>
    const patientChartData = @json($patientChart);

    new Chart(document.getElementById('patientTrendChart'), {
        type: 'line',
        data: {
            labels: patientChartData.labels,
            datasets: [{
                label: 'রোগী সংখ্যা',
                data: patientChartData.data,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79,70,229,0.08)',
                pointBackgroundColor: '#4f46e5',
                pointRadius: 3,
                tension: 0.35,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#f1f5f9' },
                },
                x: {
                    grid: { display: false },
                }
            }
        }
    });
</script>
@endsection