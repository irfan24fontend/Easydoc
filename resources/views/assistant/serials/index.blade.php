@extends('layouts.assistant')

@section('title', 'সিরিয়াল')

@push('styles')
<style>
    .serial-no {
        width: 40px; height: 40px; border-radius: 12px; background: #eef2ff; color: #4338ca;
        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.05rem;
    }
    .count-box { border-radius: 12px; padding: .6rem .9rem; background: #f8fafc; border: 1px solid #eef0f4; text-align: center; }
    .count-box .n { font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .count-box .l { font-size: .7rem; color: #64748b; font-weight: 600; }
    .field-label { font-size: .72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .03em; }
    .table > :not(caption) > * > * { vertical-align: middle; }
</style>
@endpush

@section('content')
<div class="row g-4">

    {{-- ============ নতুন সিরিয়াল ============ --}}
    <div class="col-lg-5">
        <div class="cardx p-4">
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-ticket text-primary me-2"></i>নতুন সিরিয়াল</h5>
            <p class="text-muted small mb-3">রোগীর মোবাইল নাম্বার দিয়ে খুঁজুন। পুরনো রোগী হলে তথ্য নিজে থেকে বসবে।</p>

            <form method="POST" action="{{ route('assistant.serials.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="field-label">সিরিয়ালের তারিখ *</label>
                    <input type="date" name="serial_date" id="serialDate" value="{{ old('serial_date', $date < $today ? $today : $date) }}"
                           min="{{ $today }}" max="{{ $maxDate }}" class="form-control" required>
                    <div class="small text-muted mt-1">আজ বা সামনের {{ \App\Services\SerialService::MAX_ADVANCE_DAYS }} দিনের মধ্যে যেকোনো তারিখ। নাম্বার প্রতিটি তারিখে আলাদা (১ থেকে) হয়।</div>
                </div>

                <div class="mb-3">
                    <label class="field-label">মোবাইল নাম্বার *</label>
                    <div class="input-group">
                        <input type="text" name="patient_mobile" id="mobile" value="{{ old('patient_mobile') }}" class="form-control" placeholder="01XXXXXXXXX" required>
                        <button type="button" class="btn btn-outline-primary" id="lookupBtn"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                    <div id="lookupStatus" class="small mt-1"></div>
                </div>

                <div class="mb-3">
                    <label class="field-label">রোগীর নাম *</label>
                    <input type="text" name="patient_name" id="pname" value="{{ old('patient_name') }}" class="form-control" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="field-label">বয়স</label>
                        <input type="number" name="patient_age" id="page" value="{{ old('patient_age') }}" class="form-control">
                    </div>
                    <div class="col-4">
                        <label class="field-label">লিঙ্গ</label>
                        <select name="patient_gender" id="pgender" class="form-select">
                            <option value="">--</option>
                            <option value="Male" {{ old('patient_gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('patient_gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('patient_gender') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="field-label">ওজন (kg)</label>
                        <input type="number" step="0.1" name="patient_weight" id="pweight" value="{{ old('patient_weight') }}" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="field-label">সমস্যা (সংক্ষেপে)</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" class="form-control" placeholder="যেমন: জ্বর ৩ দিন">
                </div>

                <a class="small fw-bold text-decoration-none" data-bs-toggle="collapse" href="#vitalsBox">
                    <i class="fa-solid fa-heart-pulse me-1"></i> Vitals যোগ করুন (ঐচ্ছিক — চাইলে বাদ দিন)
                </a>
                <div class="collapse {{ old('bp') || old('pulse') || old('temperature') ? 'show' : '' }} mt-2" id="vitalsBox">
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="field-label">BP</label>
                            <input type="text" name="bp" value="{{ old('bp') }}" class="form-control" placeholder="120/80">
                        </div>
                        <div class="col-4">
                            <label class="field-label">Pulse</label>
                            <input type="text" name="pulse" value="{{ old('pulse') }}" class="form-control" placeholder="72">
                        </div>
                        <div class="col-4">
                            <label class="field-label">Temp (°F)</label>
                            <input type="text" name="temperature" value="{{ old('temperature') }}" class="form-control" placeholder="98.6">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-2">
                    <i class="fa-solid fa-plus me-1"></i> সিরিয়াল দিন
                </button>
            </form>
        </div>
    </div>

    {{-- ============ আজকের কিউ (এই অংশটা নিজে থেকে রিফ্রেশ হয়) ============ --}}
    <div class="col-lg-7">

        {{-- ============ তারিখ ফিল্টার ============ --}}
        <form method="GET" action="{{ route('assistant.serials.index') }}" class="cardx p-3 mb-3 d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('assistant.serials.index', ['date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm" title="আগের দিন"><i class="fa-solid fa-chevron-left"></i></a>
            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" style="max-width:160px;" onchange="this.form.submit()">
            <a href="{{ route('assistant.serials.index', ['date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm" title="পরের দিন"><i class="fa-solid fa-chevron-right"></i></a>
            <a href="{{ route('assistant.serials.index') }}" class="btn btn-sm {{ $isToday ? 'btn-primary' : 'btn-outline-primary' }} fw-bold">আজ</a>

            @if($upcoming->isNotEmpty())
                <div class="d-flex flex-wrap gap-1 align-items-center ms-lg-auto">
                    <span class="small text-muted fw-semibold me-1">সিরিয়াল আছে:</span>
                    @foreach($upcoming as $u)
                        <a href="{{ route('assistant.serials.index', ['date' => $u['date']]) }}"
                           class="btn btn-sm {{ $date === $u['date'] ? 'btn-primary' : 'btn-outline-secondary' }} py-0 px-2" style="font-size:.75rem;">
                            {{ \Carbon\Carbon::parse($u['date'])->format('d M') }} <span class="badge text-bg-light">{{ $u['count'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </form>

        <div id="queueWrap">
            <div class="cardx p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-ol text-primary me-2"></i>{{ $isToday ? 'আজকের কিউ' : 'কিউ' }}
                        <small class="text-muted fw-normal ms-1">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</small>
                    </h5>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-3"><div class="count-box"><div class="n">{{ $counts['total'] }}</div><div class="l">মোট</div></div></div>
                    <div class="col-3"><div class="count-box"><div class="n">{{ $counts['waiting'] }}</div><div class="l">অপেক্ষায়</div></div></div>
                    <div class="col-3"><div class="count-box"><div class="n">{{ $counts['in_progress'] }}</div><div class="l">চলছে</div></div></div>
                    <div class="col-3"><div class="count-box"><div class="n">{{ $counts['done'] }}</div><div class="l">সম্পন্ন</div></div></div>
                </div>

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
                                    <td class="small text-muted">{{ $s->created_at->timezone(config('easydoc.timezone'))->format('h:i A') }}</td>
                                    <td><span class="badge rounded-pill {{ $s->badgeClass() }}">{{ $s->statusLabel() }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            @if($s->status === 'waiting')
                                                <form method="POST" action="{{ route('assistant.serials.status', $s->id) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="skipped">
                                                    <button class="btn btn-sm btn-outline-secondary" title="পরে ডাকুন">Skip</button>
                                                </form>
                                            @endif
                                            @if($s->status === 'skipped')
                                                <form method="POST" action="{{ route('assistant.serials.status', $s->id) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="waiting">
                                                    <button class="btn btn-sm btn-outline-primary">আবার অপেক্ষায়</button>
                                                </form>
                                            @endif
                                            @if(in_array($s->status, ['waiting', 'skipped']))
                                                <form method="POST" action="{{ route('assistant.serials.status', $s->id) }}" onsubmit="return confirm('সিরিয়াল #{{ $s->serial_number }} বাতিল করবেন?');">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button class="btn btn-sm btn-outline-danger">বাতিল</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-5">{{ $isToday ? 'আজ' : \Carbon\Carbon::parse($date)->format('d M Y') . ' তারিখে' }} এখনো কোনো সিরিয়াল দেওয়া হয়নি।</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const LOOKUP_URL = "{{ route('assistant.serials.lookup-patient') }}";

    function lookupPatient() {
        const mobile = document.getElementById('mobile').value.trim();
        const status = document.getElementById('lookupStatus');
        if (!mobile) return;

        fetch(`${LOOKUP_URL}?mobile=${encodeURIComponent(mobile)}&date=${encodeURIComponent(document.getElementById('serialDate').value)}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                if (!data.found) {
                    status.innerHTML = '<span class="text-warning-emphasis"><i class="fa-solid fa-user-plus me-1"></i>নতুন রোগী — নাম লিখুন</span>';
                    document.getElementById('pname').focus();
                    return;
                }
                document.getElementById('pname').value = data.name || '';
                document.getElementById('page').value = data.age || '';
                document.getElementById('pgender').value = data.gender || '';
                document.getElementById('pweight').value = data.weight || '';

                status.innerHTML = data.open_serial
                    ? `<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>পুরনো রোগী — এই তারিখে সিরিয়াল #${data.open_serial} আগে থেকেই আছে</span>`
                    : '<span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>পুরনো রোগী — তথ্য বসানো হয়েছে</span>';
            });
    }

    document.getElementById('lookupBtn').addEventListener('click', lookupPatient);
    document.getElementById('serialDate').addEventListener('change', function () {
        if (document.getElementById('mobile').value.trim()) lookupPatient();
    });
    document.getElementById('mobile').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); lookupPatient(); }
    });

    // কিউ অংশ ১৫ সেকেন্ড পরপর নিজে থেকে রিফ্রেশ হবে (ফর্ম অক্ষত থাকবে)
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
@endpush