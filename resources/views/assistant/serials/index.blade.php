@extends('layouts.assistant')

@section('title', 'সিরিয়াল')

@push('styles')
<style>
    .serial-no {
        width: 40px; height: 40px; border-radius: 12px; background: #e3f3f0; color: #0a6b65;
        display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.05rem;
    }
    .count-box { border-radius: 12px; padding: .6rem .9rem; background: #f8fafc; border: 1px solid #eef0f4; text-align: center; }
    .count-box .n { font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1; }
    .count-box .l { font-size: .7rem; color: #64748b; font-weight: 600; }
    .field-label { font-size: .72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .03em; }
    .table > :not(caption) > * > * { vertical-align: middle; }
    .as-search-results { display: grid; gap: 1rem; }
    .as-patient-summary { display: flex; align-items: center; gap: 1rem; padding: 1rem 1.1rem; border: 1px solid #d9eeeb; border-radius: 14px; background: linear-gradient(120deg, #f0faf8, #fff 72%); }
    .as-patient-avatar { width: 52px; height: 52px; flex: 0 0 52px; display: grid; place-items: center; border-radius: 15px; background: #d8f0ec; color: #08786f; font-size: 1.05rem; font-weight: 800; }
    .as-patient-main { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
    .as-patient-main h6 { margin: 0; color: #172b35; font-weight: 800; }
    .as-patient-id { border-radius: 999px; background: #e0f2fe; color: #075985; padding: .22rem .6rem; font-size: .76rem; font-weight: 750; }
    .as-patient-meta { display: flex; flex-wrap: wrap; gap: .45rem 1rem; margin-top: .45rem; color: #64748b; font-size: .82rem; }
    .as-rx-card { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.035); }
    .as-rx-head { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: .9rem 1rem; border-bottom: 1px solid #edf2f7; background: #f8fafc; }
    .as-rx-date { color: #172b35; font-weight: 750; }
    .as-rx-type { display: inline-block; margin-top: .2rem; border-radius: 999px; background: #e8f5f3; color: #086b64; padding: .18rem .55rem; font-size: .7rem; font-weight: 750; }
    .as-rx-body { display: grid; gap: .9rem; padding: 1rem; }
    .as-rx-info-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
    .as-rx-info { min-width: 0; padding: .75rem; border: 1px solid #edf2f7; border-radius: 10px; }
    .as-rx-label { margin-bottom: .3rem; color: #64748b; font-size: .68rem; font-weight: 800; letter-spacing: .045em; text-transform: uppercase; }
    .as-rx-value { color: #263746; font-size: .88rem; line-height: 1.5; white-space: pre-line; overflow-wrap: anywhere; }
    .as-rx-subsection-title { margin: 0 0 .45rem; color: #334155; font-size: .78rem; font-weight: 800; }
    .as-rx-medicines { display: grid; gap: .45rem; }
    .as-rx-medicine { padding: .65rem .75rem; border: 1px solid #e8edf2; border-radius: 9px; }
    .as-rx-medicine-name { color: #172b35; font-size: .86rem; font-weight: 750; }
    .as-rx-medicine-meta { margin-top: .18rem; color: #64748b; font-size: .78rem; line-height: 1.45; }
    .as-rx-vitals { display: flex; flex-wrap: wrap; gap: .45rem; }
    .as-rx-vital { border-radius: 8px; background: #f1f5f9; padding: .35rem .55rem; color: #475569; font-size: .78rem; }
    @media (max-width: 575.98px) {
        .as-patient-summary { align-items: flex-start; padding: .85rem; }
        .as-patient-avatar { width: 44px; height: 44px; flex-basis: 44px; border-radius: 12px; }
        .as-rx-head { align-items: flex-start; padding: .75rem; }
        .as-rx-body { padding: .75rem; }
        .as-rx-info-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
@if($section === 'search')
<section class="cardx p-4 mb-4" id="patient-prescription-search">
    <div class="row align-items-center g-3">
        <div class="col-lg-5">
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-print text-primary me-2"></i>Find and print a prescription</h5>
            <p class="text-muted small mb-0">
                Search by patient ID. Results are limited to {{ $selectedChamber?->chamber_name ?? 'the selected chamber' }}.
            </p>
        </div>
        <div class="col-lg-7">
            @if($selectedChamber)
                <form method="GET" action="{{ route('assistant.serials.index') }}" class="mb-0"
                      id="patientPrescriptionSearchForm"
                      data-search-url="{{ route('assistant.prescriptions.search') }}">
                    <input type="hidden" name="section" value="search">
                    <input type="hidden" name="chamber_id" value="{{ $selectedChamber->id }}">
                    <label class="field-label" for="patientIdSearch">Patient ID</label>
                    <div class="input-group">
                        <input type="text" name="patient_id" id="patientIdSearch" value="{{ $patientSearchTerm }}"
                               class="form-control" placeholder="e.g. 12547" inputmode="numeric" required>
                        <button class="btn btn-outline-primary" type="submit" aria-label="Search patient ID">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            @else
                <div class="alert alert-warning small mb-0">No active assigned chamber is available for prescription search.</div>
            @endif
        </div>
    </div>

    <div class="mt-3" id="patientPrescriptionResults" aria-live="polite" aria-busy="false">
        @if($patientSearchTerm !== '' && $selectedChamber)
            @if(!$searchedPatient)
                <div class="alert alert-warning small mb-0">No patient found for ID {{ $patientSearchTerm }}.</div>
            @else
                <div class="as-search-results">
                    <div class="as-patient-summary">
                        <div class="as-patient-avatar">{{ $searchedPatient->initials }}</div>
                        <div class="min-w-0">
                            <div class="as-patient-main">
                                <h6>{{ $searchedPatient->name }}</h6>
                                <span class="as-patient-id">Patient ID {{ $searchedPatient->patient_code }}</span>
                            </div>
                            <div class="as-patient-meta">
                                @if($searchedPatient->mobile)<span><i class="fa-solid fa-phone me-1"></i>{{ $searchedPatient->mobile }}</span>@endif
                                @if($searchedPatient->age !== null)<span><i class="fa-solid fa-cake-candles me-1"></i>{{ $searchedPatient->age }} years</span>@endif
                                @if($searchedPatient->gender)<span><i class="fa-solid fa-user me-1"></i>{{ $searchedPatient->gender }}</span>@endif
                                @if($searchedPatient->blood_group)<span><i class="fa-solid fa-droplet me-1"></i>{{ $searchedPatient->blood_group }}</span>@endif
                                @if($searchedPatient->weight)<span><i class="fa-solid fa-weight-scale me-1"></i>{{ $searchedPatient->weight }} kg</span>@endif
                            </div>
                        </div>
                    </div>

                    @if($patientPrescriptions->isEmpty())
                        <div class="alert alert-light border small mb-0">Patient found, but there are no prescriptions from this chamber.</div>
                    @else
                        @foreach($patientPrescriptions as $prescription)
                            <article class="as-rx-card">
                                <div class="as-rx-head">
                                    <div>
                                        <div class="as-rx-date"><i class="fa-regular fa-calendar me-1 text-primary"></i>{{ $prescription->created_at->timezone(config('easydoc.timezone'))->format('d M Y, h:i A') }}</div>
                                        <span class="as-rx-type">{{ $prescription->type === 'investigation_only' ? 'Investigation advice' : 'Prescription' }}</span>
                                    </div>
                                    <a class="btn btn-sm btn-primary flex-shrink-0" target="_blank" rel="noopener"
                                       href="{{ route('assistant.prescriptions.print', ['prescription' => $prescription->id, 'chamber_id' => $selectedChamber->id, 'autoprint' => 1]) }}">
                                        <i class="fa-solid fa-print me-1"></i> Print
                                    </a>
                                </div>
                                <div class="as-rx-body">
                                    <div class="as-rx-info-grid">
                                        @if(filled($prescription->clinical_complaints))
                                            <div class="as-rx-info"><div class="as-rx-label">Symptoms / complaints</div><div class="as-rx-value">{{ $prescription->clinical_complaints }}</div></div>
                                        @endif
                                        @if(filled($prescription->diagnosis))
                                            <div class="as-rx-info"><div class="as-rx-label">Diagnosis</div><div class="as-rx-value">{{ $prescription->diagnosis }}</div></div>
                                        @endif
                                        @if(filled($prescription->risk_factors))
                                            <div class="as-rx-info"><div class="as-rx-label">Risk factors / history</div><div class="as-rx-value">{{ $prescription->risk_factors }}</div></div>
                                        @endif
                                    </div>

                                    @if(filled($prescription->bp) || filled($prescription->pulse) || filled($prescription->temperature))
                                        <div>
                                            <div class="as-rx-subsection-title">Vitals</div>
                                            <div class="as-rx-vitals">
                                                @if(filled($prescription->bp))<span class="as-rx-vital"><strong>BP</strong> {{ $prescription->bp }}</span>@endif
                                                @if(filled($prescription->pulse))<span class="as-rx-vital"><strong>Pulse</strong> {{ $prescription->pulse }}</span>@endif
                                                @if(filled($prescription->temperature))<span class="as-rx-vital"><strong>Temp</strong> {{ $prescription->temperature }}</span>@endif
                                            </div>
                                        </div>
                                    @endif

                                    <div>
                                        <div class="as-rx-subsection-title">Medicines ({{ $prescription->medicines->count() }})</div>
                                        @if($prescription->medicines->isNotEmpty())
                                            <div class="as-rx-medicines">
                                                @foreach($prescription->medicines as $medicine)
                                                    <div class="as-rx-medicine">
                                                        <div class="as-rx-medicine-name">{{ $medicine->displayName() }}</div>
                                                        <div class="as-rx-medicine-meta">
                                                            Dose: {{ $medicine->doseLabel() }}
                                                            @if($medicine->duration) <span>&middot; Duration: {{ $medicine->duration }}</span>@endif
                                                            @if($medicine->instruction) <span>&middot; {{ $medicine->instruction }}</span>@endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="small text-muted">No medicine recorded for this prescription.</div>
                                        @endif
                                    </div>

                                    <div class="as-rx-info-grid">
                                        @if(filled($prescription->investigations))
                                            <div class="as-rx-info"><div class="as-rx-label">Tests / investigations</div><div class="as-rx-value">{{ $prescription->investigations }}</div></div>
                                        @endif
                                        @if(filled($prescription->investigation_findings))
                                            <div class="as-rx-info"><div class="as-rx-label">Investigation findings</div><div class="as-rx-value">{{ $prescription->investigation_findings }}</div></div>
                                        @endif
                                        @if(filled($prescription->advice))
                                            <div class="as-rx-info"><div class="as-rx-label">Doctor's advice</div><div class="as-rx-value">{{ $prescription->advice }}</div></div>
                                        @endif
                                        @if($prescription->followup_date)
                                            <div class="as-rx-info"><div class="as-rx-label">Follow-up</div><div class="as-rx-value">{{ $prescription->followup_date->format('d M Y') }}</div></div>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    @endif
                </div>
            @endif
        @else
            <p class="small text-muted mb-0">Enter the patient ID to find prescriptions.</p>
        @endif
    </div>
</section>
@endif

@if(in_array($section, ['new-serial', 'queue'], true))
<div class="row g-4">

    {{-- ============ নতুন সিরিয়াল ============ --}}
    @if($section === 'new-serial')
    <div class="col-12">
        <div class="cardx p-4" id="new-serial">
            <h5 class="fw-bold mb-1"><i class="fa-solid fa-ticket text-primary me-2"></i>নতুন সিরিয়াল</h5>
            <p class="text-muted small mb-3">রোগীর মোবাইল নাম্বার দিয়ে খুঁজুন। পুরনো রোগী হলে তথ্য নিজে থেকে বসবে।</p>

            <form method="POST" action="{{ route('assistant.serials.store') }}">
                @csrf

                @if($operationalChambers->isEmpty())
                    <div class="alert alert-warning">No assigned active chamber is available. Ask your doctor to assign a chamber and activate it.</div>
                @else
                    <div class="mb-3">
                        <label class="field-label">Chamber *</label>
                        <select name="chamber_id" id="chamberId" class="form-select" required>
                            @foreach($operationalChambers as $chamber)
                                <option value="{{ $chamber->id }}" {{ old('chamber_id', $selectedChamber?->id) == $chamber->id ? 'selected' : '' }}>{{ $chamber->chamber_name }}</option>
                            @endforeach
                        </select>
                    </div>

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
                @endif
            </form>
        </div>


    </div>

    {{-- ============ আজকের কিউ (এই অংশটা নিজে থেকে রিফ্রেশ হয়) ============ --}}
    @endif

    @if($section === 'queue')
    <div class="col-12">

        {{-- ============ তারিখ ফিল্টার ============ --}}
        <form method="GET" action="{{ route('assistant.serials.index') }}" class="cardx p-3 mb-3 d-flex flex-wrap gap-2 align-items-center">
            <input type="hidden" name="section" value="queue">
            @if($operationalChambers->isNotEmpty())
                <select name="chamber_id" class="form-select form-select-sm" style="max-width:220px;" onchange="this.form.submit()">
                    @foreach($operationalChambers as $chamber)
                        <option value="{{ $chamber->id }}" {{ $selectedChamber?->id === $chamber->id ? 'selected' : '' }}>{{ $chamber->chamber_name }}</option>
                    @endforeach
                </select>
            @endif
            <a href="{{ route('assistant.serials.index', ['section' => 'queue', 'chamber_id' => $selectedChamber?->id, 'date' => \Carbon\Carbon::parse($date)->subDay()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm" title="আগের দিন"><i class="fa-solid fa-chevron-left"></i></a>
            <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" style="max-width:160px;" onchange="this.form.submit()">
            <a href="{{ route('assistant.serials.index', ['section' => 'queue', 'chamber_id' => $selectedChamber?->id, 'date' => \Carbon\Carbon::parse($date)->addDay()->toDateString()]) }}" class="btn btn-outline-secondary btn-sm" title="পরের দিন"><i class="fa-solid fa-chevron-right"></i></a>
            <a href="{{ route('assistant.serials.index', ['section' => 'queue', 'chamber_id' => $selectedChamber?->id]) }}" class="btn btn-sm {{ $isToday ? 'btn-primary' : 'btn-outline-primary' }} fw-bold">আজ</a>

            @if($upcoming->isNotEmpty())
                <div class="d-flex flex-wrap gap-1 align-items-center ms-lg-auto">
                    <span class="small text-muted fw-semibold me-1">সিরিয়াল আছে:</span>
                    @foreach($upcoming as $u)
                        <a href="{{ route('assistant.serials.index', ['section' => 'queue', 'chamber_id' => $selectedChamber?->id, 'date' => $u['date']]) }}"
                           class="btn btn-sm {{ $date === $u['date'] ? 'btn-primary' : 'btn-outline-secondary' }} py-0 px-2" style="font-size:.75rem;">
                            {{ \Carbon\Carbon::parse($u['date'])->format('d M') }} <span class="badge text-bg-light">{{ $u['count'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </form>

        <div id="queueWrap">
            <div class="cardx p-4" id="serial-queue">
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
@endif
@endif
@endsection

@push('scripts')
<script>
    const LOOKUP_URL = "{{ route('assistant.serials.lookup-patient') }}";

    const patientSearchForm = document.getElementById('patientPrescriptionSearchForm');
    if (patientSearchForm) {
        const patientIdInput = document.getElementById('patientIdSearch');
        const patientResults = document.getElementById('patientPrescriptionResults');
        const searchUrl = patientSearchForm.dataset.searchUrl;
        const chamberId = patientSearchForm.querySelector('[name="chamber_id"]')?.value || '';
        let patientSearchTimer = null;
        let patientSearchRequest = null;

        function showPatientSearchMessage(message, style = 'text-muted') {
            const note = document.createElement('p');
            note.className = `small mb-0 ${style}`;
            note.textContent = message;
            patientResults.replaceChildren(note);
            patientResults.setAttribute('aria-busy', 'false');
        }

        function makePatientSearchInfo(label, value) {
            const cleanValue = String(value ?? '').trim();
            if (!cleanValue) return null;

            const block = document.createElement('div');
            block.className = 'as-rx-info';
            const title = document.createElement('div');
            title.className = 'as-rx-label';
            title.textContent = label;
            const content = document.createElement('div');
            content.className = 'as-rx-value';
            content.textContent = cleanValue;
            block.append(title, content);
            return block;
        }

        function appendPatientSearchInfoGrid(parent, entries) {
            const blocks = entries.map(([label, value]) => makePatientSearchInfo(label, value)).filter(Boolean);
            if (!blocks.length) return;
            const grid = document.createElement('div');
            grid.className = 'as-rx-info-grid';
            blocks.forEach((block) => grid.appendChild(block));
            parent.appendChild(grid);
        }

        function renderPatientSearchResults(data) {
            patientResults.replaceChildren();
            patientResults.setAttribute('aria-busy', 'false');

            if (!data.found) {
                showPatientSearchMessage('No patient found for this ID.', 'text-warning-emphasis');
                return;
            }

            if (!data.patient || !Array.isArray(data.prescriptions)) {
                showPatientSearchMessage('Could not read the search result. Please try again.', 'text-danger');
                return;
            }

            const results = document.createElement('div');
            results.className = 'as-search-results';

            const patientCard = document.createElement('div');
            patientCard.className = 'as-patient-summary';
            const avatar = document.createElement('div');
            avatar.className = 'as-patient-avatar';
            avatar.textContent = (String(data.patient.name || 'PT').trim().slice(0, 2) || 'PT').toUpperCase();
            const patientDetails = document.createElement('div');
            patientDetails.className = 'min-w-0';
            const patientMain = document.createElement('div');
            patientMain.className = 'as-patient-main';
            const patientName = document.createElement('h6');
            patientName.textContent = data.patient.name || 'Patient';
            const patientId = document.createElement('span');
            patientId.className = 'as-patient-id';
            patientId.textContent = `Patient ID ${data.patient.patient_id || ''}`;
            patientMain.append(patientName, patientId);
            const patientMeta = document.createElement('div');
            patientMeta.className = 'as-patient-meta';
            [
                data.patient.mobile,
                data.patient.age !== null && data.patient.age !== undefined ? `${data.patient.age} years` : '',
                data.patient.gender,
                data.patient.blood_group,
                data.patient.weight ? `${data.patient.weight} kg` : '',
            ].filter((value) => String(value || '').trim()).forEach((value) => {
                const item = document.createElement('span');
                item.textContent = value;
                patientMeta.appendChild(item);
            });
            patientDetails.append(patientMain, patientMeta);
            patientCard.append(avatar, patientDetails);
            results.appendChild(patientCard);

            if (!data.prescriptions.length) {
                const empty = document.createElement('div');
                empty.className = 'alert alert-light border small mb-0';
                empty.textContent = 'Patient found, but there are no prescriptions from this chamber.';
                results.appendChild(empty);
                patientResults.appendChild(results);
                return;
            }

            data.prescriptions.forEach((prescription) => {
                const card = document.createElement('article');
                card.className = 'as-rx-card';
                const header = document.createElement('div');
                header.className = 'as-rx-head';
                const heading = document.createElement('div');
                const date = document.createElement('div');
                date.className = 'as-rx-date';
                date.textContent = prescription.date || 'Prescription';
                const type = document.createElement('span');
                type.className = 'as-rx-type';
                type.textContent = prescription.type || 'Prescription';
                heading.append(date, type);
                const print = document.createElement('a');
                print.className = 'btn btn-sm btn-primary flex-shrink-0';
                print.href = prescription.print_url;
                print.target = '_blank';
                print.rel = 'noopener';
                print.innerHTML = '<i class="fa-solid fa-print me-1" aria-hidden="true"></i> Print';
                header.append(heading, print);

                const body = document.createElement('div');
                body.className = 'as-rx-body';
                appendPatientSearchInfoGrid(body, [
                    ['Symptoms / complaints', prescription.clinical_complaints],
                    ['Diagnosis', prescription.diagnosis],
                    ['Risk factors / history', prescription.risk_factors],
                ]);

                const vitals = Object.entries({
                    BP: prescription.vitals?.bp,
                    Pulse: prescription.vitals?.pulse,
                    Temp: prescription.vitals?.temperature,
                }).filter(([, value]) => String(value ?? '').trim());
                if (vitals.length) {
                    const vitalsSection = document.createElement('div');
                    const vitalsTitle = document.createElement('div');
                    vitalsTitle.className = 'as-rx-subsection-title';
                    vitalsTitle.textContent = 'Vitals';
                    const vitalsList = document.createElement('div');
                    vitalsList.className = 'as-rx-vitals';
                    vitals.forEach(([label, value]) => {
                        const item = document.createElement('span');
                        item.className = 'as-rx-vital';
                        item.textContent = `${label}: ${value}`;
                        vitalsList.appendChild(item);
                    });
                    vitalsSection.append(vitalsTitle, vitalsList);
                    body.appendChild(vitalsSection);
                }

                const medicines = Array.isArray(prescription.medicines) ? prescription.medicines : [];
                const medicineSection = document.createElement('div');
                const medicineTitle = document.createElement('div');
                medicineTitle.className = 'as-rx-subsection-title';
                medicineTitle.textContent = `Medicines (${medicines.length})`;
                medicineSection.appendChild(medicineTitle);
                if (medicines.length) {
                    const medicineList = document.createElement('div');
                    medicineList.className = 'as-rx-medicines';
                    medicines.forEach((medicine) => {
                        const item = document.createElement('div');
                        item.className = 'as-rx-medicine';
                        const name = document.createElement('div');
                        name.className = 'as-rx-medicine-name';
                        name.textContent = medicine.name || 'Medicine';
                        const meta = document.createElement('div');
                        meta.className = 'as-rx-medicine-meta';
                        meta.textContent = [
                            medicine.dose ? `Dose: ${medicine.dose}` : '',
                            medicine.duration ? `Duration: ${medicine.duration}` : '',
                            medicine.instruction || '',
                        ].filter(Boolean).join(' | ');
                        item.append(name, meta);
                        medicineList.appendChild(item);
                    });
                    medicineSection.appendChild(medicineList);
                } else {
                    const emptyMedicine = document.createElement('div');
                    emptyMedicine.className = 'small text-muted';
                    emptyMedicine.textContent = 'No medicine recorded for this prescription.';
                    medicineSection.appendChild(emptyMedicine);
                }
                body.appendChild(medicineSection);

                appendPatientSearchInfoGrid(body, [
                    ['Tests / investigations', prescription.investigations],
                    ['Investigation findings', prescription.investigation_findings],
                    ["Doctor's advice", prescription.advice],
                    ['Follow-up', prescription.followup_date],
                ]);
                card.append(header, body);
                results.appendChild(card);
            });

            patientResults.appendChild(results);
        }

        async function searchPatientPrescription() {
            const patientId = patientIdInput.value.trim();
            if (!patientId) {
                showPatientSearchMessage('Enter the patient ID to find prescriptions.');
                return;
            }

            if (/^\d+$/.test(patientId) && patientId.length < 5) {
                showPatientSearchMessage('Enter the complete patient ID, for example 12547.');
                return;
            }

            patientSearchRequest?.abort();
            patientSearchRequest = new AbortController();
            const request = patientSearchRequest;
            patientResults.setAttribute('aria-busy', 'true');
            showPatientSearchMessage('Searching…');
            patientResults.setAttribute('aria-busy', 'true');

            const url = new URL(searchUrl, window.location.origin);
            url.searchParams.set('patient_id', patientId);
            url.searchParams.set('chamber_id', chamberId);

            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    signal: request.signal,
                });
                const data = await response.json().catch(() => ({}));
                if (patientSearchRequest !== request) return;
                if (!response.ok) throw new Error(data.message || 'Patient search failed. Please try again.');
                if (typeof data.found !== 'boolean') throw new Error('Could not read the search result. Please try again.');
                renderPatientSearchResults(data);
            } catch (error) {
                if (error.name === 'AbortError' || patientSearchRequest !== request) return;
                showPatientSearchMessage(error.message || 'Could not search right now. Please try again.', 'text-danger');
            }
        }

        patientIdInput.addEventListener('input', function () {
            clearTimeout(patientSearchTimer);
            patientSearchRequest?.abort();
            patientSearchTimer = setTimeout(searchPatientPrescription, 300);
        });

        patientSearchForm.addEventListener('submit', function (event) {
            event.preventDefault();
            clearTimeout(patientSearchTimer);
            searchPatientPrescription();
        });
    }

    function lookupPatient() {
        const mobile = document.getElementById('mobile').value.trim();
        const status = document.getElementById('lookupStatus');
        if (!mobile) return;

        fetch(`${LOOKUP_URL}?mobile=${encodeURIComponent(mobile)}&date=${encodeURIComponent(document.getElementById('serialDate').value)}&chamber_id=${encodeURIComponent(document.getElementById('chamberId')?.value || '')}`, { headers: { 'Accept': 'application/json' } })
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

    const lookupBtn = document.getElementById('lookupBtn');
    const serialDateInput = document.getElementById('serialDate');
    const mobileInput = document.getElementById('mobile');
    if (lookupBtn && serialDateInput && mobileInput) {
        lookupBtn.addEventListener('click', lookupPatient);
        serialDateInput.addEventListener('change', function () {
            if (mobileInput.value.trim()) lookupPatient();
        });
        mobileInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); lookupPatient(); }
        });
    }

    // কিউ অংশ ১৫ সেকেন্ড পরপর নিজে থেকে রিফ্রেশ হবে (ফর্ম অক্ষত থাকবে)
    const queueWrap = document.getElementById('queueWrap');
    if (queueWrap) setInterval(async () => {
        if (document.hidden) return;
        try {
            const res = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            const fresh = doc.getElementById('queueWrap');
            if (fresh) queueWrap.innerHTML = fresh.innerHTML;
        } catch (e) {}
    }, 15000);
</script>
@endpush
