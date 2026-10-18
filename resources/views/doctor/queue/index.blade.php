@extends('layouts.doctor')

@section('title', $isToday ? 'আজকের কিউ' : 'সিরিয়াল কিউ')

@section('content')
@php
    $tz = config('easydoc.timezone', 'Asia/Dhaka');
    $dateC = \Carbon\Carbon::parse($date, $tz);
    $dateLabel = $date === $today ? 'আজ' : ($date === $tomorrow ? 'আগামীকাল' : ($date === $yesterday ? 'গতকাল' : $dateC->format('d M Y')));
@endphp
<style>
    .page-head h1 { font-size: 1.4rem; font-weight: 800; color: #0f172a; margin: 0; }
    .page-head p { color: #64748b; font-size: .85rem; margin: .2rem 0 0; }
    .cardx { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .serial-no { width: 40px; height: 40px; border-radius: 12px; background: #eef2ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-weight: 800; }
    .count-box { border-radius: 12px; padding: .7rem .9rem; background: #fff; border: 1px solid #eef0f4; text-align: center; }
    .count-box .n { font-size: 1.4rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .count-box .l { font-size: .72rem; color: #64748b; font-weight: 600; }
    .now-card { background: linear-gradient(135deg,#4f46e5,#4338ca); color: #fff; border-radius: 16px; }
    .table > :not(caption) > * > * { vertical-align: middle; }
    .date-chip { display: inline-flex; align-items: center; gap: 6px; padding: .3rem .7rem; border-radius: 999px; font-size: .78rem; font-weight: 700; border: 1px solid #e2e8f0; background: #fff; color: #475569; text-decoration: none; }
    .date-chip:hover { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }
    .date-chip.active { background: #4f46e5; border-color: #4f46e5; color: #fff; }
    .date-chip .cnt { background: rgba(79,70,229,.12); color: #4338ca; border-radius: 999px; padding: 0 .45rem; font-size: .7rem; }
    .date-chip.active .cnt { background: rgba(255,255,255,.25); color: #fff; }
</style>

<div class="page-head mb-3 d-flex justify-content-between align-items-end flex-wrap gap-2">
    <div>
        <h1>{{ $isToday ? 'আজকের কিউ' : 'সিরিয়াল কিউ' }}
            <span class="text-muted fw-normal" style="font-size:1rem;">· {{ $dateLabel }} ({{ $dateC->format('d M Y') }})</span>
        </h1>
        <p>সহকারীর দেওয়া সিরিয়াল এখানে নিজে থেকে দেখা যাবে। তারিখ বদলে অন্য দিনের সিরিয়াল দেখুন।</p>
    </div>
    <a href="{{ route('doctor.assistants.index') }}" class="btn btn-outline-primary btn-sm fw-bold">
        <i class="fa-solid fa-user-group me-1"></i> সহকারী ব্যবস্থাপনা
    </a>
</div>

{{-- ============ তারিখ ফিল্টার ============ --}}
<form method="GET" action="{{ route('doctor.queue.index') }}" class="cardx p-3 mb-3 d-flex flex-wrap gap-2 align-items-center">
    <a href="{{ route('doctor.queue.index', ['date' => $prevDate]) }}" class="btn btn-outline-secondary btn-sm" title="আগের দিন"><i class="fa-solid fa-chevron-left"></i></a>
    <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" style="max-width:160px;" onchange="this.form.submit()">
    <a href="{{ route('doctor.queue.index', ['date' => $nextDate]) }}" class="btn btn-outline-secondary btn-sm" title="পরের দিন"><i class="fa-solid fa-chevron-right"></i></a>

    <div class="d-flex flex-wrap gap-1 ms-1">
        <a href="{{ route('doctor.queue.index', ['date' => $yesterday]) }}" class="date-chip {{ $date === $yesterday ? 'active' : '' }}">গতকাল</a>
        <a href="{{ route('doctor.queue.index') }}" class="date-chip {{ $date === $today ? 'active' : '' }}">আজ</a>
        <a href="{{ route('doctor.queue.index', ['date' => $tomorrow]) }}" class="date-chip {{ $date === $tomorrow ? 'active' : '' }}">আগামীকাল</a>
    </div>

    @if($upcoming->isNotEmpty())
        <div class="d-flex flex-wrap gap-1 align-items-center ms-lg-auto">
            <span class="small text-muted fw-semibold me-1">সিরিয়াল আছে:</span>
            @foreach($upcoming as $u)
                <a href="{{ route('doctor.queue.index', ['date' => $u['date']]) }}" class="date-chip {{ $date === $u['date'] ? 'active' : '' }}">
                    {{ \Carbon\Carbon::parse($u['date'])->format('d M') }} <span class="cnt">{{ $u['count'] }}</span>
                </a>
            @endforeach
        </div>
    @endif
</form>

@if(!$isToday)
    <div class="alert alert-info border-0 small py-2" style="border-radius:12px;">
        <i class="fa-solid fa-circle-info me-1"></i>
        @if($date > $today)
            এটি আগামী তারিখের কিউ। রোগী দেখা (Start) শুধু ঐ দিনেই করা যাবে।
        @else
            এটি আগের তারিখের কিউ, শুধু দেখার জন্য। অপেক্ষায় থাকা রোগী এলে সহকারীকে আজকের নতুন সিরিয়াল দিতে বলুন।
        @endif
    </div>
@endif

<div id="queueWrap">

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3"><div class="count-box"><div class="n">{{ $counts['total'] }}</div><div class="l">মোট সিরিয়াল</div></div></div>
        <div class="col-6 col-md-3"><div class="count-box"><div class="n">{{ $counts['waiting'] }}</div><div class="l">অপেক্ষায়</div></div></div>
        <div class="col-6 col-md-3"><div class="count-box"><div class="n">{{ $counts['in_progress'] }}</div><div class="l">চলছে</div></div></div>
        <div class="col-6 col-md-3"><div class="count-box"><div class="n">{{ $counts['done'] }}</div><div class="l">সম্পন্ন</div></div></div>
    </div>

    @php $focus = $current ?? $next; @endphp
    @if($isToday && $focus)
        <div class="now-card p-4 mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="small opacity-75 fw-semibold">{{ $current ? 'বর্তমানে চলছে' : 'পরবর্তী রোগী' }}</div>
                <div class="fs-4 fw-bold">#{{ $focus->serial_number }} · {{ $focus->patient->name }}</div>
                <div class="small opacity-75">
                    {{ $focus->patient->mobile }}@if($focus->patient->age) · {{ $focus->patient->age }} বছর @endif
                    @if($focus->reason) · {{ $focus->reason }} @endif
                </div>
            </div>
            <a href="{{ route('doctor.prescriptions.create', ['serial' => $focus->id]) }}" class="btn btn-light fw-bold px-4">
                <i class="fa-solid fa-file-medical me-1"></i> {{ $current ? 'Resume' : 'Start' }}
            </a>
        </div>
    @endif

    <div class="cardx p-3 p-lg-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr class="small text-muted text-uppercase">
                        <th>#</th><th>রোগী</th><th>সমস্যা / Vitals</th><th>সময়</th><th>অবস্থা</th><th class="text-end">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serials as $s)
                        @php
                            $vitals = collect([
                                $s->bp ? "BP {$s->bp}" : null,
                                $s->pulse ? "P {$s->pulse}" : null,
                                $s->temperature ? "T {$s->temperature}" : null,
                            ])->filter()->implode(' · ');
                        @endphp
                        <tr class="{{ $s->status === 'cancelled' ? 'opacity-50' : '' }}">
                            <td><div class="serial-no">{{ $s->serial_number }}</div></td>
                            <td>
                                <div class="fw-bold">{{ $s->patient->name }}</div>
                                <div class="small text-muted">{{ $s->patient->mobile }}@if($s->patient->age) · {{ $s->patient->age }} বছর @endif</div>
                            </td>
                            <td class="small">
                                {{ $s->reason ?: '—' }}
                                @if($vitals)<div class="text-muted">{{ $vitals }}</div>@endif
                            </td>
                            <td class="small text-muted">{{ $s->created_at->timezone($tz)->format('h:i A') }}</td>
                            <td><span class="badge rounded-pill {{ $s->badgeClass() }}">{{ $s->statusLabel() }}</span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @if($s->isOpen() && $isToday)
                                        <a href="{{ route('doctor.prescriptions.create', ['serial' => $s->id]) }}" class="btn btn-sm btn-primary">
                                            {{ $s->status === 'in_progress' ? 'Resume' : 'Start' }}
                                        </a>
                                    @elseif($s->isOpen())
                                        <span class="btn btn-sm btn-light disabled" title="শুধু আজকের সিরিয়ালে Start করা যায়">
                                            {{ $date > $today ? 'আগামী' : 'মেয়াদ শেষ' }}
                                        </span>
                                    @endif

                                    @if($s->status === 'waiting')
                                        <form method="POST" action="{{ route('doctor.queue.status', $s->id) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="skipped">
                                            <button class="btn btn-sm btn-outline-secondary">Skip</button>
                                        </form>
                                    @endif

                                    @if($s->status === 'skipped')
                                        <form method="POST" action="{{ route('doctor.queue.status', $s->id) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="waiting">
                                            <button class="btn btn-sm btn-outline-primary">আবার অপেক্ষায়</button>
                                        </form>
                                    @endif

                                    @if(in_array($s->status, ['waiting', 'skipped']))
                                        <form method="POST" action="{{ route('doctor.queue.status', $s->id) }}" onsubmit="return confirm('সিরিয়াল #{{ $s->serial_number }} বাতিল করবেন?');">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button class="btn btn-sm btn-outline-danger">বাতিল</button>
                                        </form>
                                    @endif

                                    @if($s->status === 'done' && $s->prescription_id)
                                        <a href="{{ route('doctor.prescriptions.print', $s->prescription_id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fa-solid fa-print"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">{{ $isToday ? 'আজ' : $dateC->format('d M Y') . ' তারিখে' }} এখনো কোনো সিরিয়াল নেই।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // ১৫ সেকেন্ড পরপর কিউ নিজে থেকে রিফ্রেশ (নির্বাচিত তারিখ ঠিক থাকে, কারণ URL-এ ?date আছে)
    setInterval(async () => {
        if (document.hidden) return;
        try {
            const res = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            const fresh = doc.getElementById('queueWrap');
            if (fresh) document.getElementById('queueWrap').innerHTML = fresh.innerHTML;
        } catch (e) {}
    }, 15000);
</script>
@endsection