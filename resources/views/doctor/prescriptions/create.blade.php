@extends('layouts.doctor')

@section('title', 'Create Prescription')

@push('styles')
    {{-- Tailwind is used by the existing prescription form. Disable its global reset so it does not restyle the dashboard shell. --}}
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = { corePlugins: { preflight: false } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .prescription-builder { width: 100%; color: #1e293b; }
        .prescription-builder *,
        .prescription-builder *::before,
        .prescription-builder *::after { box-sizing: border-box; }
        .prescription-builder h1,
        .prescription-builder h2,
        .prescription-builder h3,
        .prescription-builder h4,
        .prescription-builder h5,
        .prescription-builder h6,
        .prescription-builder p,
        .prescription-builder ul,
        .prescription-builder ol { margin: 0; }
        .prescription-builder ul,
        .prescription-builder ol { padding: 0; list-style: none; }
        .prescription-builder button,
        .prescription-builder input,
        .prescription-builder select,
        .prescription-builder textarea { font-family: inherit; letter-spacing: inherit; }
        .prescription-builder button { cursor: pointer; }
        .prescription-builder input,
        .prescription-builder select,
        .prescription-builder textarea { min-width: 0; }
        #queueList { max-height: 260px; overflow-y: auto; }
        #appDialog ul { padding: 0; margin: 0; list-style: none; }
        #appDialog p, #appDialog h3 { margin: 0; }
        @media (max-width: 767px) {
            .prescription-builder { min-width: 0; }
        }

        /* ============ MOBILE STEP SYSTEM ============ */
        .rx-track { display: flex; align-items: flex-start; }
        .rx-dot { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; background: none; border: 0; padding: 0; position: relative; font-size: 10px; font-weight: 700; color: #94a3b8; line-height: 1.1; }
        .rx-dot::before { content: ''; position: absolute; top: 13px; left: -50%; width: 100%; height: 2px; background: #e2e8f0; z-index: 0; }
        .rx-dot:first-child::before { display: none; }
        .rx-dot.done::before, .rx-dot.active::before { background: #6366f1; }
        .rx-dot-c { position: relative; z-index: 1; width: 28px; height: 28px; border-radius: 999px; display: flex; align-items: center; justify-content: center; background: #fff; border: 2px solid #cbd5e1; font-size: 12px; }
        .rx-dot.active { color: #4338ca; }
        .rx-dot.active .rx-dot-c { background: #4f46e5; border-color: #4f46e5; color: #fff; box-shadow: 0 0 0 4px #e0e7ff; }
        .rx-dot.done { color: #059669; }
        .rx-dot.done .rx-dot-c { background: #10b981; border-color: #10b981; color: #fff; }
        .rx-badge { position: absolute; top: -5px; left: calc(50% + 8px); z-index: 2; background: #f43f5e; color: #fff; font-size: 9px; min-width: 16px; height: 16px; padding: 0 4px; border-radius: 99px; display: flex; align-items: center; justify-content: center; }
        .rx-nav { position: sticky; bottom: 0; z-index: 30; display: flex; align-items: center; gap: 8px; padding: 10px 12px calc(10px + env(safe-area-inset-bottom, 0px)); background: rgba(255,255,255,.97); border-top: 1px solid #e2e8f0; box-shadow: 0 -6px 18px rgba(15,23,42,.08); border-radius: 16px 16px 0 0; }
        .rx-nav-info { flex: 1; text-align: center; font-size: 11px; font-weight: 700; color: #64748b; line-height: 1.25; }
        .rx-nav-btn { border: 0; border-radius: 12px; padding: 11px 16px; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; }
        .rx-nav-back { background: #f1f5f9; color: #475569; }
        .rx-nav-back:disabled { opacity: .4; }
        .rx-nav-next { background: #4f46e5; color: #fff; box-shadow: 0 4px 10px rgba(79,70,229,.3); }
        .rx-nav-next.rx-save { background: #059669; box-shadow: 0 4px 10px rgba(5,150,105,.3); }
        @keyframes rxIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
        @media (max-width: 1023px) {
            .rx-step { display: none !important; }
            .rx-step.is-active { display: block !important; animation: rxIn .18s ease; }
            .rx-step.hidden { display: none !important; }
            #medicineCard { min-height: 0 !important; }
        }
        @media (min-width: 1024px) {
            #stepper, #stepNav { display: none !important; }
            #advCard > .rx-submit-row { margin-top: auto !important; }
        }

        /* ============ FIELD SUGGESTIONS (Dose / Duration / Instruction) ============ */
        .fld-sug { position: absolute; left: 0; top: 100%; margin-top: 4px; min-width: 100%; width: max-content; max-width: min(340px, 86vw); background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 12px 28px rgba(15,23,42,.18); z-index: 60; overflow-y: auto; display: none; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }
        .fld-sug.up { top: auto; bottom: 100%; margin: 0 0 4px; }
        .fld-opt { padding: 8px 12px; font-size: 13px; color: #334155; cursor: pointer; display: flex; justify-content: space-between; align-items: baseline; gap: 12px; border-bottom: 1px solid #f1f5f9; }
        .fld-opt:last-child { border-bottom: 0; }
        .fld-opt small { font-size: 10px; color: #94a3b8; white-space: nowrap; }
        .fld-opt.on, .fld-opt:hover { background: #eef2ff; color: #3730a3; }
        @media (pointer: coarse) { .fld-opt { padding: 11px 12px; } }

        /* ============ MEDICINE TABLE -> CARDS (mobile) ============ */
        @media (max-width: 767px) {
            #rxTable { min-width: 0 !important; display: block; width: 100%; }
            #rxTable thead { display: none; }
            #rxTable tbody { display: block; }
            #rxTable tbody tr { display: grid; grid-template-columns: 1fr auto; gap: 4px 8px; padding: 10px 2px; }
            #rxTable tbody tr#empty-row { display: block; }
            #rxTable tbody td { display: block; padding: 0 !important; }
            #rxTable tbody td:nth-child(1) { grid-column: 1; }
            #rxTable tbody td:nth-child(5) { grid-column: 2; grid-row: 1; }
            #rxTable tbody td:nth-child(2), #rxTable tbody td:nth-child(3), #rxTable tbody td:nth-child(4) { grid-column: 1 / -1; font-size: 12px; }
            #rxTable tbody td[data-label]::before { content: attr(data-label) ': '; font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .03em; }
            #rxTable tbody td[data-label]:empty { display: none; }
        }

        #rxToast { position: fixed; left: 50%; bottom: 88px; transform: translateX(-50%) translateY(10px); background: #0f172a; color: #fff; font-size: 12px; font-weight: 600; padding: 8px 14px; border-radius: 999px; z-index: 9998; opacity: 0; pointer-events: none; transition: all .2s; max-width: 90vw; text-align: center; }
        #rxToast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    </style>
@endpush

@section('content')
@php
    $doctor = auth()->user()->doctor;
    $parent = $parent ?? null;
    $serial = $serial ?? null;

    // Investigation visit-er patient, na hole serial-er patient (Start click korle ei patient-er info auto load hobe)
    $pp = $parent?->patient ?? $serial?->patient;

    // If returning from a validation error, restore the selected patient.
    $selPatient = $pp;
    if (!$selPatient && old('patient_id') && $doctor) {
        $selPatient = \App\Models\Patient::where('doctor_id', $doctor->id)->find(old('patient_id'));
    }
    $initPatient = $selPatient
        ? ['id' => $selPatient->id, 'code' => $selPatient->patient_code, 'name' => $selPatient->name]
        : null;
    $invCharge = (float) config('easydoc.investigation_advice_charge', 2);
    $fullCharge = (float) ($doctor?->getPrescriptionCharge() ?? 0);
    $balance = (float) ($doctor?->wallet?->balance ?? 0);
    // Controller theke $activeChamber (status = true AND doctor_active = true) na ashle Save block hobe
    $hasChamber = !empty($activeChamber ?? null);

    // ---------------------------------------------------------------
    // SERIAL QUEUE (optional): controller theke $serials pathale page-er bam-e "আজকের সিরিয়াল" dekhabe.
    // Field naam alada hole shudhu ei map() ta mila nin.
    // ---------------------------------------------------------------
    $serialCol = collect($serials ?? []);
    if ($serial && !$serialCol->contains('id', $serial->id)) {
        $serialCol->prepend($serial);
    }
    $queue = $serialCol->map(function ($s) {
        $p = data_get($s, 'patient');
        return [
            'id'          => data_get($s, 'id'),
            'no'          => data_get($s, 'serial_number', data_get($s, 'serial_no')),
            'status'      => strtolower((string) data_get($s, 'status', 'waiting')),
            'patient_id'  => data_get($s, 'patient_id', data_get($p, 'id')),
            'code'        => data_get($p, 'patient_code'),
            'name'        => data_get($p, 'name', data_get($s, 'patient_name')),
            'mobile'      => data_get($p, 'mobile', data_get($s, 'patient_mobile')),
            'age'         => data_get($p, 'age', data_get($s, 'patient_age')),
            'gender'      => data_get($p, 'gender', data_get($s, 'patient_gender')),
            'blood_group' => data_get($p, 'blood_group'),
            'address'     => data_get($p, 'address'),
            'weight'      => data_get($s, 'weight', data_get($p, 'weight')),
            'bp'          => data_get($s, 'bp'),
            'pulse'       => data_get($s, 'pulse'),
            'temperature' => data_get($s, 'temperature'),
            'reason'      => data_get($s, 'reason'),
        ];
    })->values()->all();
@endphp

<div class="prescription-builder w-full">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark mb-1">Create Prescription</h1>
            <p class="text-secondary small mb-0">রোগীর তথ্য, Clinical Vitals, Diagnosis ও Medicine দিয়ে প্রেসক্রিপশন তৈরি করুন।</p>
        </div>
        <a href="{{ route('doctor.prescriptions.index') }}" class="btn btn-outline-primary btn-sm fw-semibold">
            <i class="fa-solid fa-list-ul me-1"></i> Prescription List
        </a>
    </div>

    <form action="{{ route('doctor.prescriptions.store') }}" method="POST" id="prescriptionForm" autocomplete="off" novalidate>
            @csrf
            <input type="hidden" name="type" id="typeInput" value="{{ old('type', 'full') }}">
            <input type="hidden" name="parent_prescription_id" value="{{ $parent->id ?? old('parent_prescription_id') }}">
            <input type="hidden" name="serial_id" id="serialIdInput" value="{{ old('serial_id', $serial->id ?? '') }}">
            <input type="hidden" name="patient_id" id="patientIdInput" value="{{ old('patient_id', $parent->patient_id ?? $serial->patient_id ?? '') }}">

            @if($errors->any())
                <div class="max-w-[1400px] mx-auto mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <main class="max-w-[1400px] mx-auto grid grid-cols-12 gap-6">

                <!-- MODE TOGGLE + REPORT / SERIAL BANNER -->
                <div class="col-span-12 space-y-3">
                    @if($parent)
                        <div class="bg-indigo-50 border border-indigo-200 text-indigo-800 rounded-xl px-4 py-3 text-sm flex items-start gap-3">
                            <i class="fa-solid fa-flask-vial mt-0.5"></i>
                            <div>
                                <p class="font-bold">Report দেখে চূড়ান্ত প্রেসক্রিপশন লিখছেন</p>
                                <p class="text-xs mt-0.5">{{ $parent->created_at->format('d M Y') }} তারিখের Investigation Advice (#{{ $parent->id }}) থেকে তথ্য আনা হয়েছে। Investigation Findings ঘরে রিপোর্টের ফলাফল লিখুন, তারপর ঔষধ যোগ করুন।</p>
                            </div>
                        </div>
                    @endif

                    <!-- Serial banner (page-e serial niye ashle ba queue theke Start chaple dekhabe) -->
                    <div id="serialBanner" class="{{ $serial ? '' : 'hidden' }} bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm flex items-start gap-3">
                        <i class="fa-solid fa-ticket mt-0.5"></i>
                        <div class="min-w-0">
                            <p class="font-bold break-words" id="serialBannerTitle">@if($serial)সিরিয়াল #{{ $serial->serial_number }} · {{ $serial->patient->name ?? '' }}@endif</p>
                            <p class="text-xs mt-0.5">সহকারীর নেওয়া তথ্য ও Vitals আগে থেকেই ভরা আছে।<span id="serialBannerReason">@if($serial && $serial->reason) সমস্যা: {{ $serial->reason }}@endif</span> সেভ করলে সিরিয়াল নিজে থেকে "সম্পন্ন" হয়ে যাবে।</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" id="btnFull" onclick="switchMode('full')" class="px-4 py-2 rounded-xl text-sm font-bold border">
                            <i class="fa-solid fa-file-medical mr-1"></i> Full Prescription <span class="text-[11px] font-semibold opacity-80">(৳{{ rtrim(rtrim(number_format($fullCharge, 2), '0'), '.') }})</span>
                        </button>
                        <button type="button" id="btnInv" onclick="switchMode('investigation_only')" class="px-4 py-2 rounded-xl text-sm font-bold border">
                            <i class="fa-solid fa-flask mr-1"></i> Investigation Advice <span class="text-[11px] font-semibold opacity-80">(৳{{ rtrim(rtrim(number_format($invCharge, 2), '0'), '.') }})</span>
                        </button>
                    </div>
                </div>

                <!-- ===== MOBILE STEP BAR (শুধু মোবাইল/ট্যাবলেটে দেখাবে) ===== -->
                <div id="stepper" class="col-span-12 lg:hidden bg-white rounded-2xl border border-slate-100 shadow-sm px-2 pt-3 pb-2">
                    <div id="stepperTrack" class="rx-track"></div>
                </div>

                <!-- LEFT: Patient + Vitals + Advice/Save -->
                <div class="col-span-12 lg:col-span-5 xl:col-span-4 flex flex-col gap-6 min-w-0">

                    <!-- Serial queue (shudhu $serials thakle) -->
                    @if(!$parent && count($queue))
                    <div id="queueCard" data-step="1" class="rx-step is-active bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="px-5 py-3 bg-gradient-to-r from-violet-50 to-indigo-50 border-b border-slate-100 flex items-center justify-between gap-2">
                            <h3 class="font-bold text-slate-900 flex items-center"><i class="fa-solid fa-list-ol text-indigo-600 mr-2"></i> আজকের সিরিয়াল</h3>
                            <span id="queueCount" class="text-[11px] font-bold bg-indigo-600 text-white px-2.5 py-0.5 rounded-full">0</span>
                        </div>
                        <div id="queueList" class="p-3 space-y-2"></div>
                    </div>
                    @endif

                    <!-- Patient Info -->
                    <div id="stepPatient" data-step="1" class="rx-step is-active bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-900 flex items-center"><i class="fa-solid fa-circle-user text-indigo-600 mr-2 text-lg"></i> Patient Information</h3>
                            <span id="patientStatus" class="text-[10px] font-bold px-2 py-0.5 rounded-full hidden"></span>
                        </div>
                        <!-- Selected patient banner (Patient ID) -->
                        <div id="patientBanner" class="hidden"></div>

                        <!-- Find existing patient by Mobile / Patient ID -->
                        <div id="patientSearchWrap" class="mb-3">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Find Existing Patient</label>
                            <div class="flex gap-1">
                                <input type="text" id="patientSearch" placeholder="Mobile or Patient ID (e.g., PT-0007)" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <button type="button" id="patientSearchBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 rounded-lg">Search</button>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">একই মোবাইল নম্বরে একাধিক রোগী থাকতে পারে — তাই Patient ID দিয়েও খুঁজতে পারেন।</p>
                        </div>
                        <div id="patientMatches" class="hidden mb-3 space-y-2"></div>

                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Mobile *</label>
                                    <div class="flex gap-1">
                                        <input type="text" name="patient_mobile" id="patientMobile" value="{{ old('patient_mobile', $pp->mobile ?? '') }}" placeholder="017XXXXXXXX" inputmode="numeric" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <button type="button" id="lookupBtn" class="bg-indigo-50 border border-indigo-100 text-indigo-600 px-2.5 rounded-lg hover:bg-indigo-100"><i class="fa-solid fa-magnifying-glass text-xs"></i></button>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Name *</label>
                                    <input type="text" name="patient_name" id="patientName" value="{{ old('patient_name', $pp->name ?? '') }}" placeholder="Full Name" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Age</label>
                                    <input type="number" min="0" name="patient_age" id="patientAge" value="{{ old('patient_age', $pp->age ?? '') }}" placeholder="Years" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Weight</label>
                                    <input type="text" name="patient_weight" id="patientWeight" value="{{ old('patient_weight', data_get($serial, 'weight', $pp->weight ?? '')) }}" placeholder="KG" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Gender</label>
                                    @php $g = old('patient_gender', $pp->gender ?? ''); @endphp
                                    <select name="patient_gender" id="patientGender" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">--</option>
                                        <option value="Male" {{ strcasecmp($g, 'Male') == 0 ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ strcasecmp($g, 'Female') == 0 ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ strcasecmp($g, 'Other') == 0 ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Blood Group</label>
                                    <input type="text" name="blood_group" id="patientBlood" value="{{ old('blood_group', $pp->blood_group ?? '') }}" placeholder="B+" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Address</label>
                                    <input type="text" name="patient_address" id="patientAddress" value="{{ old('patient_address', $pp->address ?? '') }}" placeholder="Optional" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clinical Vitals -->
                    <div id="stepClinical" data-step="2" class="rx-step bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
                        <h3 class="font-bold text-slate-900 flex items-center mb-2"><i class="fa-solid fa-heart-pulse text-rose-500 mr-2 text-lg"></i> Clinical Vitals</h3>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">BP (mmHg)</span>
                                <input type="text" name="bp" id="vBp" value="{{ old('bp', $parent->bp ?? $serial->bp ?? '') }}" placeholder="120/80" class="w-full bg-transparent border-0 p-0 text-sm font-semibold text-slate-700 focus:ring-0 focus:outline-none mt-1">
                            </div>
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Pulse</span>
                                <input type="text" name="pulse" id="vPulse" value="{{ old('pulse', $parent->pulse ?? $serial->pulse ?? '') }}" placeholder="72" class="w-full bg-transparent border-0 p-0 text-sm font-semibold text-slate-700 focus:ring-0 focus:outline-none mt-1">
                            </div>
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Temp (°F)</span>
                                <input type="text" name="temperature" id="vTemp" value="{{ old('temperature', $parent->temperature ?? $serial->temperature ?? '') }}" placeholder="98.6" class="w-full bg-transparent border-0 p-0 text-sm font-semibold text-slate-700 focus:ring-0 focus:outline-none mt-1">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Clinical Complaints</label>
                            <textarea name="clinical_complaints" id="symptomsInput" rows="2" placeholder="e.g., Fever with chest pain..." class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('clinical_complaints', $parent->clinical_complaints ?? $serial->reason ?? '') }}</textarea>

                            <button type="button" onclick="suggestDiagnosis()" id="diagBtn" class="mt-2 text-xs font-bold bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-3 py-1.5 rounded-lg flex items-center gap-1 hover:opacity-90">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Suggest Diagnosis (AI)
                            </button>
                            <div id="diagSuggestions" class="flex flex-wrap gap-1.5 mt-2"></div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Risk Factors</label>
                            <textarea name="risk_factors" id="riskInput" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('risk_factors', $parent->risk_factors ?? '') }}</textarea>
                        </div>
                    </div>
                    <!-- Advice + Follow-up + Submit (দুই মোডেই দেখা যাবে) -->
                    <div id="advCard" data-step="5" class="rx-step lg:flex-1 flex flex-col bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-100 space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 block mb-1">Advice</label>
                            <textarea name="advice" id="adviceInput" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('advice') }}</textarea>

                            <!-- Quick Advice presets (JS diye render hoy, ADVICE_PRESETS array te notun advice jog korun) -->
                            <div class="mt-3">
                                <span class="text-[11px] font-bold text-slate-400 uppercase block mb-1.5"><i class="fa-solid fa-bolt text-amber-400 mr-1"></i> Quick Advice</span>
                                <div id="adviceTabs" class="flex flex-wrap gap-1.5 mb-2"></div>
                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-3">
                                    <div id="adviceChips" class="flex flex-wrap gap-1.5"></div>
                                    <button type="button" id="adviceAddAll" class="mt-2 text-[11px] font-bold text-indigo-600 hover:underline">+ এই গ্রুপের সবগুলো যোগ করুন</button>
                                </div>
                            </div>
                        </div>
                        <div class="rx-submit-row grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-slate-500 block mb-1" id="followupLabel">Follow-up Date</label>
                                <input type="date" name="followup_date" id="followupDate" value="{{ old('followup_date') }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="flex items-end md:col-span-2">
                                <button type="submit" id="submitBtn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl text-sm shadow-md flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-print mr-2"></i> <span id="submitLabel">Save & Print Prescription</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-span-12 lg:col-span-7 xl:col-span-8 flex flex-col gap-6 min-w-0">

                    <!-- Investigation-only note -->
                    <div id="investigationNote" data-step="3" class="rx-step hidden bg-teal-50 border border-teal-200 rounded-2xl p-6">
                        <h3 class="font-bold text-teal-900 flex items-center mb-2"><i class="fa-solid fa-flask mr-2"></i> Investigation Advice মোড</h3>
                        <p class="text-sm text-teal-800">এই ভিজিটে কোনো ঔষধ লেখা হচ্ছে না। বাম দিকে রোগীর তথ্য ও পরীক্ষাগুলো লিখে সেভ করুন, রোগীর জন্য একটা Investigation Slip প্রিন্ট হবে।</p>
                        <p class="text-sm text-teal-800 mt-2">রোগী রিপোর্ট নিয়ে ফিরলে মোবাইল নাম্বার দিয়ে খুঁজুন। সিস্টেম এই ভিজিটটা দেখাবে, <b>Continue with Reports</b> চাপলে সব তথ্য আগে থেকেই ভরা থাকবে।</p>
                    </div>

                    <!-- Diagnosis & Investigations -->
                    <div id="dxCard" data-step="3" class="rx-step bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
                        <h3 class="font-bold text-slate-900 flex items-center mb-1"><i class="fa-solid fa-stethoscope text-teal-600 mr-2 text-lg"></i> Diagnosis & Tests</h3>

                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Diagnosis <span id="dxHint" class="normal-case font-normal text-slate-400"></span></label>
                            <textarea name="diagnosis" id="diagnosisInput" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('diagnosis', $parent->diagnosis ?? '') }}</textarea>

                            <div id="patternPanel" class="hidden mt-3 bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                                <p class="text-xs font-bold text-emerald-800 flex items-center mb-2">
                                    <i class="fa-solid fa-brain mr-1.5"></i> আপনার Usual Treatment এই Diagnosis-এর জন্য
                                </p>
                                <div id="patternList" class="space-y-2"></div>
                            </div>
                        </div>

                        <div class="relative">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Investigations / Tests Advice <span id="invReq" class="text-rose-500 hidden">*</span></label>
                            <textarea name="investigations" id="investigations" rows="2" placeholder="টেস্টের নাম লিখুন — suggestion আসবে (যেমন: cbc, sugar, usg, ecg)" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('investigations', $parent->investigations ?? '') }}</textarea>

                            <!-- Test suggestions (likhle dropdown-e ashbe; TEST_CATALOG array te notun test jog korun) -->
                            <div id="invSuggest" class="hidden absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-xl z-40 max-h-60 overflow-y-auto"></div>
                        </div>

                        <div id="findingsWrap" class="{{ $parent ? 'bg-amber-50 border border-amber-200 rounded-xl p-3' : '' }}">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Investigation Findings <span class="normal-case font-normal text-slate-400">(রিপোর্টের ফলাফল)</span></label>
                            <textarea name="investigation_findings" id="investigationFindings" rows="3" placeholder="e.g., CBC: Hb 11.2, WBC 14000, Platelet 90000. NS-1: Positive" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">{{ old('investigation_findings') }}</textarea>
                        </div>
                    </div>

                    <!-- Medicine card -->
                    <div id="medicineCard" data-step="4" class="rx-step lg:flex-1 bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-100 flex flex-col lg:min-h-[420px]">
                        <div class="flex justify-between items-center pb-4 border-b border-slate-100 mb-4 flex-wrap gap-2">
                            <h3 class="font-bold text-lg text-slate-900 flex items-center"><span class="text-indigo-600 font-serif italic text-2xl mr-2">Rx</span> Medicine Prescription</h3>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" onclick="checkConflicts()" id="conflictBtn" class="bg-gradient-to-r from-rose-500 to-orange-500 hover:opacity-90 text-white text-xs font-bold py-2 px-3 rounded-lg shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-triangle-exclamation"></i> AI Conflict Check
                                </button>
                                <button type="button" onclick="generateAiMedicines()" id="aiMedBtn" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:opacity-90 text-white text-xs font-bold py-2 px-3 rounded-lg shadow-sm flex items-center gap-1">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> Generate with AI
                                </button>
                            </div>
                        </div>

                        <!-- AI Suggestion Panel -->
                        <div id="aiMedPanel" class="hidden mb-4 bg-purple-50 border border-purple-200 rounded-xl p-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-bold text-purple-800 flex items-center"><i class="fa-solid fa-robot mr-1.5"></i> AI Suggested Treatment — রিভিউ করে Add করুন</span>
                                <button type="button" onclick="document.getElementById('aiMedPanel').classList.add('hidden')" class="text-purple-400 hover:text-purple-600 text-xs"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <p class="text-[11px] text-purple-700 mb-3 italic">⚠ AI suggestions শুধুমাত্র সহায়ক — প্রেসক্রাইব করার আগে অবশ্যই ক্লিনিক্যালি যাচাই করুন।</p>
                            <div id="aiMedList" class="space-y-2"></div>
                            <div id="aiAdviceBox" class="hidden mt-3 bg-white border border-purple-100 rounded-lg p-3">
                                <p class="text-xs font-bold text-purple-700 mb-1">Suggested Advice:</p>
                                <p id="aiAdviceText" class="text-xs text-slate-600 mb-2"></p>
                                <button type="button" onclick="useAiAdvice()" class="text-xs font-bold text-purple-600 hover:underline">+ Use this advice</button>
                            </div>
                        </div>

                        <!-- AI Conflict Check result -->
                        <div id="conflictBox" class="hidden mb-4"></div>

                        <!-- Add Medicine Row -->
                        <div class="grid grid-cols-2 md:grid-cols-12 gap-3 mb-3 bg-slate-50 p-3 sm:p-4 rounded-xl border border-slate-100">
                            <div class="col-span-2 md:col-span-4 relative">
                                <label class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Search Medicine</label>
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-sm"></i>
                                    <input type="text" id="med-search" placeholder="Type sec, napa, pcm..." autocomplete="off" class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <input type="hidden" id="med-id">
                                </div>
                                <div id="search-results" class="hidden absolute left-0 right-0 md:right-4 mt-1 bg-white border border-slate-200 rounded-lg shadow-xl z-50 max-h-52 overflow-y-auto text-sm"></div>
                            </div>
                            <div class="col-span-1 md:col-span-2 relative">
                                <label class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Type</label>
                                <select id="med-type" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="Tablet">Tablet</option>
                                    <option value="Capsule">Capsule</option>
                                    <option value="Syrup">Syrup</option>
                                    <option value="Injection">Injection</option>
                                    <option value="Drop">Drop</option>
                                    <option value="Cream">Cream</option>
                                    <option value="Inhaler">Inhaler</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-span-1 md:col-span-2 relative">
                                <label class="text-[11px] font-bold text-slate-400 uppercase block mb-1" id="med-dose-label">Dose (M+A+N)</label>
                                <input type="text" id="med-dose" placeholder="e.g., 1+0+1" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <input type="text" id="med-dose-text" placeholder="২ চামচ" class="hidden w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="col-span-1 md:col-span-2 relative">
                                <label class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Duration</label>
                                <input type="text" id="med-duration" placeholder="৭ দিন" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="col-span-2 md:col-span-2 flex items-end order-last md:order-none">
                                <button type="button" onclick="addMedicineRow()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-lg text-sm shadow-sm">
                                    <i class="fa-solid fa-plus mr-1"></i> Add
                                </button>
                            </div>
                            <div class="col-span-2 md:col-span-12 relative">
                                <label class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Instruction (optional)</label>
                                <input type="text" id="med-instruction" placeholder="খাবার পরে" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-400 -mt-1 mb-3"><i class="fa-solid fa-lightbulb text-amber-400 mr-1"></i> Dose / Duration / Instruction ঘরে ট্যাপ করলেই সব অপশন দেখাবে, লিখলে ফিল্টার হবে (বাংলা বা ইংরেজি সংখ্যা দুটোই চলবে)। Instruction-এ একাধিক অপশন যোগ করা যায়।</p>

                        <!-- Favorites -->
                        <div class="mb-4" id="favWrap" style="display:none;">
                            <span class="text-xs font-semibold text-slate-400 block mb-2"><i class="fa-solid fa-star text-amber-400 mr-1"></i> Quick Favorites:</span>
                            <div class="flex flex-wrap gap-2" id="favChips"></div>
                        </div>

                        <!-- Table -->
                        <div class="flex-grow overflow-x-auto">
                            <table id="rxTable" class="w-full text-left border-collapse min-w-[520px]">
                                <thead>
                                    <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        <th class="py-2">Medicine</th>
                                        <th class="py-2">Dose</th>
                                        <th class="py-2">Duration</th>
                                        <th class="py-2">Instruction</th>
                                        <th class="py-2 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="prescription-list" class="text-sm divide-y divide-slate-50">
                                    <tr id="empty-row">
                                        <td colspan="5" class="text-xs text-slate-400 italic text-center py-8">No medications added yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ===== MOBILE STEP NAV ===== -->
                <div id="stepNav" class="rx-nav col-span-12 lg:hidden">
                    <button type="button" id="stepBack" class="rx-nav-btn rx-nav-back"><i class="fa-solid fa-arrow-left"></i> পিছনে</button>
                    <span id="stepInfo" class="rx-nav-info"></span>
                    <button type="button" id="stepNext" class="rx-nav-btn rx-nav-next"></button>
                </div>
            </main>
        </form>
</div>

<!-- ===================== POPUP DIALOG (alert-er bodole) ===================== -->
<div id="appDialog" class="hidden fixed inset-0 z-[9999] items-center justify-center p-4 bg-slate-900/50" aria-hidden="true">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 text-center max-h-[90vh] overflow-y-auto" role="dialog" aria-modal="true">
        <div id="dlgIcon" class="mx-auto w-14 h-14 rounded-full flex items-center justify-center text-2xl mb-3"></div>
        <h3 id="dlgTitle" class="font-bold text-lg text-slate-900 mb-1"></h3>
        <p id="dlgMessage" class="text-sm text-slate-600 whitespace-pre-line"></p>
        <ul id="dlgItems" class="hidden mt-3 space-y-1.5 text-left"></ul>
        <div class="flex flex-wrap gap-2 mt-5">
            <button type="button" id="dlgCancel" class="hidden flex-1 min-w-[110px] px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"></button>
            <button type="button" id="dlgExtra" class="hidden flex-1 min-w-[110px] px-4 py-2.5 rounded-xl text-sm font-semibold border border-indigo-200 bg-white text-indigo-700 hover:bg-indigo-50"></button>
            <button type="button" id="dlgConfirm" class="flex-1 min-w-[110px] px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
        const SUGGEST_URL = "{{ route('doctor.prescriptions.medicine-suggest') }}";
        const LOOKUP_URL = "{{ route('doctor.prescriptions.lookup-patient') }}";
        const TOP_MED_URL = "{{ route('doctor.prescriptions.top-medicines') }}";
        const AI_DIAG_URL = "{{ route('doctor.prescriptions.ai-diagnosis') }}";
        const AI_MED_URL = "{{ route('doctor.prescriptions.ai-medicine') }}";
        const PATTERN_URL = "{{ route('doctor.prescriptions.pattern-suggest') }}";
        const CHECK_URL = "{{ route('doctor.prescriptions.check-interactions') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const HAS_PARENT = @json((bool) $parent);
        const INIT_PATIENT = @json($initPatient);
        const FULL_CHARGE = {{ $fullCharge }};
        const INV_CHARGE = {{ $invCharge }};
        const BALANCE = {{ $balance }};
        const HAS_CHAMBER = @json($hasChamber);
        const CHAMBERS_URL = "{{ route('doctor.chambers.index') }}";
        const START_URL = "{{ route('doctor.queue.start', ['serial' => '__ID__']) }}";
        const SERVER_ERROR = @json(session('error'));
        const SERVER_SUCCESS = @json(session('success'));
        const SERIALS = @json($queue);
        const OLD_MEDS = @json(array_values((array) old('medicines', [])));
        const ASK_CONFLICT_CHECK = true;      // Save-er age "Conflict check koren ni" bole jiggesh korbe (false = bondho)
        const CONFIRM_BEFORE_SAVE = false;    // true korle proti Save-e charge confirm popup ashbe
        let lastAiAdvice = '';
        let dirty = false;
        let submitting = false;

        const byId = (id) => document.getElementById(id);
        const val = (id) => { const e = byId(id); return e ? e.value.trim() : ''; };
        const money = (n) => '৳' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

        function escHtml(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        const medSearch = byId('med-search');
        const medId = byId('med-id');
        const medType = byId('med-type');
        const medDose = byId('med-dose');
        const medDoseText = byId('med-dose-text');
        const medDoseLabel = byId('med-dose-label');
        const searchResults = byId('search-results');
        const prescriptionList = byId('prescription-list');
        let searchDebounce;
        let pendingGeneric = '';   // list theke bachai kora ouchudh-er generic (duplicate check-er jonno)

        /* ================= POPUP DIALOG ================= */
        // returns Promise: 'confirm' | 'cancel' | 'extra'
        const DLG_ICONS = {
            warning: ['fa-triangle-exclamation', 'bg-amber-100 text-amber-600'],
            danger:  ['fa-circle-xmark',         'bg-rose-100 text-rose-600'],
            info:    ['fa-circle-info',          'bg-indigo-100 text-indigo-600'],
            success: ['fa-circle-check',         'bg-emerald-100 text-emerald-600'],
        };

        function appDialog(opts) {
            const o = Object.assign({ type: 'warning', title: '', message: '', items: [], confirmText: 'ঠিক আছে', cancelText: null, extraText: null }, opts || {});
            return new Promise(function (resolve) {
                const root = byId('appDialog');
                const ic = DLG_ICONS[o.type] || DLG_ICONS.warning;
                byId('dlgIcon').className = 'mx-auto w-14 h-14 rounded-full flex items-center justify-center text-2xl mb-3 ' + ic[1];
                byId('dlgIcon').innerHTML = '<i class="fa-solid ' + ic[0] + '"></i>';
                byId('dlgTitle').textContent = o.title;
                byId('dlgMessage').textContent = o.message;

                const ul = byId('dlgItems');
                ul.innerHTML = o.items.map(function (t) {
                    return '<li class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-xs text-slate-700">' + escHtml(t) + '</li>';
                }).join('');
                ul.classList.toggle('hidden', !o.items.length);

                const bC = byId('dlgConfirm'), bX = byId('dlgCancel'), bE = byId('dlgExtra');
                bC.textContent = o.confirmText;
                bC.className = 'flex-1 min-w-[110px] px-4 py-2.5 rounded-xl text-sm font-bold text-white ' +
                    (o.type === 'danger' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-indigo-600 hover:bg-indigo-700');
                bX.classList.toggle('hidden', !o.cancelText); if (o.cancelText) bX.textContent = o.cancelText;
                bE.classList.toggle('hidden', !o.extraText);  if (o.extraText) bE.textContent = o.extraText;

                const finish = function (r) {
                    root.classList.add('hidden'); root.classList.remove('flex');
                    root.setAttribute('aria-hidden', 'true');
                    document.removeEventListener('keydown', onKey);
                    root.onclick = null;
                    resolve(r);
                };
                const onKey = function (e) {
                    if (e.key === 'Escape') { e.preventDefault(); finish(o.cancelText ? 'cancel' : 'confirm'); }
                };
                bC.onclick = function () { finish('confirm'); };
                bX.onclick = function () { finish('cancel'); };
                bE.onclick = function () { finish('extra'); };
                root.onclick = function (e) { if (e.target === root && o.cancelText) finish('cancel'); };
                document.addEventListener('keydown', onKey);

                root.classList.remove('hidden'); root.classList.add('flex');
                root.setAttribute('aria-hidden', 'false');
                bC.focus();
            });
        }

        // Chamber chalu nei -> popup warning (OK chaple "আমার চেম্বার" page-e niye jabe)
        async function showNoChamberDialog() {
            const r = await appDialog({
                type: 'danger',
                title: 'কোনো চেম্বার চালু নেই',
                message: 'প্রেসক্রিপশন লিখতে একটি চেম্বার চালু থাকতে হবে।\n"আমার চেম্বার" পেজ থেকে একটি চেম্বার চালু করুন।',
                confirmText: 'আমার চেম্বারে যান',
                cancelText: 'পরে করব',
            });
            if (r === 'confirm') { dirty = false; window.location.href = CHAMBERS_URL; }
        }

        function setSelect(sel, value) {
            if (!sel || value == null || value === '') return;
            const v = String(value).toLowerCase();
            for (const o of sel.options) { if (o.value.toLowerCase() === v) { sel.value = o.value; return; } }
        }

        /* ================= MODE: Full / Investigation Advice ================= */
        const ACTIVE_CLS = ['bg-indigo-600', 'text-white', 'border-indigo-600', 'shadow-md'];
        const INACTIVE_CLS = ['bg-white', 'text-slate-600', 'border-slate-200'];
        const fmtCharge = (n) => String(Number(n));

        function currentMode() { return byId('typeInput').value === 'investigation_only' ? 'investigation_only' : 'full'; }
        function currentCharge() { return currentMode() === 'investigation_only' ? INV_CHARGE : FULL_CHARGE; }
        function medRowCount() { return document.querySelectorAll('#prescription-list tr:not(#empty-row)').length; }

        function setMode(mode) {
            const inv = mode === 'investigation_only';
            byId('typeInput').value = mode;

            byId('medicineCard').classList.toggle('hidden', inv);
            byId('investigationNote').classList.toggle('hidden', !inv);
            byId('findingsWrap').classList.toggle('hidden', inv);
            byId('invReq').classList.toggle('hidden', !inv);
            byId('dxHint').textContent = inv ? '(Provisional)' : '';
            byId('followupLabel').textContent = inv ? 'Report নিয়ে আসার তারিখ' : 'Follow-up Date';
            byId('submitLabel').textContent = inv
                ? `Save Investigation Slip & Print (৳${fmtCharge(INV_CHARGE)})`
                : `Save & Print Prescription (৳${fmtCharge(FULL_CHARGE)})`;

            // Investigation mode-e ঔষধ submit hobe na
            prescriptionList.querySelectorAll('input').forEach(i => { i.disabled = inv; });
            if (inv) byId('patternPanel').classList.add('hidden');

            const btnFull = byId('btnFull');
            const btnInv = byId('btnInv');
            btnFull.classList.remove(...ACTIVE_CLS, ...INACTIVE_CLS);
            btnInv.classList.remove(...ACTIVE_CLS, ...INACTIVE_CLS);
            btnFull.classList.add(...(inv ? INACTIVE_CLS : ACTIVE_CLS));
            btnInv.classList.add(...(inv ? ACTIVE_CLS : INACTIVE_CLS));

            byId('dxCard').classList.toggle('lg:flex-1', inv);
            onStepModeChange();
        }

        async function switchMode(mode) {
            if (mode === currentMode()) return;
            if (mode === 'investigation_only' && medRowCount() > 0) {
                const r = await appDialog({
                    type: 'warning', title: 'Investigation Advice মোডে যাবেন?',
                    message: 'এই মোডে ঔষধ লেখা হয় না। আপনার যোগ করা ' + medRowCount() + 'টি ঔষধ এই ভিজিটে সেভ হবে না (Full Prescription-এ ফিরলে তালিকা থাকবে)।',
                    confirmText: 'মোড বদলান', cancelText: 'এখানেই থাকুন',
                });
                if (r !== 'confirm') return;
            }
            setMode(mode);
        }

        /* ================= Medicine search + autocomplete ================= */
        function syncDoseField() {
            const isSyrup = medType.value === 'Syrup';
            medDose.classList.toggle('hidden', isSyrup);
            medDoseText.classList.toggle('hidden', !isSyrup);
            medDoseLabel.textContent = isSyrup ? 'Dose (e.g., ২ চামচ)' : 'Dose (M+A+N)';
        }
        medType.addEventListener('change', syncDoseField);

        document.addEventListener('click', function (e) {
            if (!medSearch.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });

        medSearch.addEventListener('input', function () {
            medId.value = ''; pendingGeneric = '';
            clearTimeout(searchDebounce);
            const term = this.value.trim();
            if (term.length < 2) { searchResults.classList.add('hidden'); return; }

            searchDebounce = setTimeout(() => {
                fetch(`${SUGGEST_URL}?term=${encodeURIComponent(term)}`)
                    .then(res => res.json())
                    .then(data => {
                        const list = Array.isArray(data) ? data : (data.data || []);
                        if (!list.length) { searchResults.classList.add('hidden'); return; }
                        searchResults.innerHTML = list.map(item => `
                            <div class="px-4 py-2 hover:bg-indigo-50 cursor-pointer flex justify-between items-center gap-2" data-id="${escHtml(item.id)}" data-label="${escHtml(item.label)}" data-generic="${escHtml(item.generic_name || '')}">
                                <div>
                                    <div class="font-semibold text-slate-700">${escHtml(item.label)}</div>
                                    <div class="text-[11px] text-slate-400">${escHtml(item.generic_name || '')}${item.company ? ' · ' + escHtml(item.company) : ''}</div>
                                </div>
                                ${item.is_favorite_company ? '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap" style="background:#d1fae5;color:#065f46"><i class="fa-solid fa-star"></i> Best</span>' : ''}
                                ${item.is_sponsored ? '<span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full whitespace-nowrap"><i class="fa-solid fa-star"></i> Sponsored</span>' : ''}
                            </div>`).join('');
                        searchResults.classList.remove('hidden');

                        searchResults.querySelectorAll('div[data-id]').forEach(el => {
                            el.addEventListener('click', function () {
                                medSearch.value = this.dataset.label;
                                medId.value = this.dataset.id;
                                pendingGeneric = (this.dataset.generic || '').toLowerCase();
                                searchResults.classList.add('hidden');
                                (medType.value === 'Syrup' ? medDoseText : medDose).focus();
                            });
                        });
                    })
                    .catch(() => searchResults.classList.add('hidden'));
            }, 250);
        });

        function fillDose(v) { (medType.value === 'Syrup' ? medDoseText : medDose).value = v; }
        function fillDuration(v) { byId('med-duration').value = v; }
        function fillInstruction(v) { byId('med-instruction').value = v; }

        /* ================= Investigation suggestions (likhle suggest hobe) ================= */
// Notun test jog korte chaile ei list-e ekta line add korun.
// Format: 'Test-er Naam|alias|alias'  (alias diye-o khujle pawa jabe, e.g. "sugar" likhle RBS/FBS ashbe).
// Test-er naame comma (,) dibenna, karon comma diye test alada kora hoy.
const TEST_CATALOG = [
    // --- Blood: basic ---
    'CBC|complete blood count|blood count|hb|hemoglobin|wbc|platelet',
    'Hb%|hemoglobin|haemoglobin|anemia',
    'ESR|erythrocyte sedimentation rate',
    'Platelet Count|platelet|plt|dengue',
    'TC DC|total count|differential count|wbc',
    'PBF|peripheral blood film|blood film|smear',
    'Reticulocyte Count|retic',
    'Blood Grouping & Rh|blood group|rh factor',
    'Bleeding Time & Clotting Time|bt ct',
    'Prothrombin Time (PT/INR)|pt|inr|coagulation',
    'aPTT|activated partial thromboplastin',
    'D-Dimer|ddimer',
    'Coombs Test|direct coombs|indirect coombs',
    'Hb Electrophoresis|thalassemia|hemoglobin electrophoresis',
    'Serum Ferritin|ferritin|s. ferritin|iron',
    'Serum Iron|iron',
    'TIBC|iron binding capacity',
    'Serum Vitamin B12|b12|vitamin b12',
    'Serum Folate|folic acid|folate',
    // --- Sugar / diabetes ---
    'RBS|random blood sugar|sugar|glucose',
    'FBS|fasting blood sugar|sugar|glucose',
    '2 Hrs ABF|2 hrs after breakfast|ppbs|pp sugar|sugar',
    '2 Hrs After 75g Glucose (OGTT)|ogtt|glucose tolerance|sugar',
    'HbA1c|hba1c|glycated hemoglobin|sugar|diabetes',
    'Urine Sugar|urine glucose',
    'Urine Ketone|ketone',
    'Serum Insulin (Fasting)|insulin',
    'C-Peptide|cpeptide',
    'Urine Microalbumin|albumin creatinine ratio|acr|microalbuminuria',
    // --- Lipid ---
    'Lipid Profile|cholesterol|tg|ldl|hdl|triglyceride',
    'Serum Cholesterol|cholesterol',
    'Serum Triglyceride|triglyceride|tg',
    'LDL-C|ldl|cholesterol',
    'HDL-C|hdl|cholesterol',
    'Lipoprotein (a)|lp(a)|lipoprotein a',
    // --- Liver ---
    'LFT|liver function test|liver profile',
    'SGPT (ALT)|alt|sgpt|liver',
    'SGOT (AST)|ast|sgot|liver',
    'Serum Bilirubin (Total & Direct)|bilirubin|jaundice',
    'Serum Alkaline Phosphatase|alp|alkaline phosphatase',
    'GGT|gamma gt|ggt',
    'Serum Albumin|albumin',
    'Serum Total Protein|total protein|a/g ratio',
    'Serum Amylase|amylase|pancreas',
    'Serum Lipase|lipase|pancreas',
    'AFP|alpha fetoprotein|alpha feto protein',
    // --- Kidney / minerals ---
    'RFT|renal function test|kidney function',
    'Serum Creatinine|creatinine|kidney',
    'Blood Urea|urea|bun',
    'eGFR|gfr|kidney',
    'Serum Electrolytes|na k cl|sodium|potassium|electrolyte',
    'Serum Sodium|sodium|na',
    'Serum Potassium|potassium|k',
    'Serum Calcium|calcium|ca',
    'Serum Phosphate|phosphate|phosphorus',
    'Serum Magnesium|magnesium|mg',
    'Serum Uric Acid|uric acid|gout',
    'Serum Bicarbonate|bicarbonate|hco3',
    'ABG|arterial blood gas|blood gas',
    // --- Urine / stool ---
    'Urine R/E|urine re|urine routine|urine routine examination',
    'Urine C/S|urine culture|culture sensitivity|urine cs',
    'Urine for Pus Cells|pus cell',
    '24 Hrs Urinary Protein|24 hour urine protein|proteinuria',
    '24 Hrs Urinary Creatinine Clearance|creatinine clearance',
    'Urine Pregnancy Test (UPT)|upt|pregnancy test|pregnancy',
    'Stool R/E|stool re|stool routine',
    'Stool C/S|stool culture',
    'Stool for Occult Blood|occult blood|fobt',
    'Stool for Ova & Cyst|ova cyst|worm|parasite',
    'Stool for Reducing Substances|reducing substance',
    // --- Infection / fever ---
    'Dengue NS-1|ns1|ns-1|dengue',
    'Dengue IgG/IgM|dengue igg|dengue igm|dengue antibody',
    'MP (Malaria Parasite)|mp|malaria|malaria parasite',
    'Malaria Rapid Test (ICT)|malaria ict|malaria card',
    'Widal Test|widal|typhoid',
    'Typhidot (IgM/IgG)|typhidot|typhoid',
    'Blood Culture & Sensitivity|blood culture|culture sensitivity|bc',
    'CRP|c reactive protein|c-reactive protein|inflammation',
    'Procalcitonin|pct',
    'Chikungunya IgM|chikungunya',
    'COVID-19 Rapid Antigen|covid|corona|rapid antigen',
    'COVID-19 RT-PCR|covid pcr|corona pcr|rt pcr',
    'Influenza A/B Rapid Test|influenza|flu',
    'Brucella Antibody|brucella',
    'Leptospira IgM|leptospira',
    'Kala-azar (rK39) Test|kala azar|rk39|leishmania',
    'HBsAg|hepatitis b|hbs ag',
    'Anti-HCV|hepatitis c|hcv',
    'Anti-HBs|hbs antibody',
    'HBV DNA (Quantitative)|hbv dna|hepatitis b dna',
    'HCV RNA (Quantitative)|hcv rna|hepatitis c rna',
    'Anti-HAV IgM|hepatitis a',
    'Anti-HEV IgM|hepatitis e',
    'Anti-HIV (I & II)|hiv|aids',
    'VDRL / TPHA|vdrl|tpha|syphilis',
    'Mantoux Test (MT)|mantoux|tuberculin|tb test',
    'Sputum for AFB|afb|sputum|tb',
    'GeneXpert MTB/RIF|genexpert|xpert|tb',
    'Sputum C/S|sputum culture',
    'Throat Swab C/S|throat swab',
    'Wound Swab C/S|wound swab|pus culture',
    'H. pylori Stool Antigen|h pylori|pylori|stool antigen',
    'H. pylori Breath Test|urea breath test|h pylori',
    'ASO Titre|aso|anti streptolysin',
    // --- Hormones / vitamins ---
    'TSH|thyroid stimulating hormone|thyroid',
    'Free T3|ft3|thyroid',
    'Free T4|ft4|thyroid',
    'Thyroid Profile (T3, T4, TSH)|thyroid profile|thyroid function',
    'Anti-TPO|thyroid antibody|tpo',
    'Serum Vitamin D (25-OH)|vitamin d|vit d',
    'Serum PTH|parathyroid|pth',
    'Serum Cortisol|cortisol',
    'FSH|follicle stimulating hormone',
    'LH|luteinizing hormone',
    'Prolactin|prl',
    'Serum Estradiol (E2)|estradiol|oestrogen',
    'Serum Progesterone|progesterone',
    'Serum Testosterone|testosterone',
    'AMH|anti mullerian hormone',
    'Beta-hCG (Quantitative)|beta hcg|hcg|pregnancy',
    'PSA (Total)|psa|prostate',
    'Semen Analysis|semen|sperm count',
    // --- Autoimmune / rheumatology / tumor markers ---
    'RA Factor|ra test|rheumatoid factor|rheumatoid',
    'Anti-CCP|ccp|rheumatoid',
    'ANA|antinuclear antibody',
    'Anti-dsDNA|dsdna|lupus',
    'C3 / C4|complement',
    'HLA-B27|hla b27',
    'CA-125|ca125|ovarian',
    'CA 19-9|ca19-9|pancreas',
    'CEA|carcinoembryonic antigen',
    'Biopsy (Histopathology)|biopsy|histopathology|histology',
    'FNAC|fine needle aspiration|fnac',
    'Pap Smear|pap test|cervical smear',
    // --- Cardiac ---
    'ECG|electrocardiogram|ekg|heart',
    'Echocardiogram|echo|2d echo|heart',
    'Exercise ECG (TMT)|tmt|stress test|treadmill',
    'Holter Monitoring (24 Hrs)|holter',
    'Ambulatory BP Monitoring|abpm',
    'Troponin-I|trop i|troponin|heart attack',
    'Troponin-T|trop t|troponin',
    'CK-MB|ckmb|cardiac enzyme',
    'CPK|cpk|creatine phosphokinase',
    'NT-proBNP|bnp|probnp|heart failure',
    'LDH|lactate dehydrogenase',
    'CT Coronary Angiogram|ct angiogram|coronary angiogram',
    'Coronary Angiogram (CAG)|cag|angiogram',
    'Carotid Doppler|carotid',
    'Doppler Study of Lower Limb (Arterial)|arterial doppler|leg doppler',
    'Doppler Study of Lower Limb (Venous)|venous doppler|dvt',
    // --- X-ray ---
    'X-Ray Chest (P/A View)|x-ray chest|xray chest|cxr|chest x ray',
    'X-Ray Chest (Lateral View)|chest lateral',
    'X-Ray Abdomen (Erect)|x ray abdomen|abdomen xray',
    'X-Ray KUB|kub xray',
    'X-Ray PNS (OM View)|pns|sinus|om view',
    'X-Ray Cervical Spine (B/V)|cervical spine|c spine|neck xray',
    'X-Ray Lumbosacral Spine (B/V)|ls spine|lumbar spine|back xray',
    'X-Ray Thoracic Spine (B/V)|thoracic spine',
    'X-Ray Skull (B/V)|skull',
    'X-Ray Pelvis (A/P View)|pelvis|hip',
    'X-Ray Knee Joint (B/V)|knee',
    'X-Ray Shoulder Joint|shoulder',
    'X-Ray Elbow Joint|elbow',
    'X-Ray Wrist Joint|wrist',
    'X-Ray Ankle Joint|ankle',
    'X-Ray Hand|hand',
    'X-Ray Foot|foot',
    'Barium Swallow|barium|swallow',
    'Barium Meal|barium|stomach',
    'Barium Enema|barium|colon',
    'IVU|intravenous urography|urogram',
    'HSG|hysterosalpingography',
    'Mammography|mammogram|breast',
    'DEXA Scan|bone density|bmd|dexa|osteoporosis',
    // --- Ultrasound ---
    'USG of Whole Abdomen|usg w/a|usg wa|usg|ultrasound|whole abdomen',
    'USG of Upper Abdomen|usg upper abdomen|usg|liver|gallbladder',
    'USG of Lower Abdomen|usg lower abdomen|usg|pelvis',
    'USG of KUB & Prostate|usg kub|kub|kidney|prostate',
    'USG of Pelvis|usg pelvis|uterus|ovary',
    'USG of Pregnancy Profile|usg pregnancy|obstetric usg|anomaly scan',
    'USG of Neck|usg neck|thyroid usg',
    'USG of Breast|usg breast',
    'USG of Scrotum|usg scrotum|testis',
    'USG of Thyroid|usg thyroid',
    'USG of Chest (Pleural)|usg chest|pleural effusion',
    'Fibroscan|liver stiffness|elastography',
    'Color Doppler Study|doppler',
    'Transvaginal Sonography (TVS)|tvs|transvaginal',
    // --- CT / MRI ---
    'CT Scan of Brain|ct brain|ct head|head ct',
    'CT Scan of PNS|ct pns|ct sinus',
    'CT Scan of Chest (HRCT)|hrct|ct chest',
    'CT Scan of Whole Abdomen|ct abdomen|ct w/a',
    'CT Scan of KUB|ct kub|stone',
    'CT Scan of Neck|ct neck',
    'CT Scan of Spine|ct spine',
    'MRI of Brain|mri brain|brain mri',
    'MRI of Cervical Spine|mri c spine|mri neck',
    'MRI of Lumbosacral Spine|mri ls spine|mri lumbar|mri back',
    'MRI of Knee Joint|mri knee',
    'MRI of Shoulder Joint|mri shoulder',
    'MRI of Pelvis|mri pelvis',
    'MRI of Whole Abdomen|mri abdomen',
    'MRCP|mr cholangiopancreatography',
    'MR Angiography (Brain)|mra|mr angiogram',
    'PET-CT Scan|pet ct|pet scan',
    // --- Neuro / ENT / eye / others ---
    'EEG|electroencephalogram|seizure|epilepsy',
    'EMG & NCV|nerve conduction|nct|nerve study|emg',
    'Audiometry (PTA)|audiometry|pta|hearing',
    'Tympanometry|tympanogram',
    'Nasal Endoscopy|dne|nasal endoscope',
    'Pulmonary Function Test (Spirometry)|pft|spirometry|lung function|asthma',
    'Peak Flow Meter Reading|peak flow',
    'Upper GI Endoscopy|endoscopy|ugi|gastroscopy|oges',
    'Colonoscopy|colon scope',
    'Sigmoidoscopy',
    'Bronchoscopy',
    'Cystoscopy',
    'Pleural Fluid Analysis|pleural fluid|pleural tap',
    'Ascitic Fluid Analysis|ascitic fluid|ascitic tap',
    'CSF Study|csf|lumbar puncture|lp',
    'Synovial Fluid Analysis|joint fluid',
    'Bone Marrow Study|bone marrow|bm study',
    'Fundoscopy|fundus|eye',
    'Slit Lamp Examination|slit lamp|eye',
    'OCT (Optical Coherence Tomography)|oct|retina',
    'Visual Field Test|visual field|perimetry',
    'Intraocular Pressure (IOP)|iop|glaucoma|tonometry',
    'Blood Sugar Profile (Day Long)|sugar profile|sugar series',
    'Blood Film for Microfilaria|microfilaria|filaria',
    'Fine Needle Aspiration Cytology (Thyroid)|thyroid fnac',
    'BMI & Waist Measurement|bmi|waist',
    'Oral Glucose Challenge Test (GDM)|gdm|gestational diabetes',
].filter(Boolean).map(function (line) {
    const parts = line.split('|');
    return { name: parts[0], keys: parts.map(function (x) { return x.toLowerCase(); }) };
});

const invBox = byId('investigations');
const invSuggest = byId('invSuggest');
let invList = [];
let invActive = 0;
let invRange = null;

// Cursor jei test-er ghore ache (comma / notun line diye alada) sheta ber kore
function invToken() {
    const v = invBox.value;
    const pos = invBox.selectionStart || 0;
    const s = pos === 0 ? 0 : Math.max(v.lastIndexOf(',', pos - 1), v.lastIndexOf('\n', pos - 1)) + 1;
    const next = v.slice(pos).search(/[,\n]/);
    const e = next === -1 ? v.length : pos + next;
    return { s: s, e: e, q: v.slice(s, e).trim() };
}

function invMatches(q) {
    const needle = q.toLowerCase();
    const words = needle.split(/\s+/).filter(Boolean);
    const t = invToken();
    const taken = invBox.value.slice(0, t.s).concat(',', invBox.value.slice(t.e))
        .split(/[,\n]/).map(function (x) { return x.trim().toLowerCase(); }).filter(Boolean);

    const out = [];
    TEST_CATALOG.forEach(function (it, idx) {
        if (taken.indexOf(it.name.toLowerCase()) !== -1) return;
        const hay = it.keys.join(' ');
        if (!words.every(function (w) { return hay.indexOf(w) !== -1; })) return;

        let score = 3;
        if (it.name.toLowerCase().indexOf(needle) === 0) score = 0;
        else if (it.keys.some(function (k) { return k.indexOf(needle) === 0; })) score = 1;
        else if (it.keys.some(function (k) { return (' ' + k).indexOf(' ' + needle) !== -1; })) score = 2;
        out.push({ it: it, score: score, idx: idx });
    });
    out.sort(function (a, b) { return a.score - b.score || a.idx - b.idx; });
    return out.slice(0, needle ? 12 : 40).map(function (x) { return x.it; });
}

function hideInvSuggest() { invSuggest.classList.add('hidden'); invList = []; }

function renderInvSuggest(showAll) {
    const t = invToken();
    invRange = t;
    if (t.q.length < 1 && !showAll) { hideInvSuggest(); return; }

    invList = invMatches(t.q);
    if (!invList.length) { hideInvSuggest(); return; }
    if (invActive >= invList.length) invActive = 0;

    invSuggest.innerHTML = invList.map(function (it, i) {
        return '<div class="inv-sug px-3 py-1.5 cursor-pointer text-sm ' +
            (i === invActive ? 'bg-teal-50 text-teal-800 font-semibold' : 'text-slate-700 hover:bg-teal-50') +
            '" data-i="' + i + '">🔬 ' + escHtml(it.name) + '</div>';
    }).join('');
    invSuggest.classList.remove('hidden');
}

function pickInvSuggest(i) {
    const it = invList[i];
    if (!it) return;
    const t = invToken();
    const v = invBox.value;
    let before = v.slice(0, t.s);
    if (before.trim() && !/\s$/.test(before)) before += ' ';
    const after = v.slice(t.e);
    const tail = after.trim() === '' ? ', ' : '';

    invBox.value = before + it.name + tail + after;
    const caret = (before + it.name + tail).length;
    invBox.setSelectionRange(caret, caret);
    invBox.focus();
    dirty = true;
    invActive = 0;
    hideInvSuggest();
}

// Save-er age shesh-er extra comma/space muche felbo
function cleanInvText() {
    invBox.value = invBox.value.replace(/[,\s]+$/, '');
}

invBox.addEventListener('input', function () { invActive = 0; renderInvSuggest(); });
invBox.addEventListener('click', renderInvSuggest);
invBox.addEventListener('focus', function () { renderInvSuggest(true); });
invBox.addEventListener('keyup', function (e) {
    if (e.key === 'ArrowLeft' || e.key === 'ArrowRight' || e.key === 'Home' || e.key === 'End') renderInvSuggest();
});
invBox.addEventListener('keydown', function (e) {
    if (invSuggest.classList.contains('hidden') || !invList.length) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); invActive = (invActive + 1) % invList.length; renderInvSuggest(); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); invActive = (invActive - 1 + invList.length) % invList.length; renderInvSuggest(); }
    else if (e.key === 'Enter' || e.key === 'Tab') { e.preventDefault(); pickInvSuggest(invActive); }
    else if (e.key === 'Escape') { e.preventDefault(); hideInvSuggest(); }
});
invBox.addEventListener('blur', function () { setTimeout(hideInvSuggest, 150); cleanInvText(); });
invSuggest.addEventListener('mousedown', function (e) {
    const row = e.target.closest('.inv-sug');
    if (!row) return;
    e.preventDefault();   // textarea-r focus jate na jay
    pickInvSuggest(parseInt(row.dataset.i, 10));
});
byId('prescriptionForm').addEventListener('submit', cleanInvText);

        /* ================= Patient search / select (Patient ID aware) ================= */
        // Ekoi mobile-e ekadhik rogi thakte pare, tai mobile diye shudhu ekjon dhora hoy na.
        // Doctor tālika theke sothik rogi (Patient ID shoho) bachai kore, noile "notun rogi" bachai kore.
        const patientIdInput = byId('patientIdInput');
        const serialIdInput = byId('serialIdInput');
        const patientBanner = byId('patientBanner');
        const patientMatches = byId('patientMatches');
        const patientSearchInput = byId('patientSearch');
        const patientMobileInput = byId('patientMobile');
        let lastMatches = [];
        let patientDecisionPending = false;   // same mobile-e ager rogi ache, kintu doctor ekhono bachai kore ni
        let patientSearchInFlight = null;

        function setPatientFields(p) {
            byId('patientName').value = p.name || '';
            byId('patientAge').value = p.age != null ? p.age : '';
            byId('patientWeight').value = p.weight != null ? p.weight : '';
            byId('patientGender').value = '';
            setSelect(byId('patientGender'), p.gender);
            byId('patientBlood').value = p.blood_group || '';
            byId('patientAddress').value = p.address || '';
        }

        function showBanner(mode, p) {
            if (mode === 'none') { patientBanner.className = 'hidden'; patientBanner.innerHTML = ''; return; }

            if (mode === 'existing') {
                patientBanner.className = 'mb-3 rounded-xl border px-3 py-2.5 text-sm flex items-center justify-between gap-2 bg-emerald-50 border-emerald-200 text-emerald-800';
                patientBanner.innerHTML =
                    '<div class="min-w-0"><i class="fa-solid fa-id-card-clip mr-1.5"></i><span class="font-bold">Patient ID: ' + escHtml((p && p.code) || '—') + '</span>' +
                    '<span class="text-xs ml-2">' + escHtml((p && p.name) || '') + '</span></div>' +
                    (HAS_PARENT
                        ? '<span class="text-[10px] font-semibold">Investigation visit-er rogi</span>'
                        : '<button type="button" class="change-patient text-xs font-bold underline">Change</button>');
            } else {
                patientBanner.className = 'mb-3 rounded-xl border px-3 py-2.5 text-sm flex items-center justify-between gap-2 bg-amber-50 border-amber-200 text-amber-800';
                patientBanner.innerHTML =
                    '<div><i class="fa-solid fa-user-plus mr-1.5"></i><span class="font-bold">নতুন রোগী</span>' +
                    '<span class="text-xs ml-2">Save করলে নতুন Patient ID তৈরি হবে</span></div>' +
                    '<button type="button" class="change-patient text-xs font-bold underline">Search existing</button>';
            }

            const btn = patientBanner.querySelector('.change-patient');
            if (btn) btn.addEventListener('click', clearPatient);
        }

        function selectPatient(p) {
            patientIdInput.value = p.id;
            patientDecisionPending = false;
            setPatientFields(p);
            if (p.mobile) patientMobileInput.value = p.mobile;
            showBanner('existing', p);
            patientMatches.classList.add('hidden');
            dirty = true;
        }

        function startNewPatient() {
            patientIdInput.value = '';
            patientDecisionPending = false;
            setPatientFields({});
            showBanner('new');
            patientMatches.classList.add('hidden');
            byId('patientName').focus();
        }

        function clearPatient() {
            patientIdInput.value = '';
            patientDecisionPending = false;
            setPatientFields({});
            patientMobileInput.value = '';
            showBanner('none');
            patientMatches.classList.add('hidden');
            patientSearchInput.focus();
        }

        function renderMatches(data, q, auto) {
            const list = data.patients || [];
            lastMatches = list;
            patientMatches.innerHTML = '';

            if (!list.length) {
                patientDecisionPending = false;
                if (auto) { if (!patientIdInput.value) showBanner('new'); patientMatches.classList.add('hidden'); return; }
                patientMatches.innerHTML = '<div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs text-slate-600">"' + escHtml(q) + '" দিয়ে কোনো রোগী পাওয়া যায়নি। নতুন রোগীর তথ্য নিচে দিন।</div>';
                patientMatches.classList.remove('hidden');
                if (data.type === 'mobile') { patientMobileInput.value = q; showBanner('new'); }
                return;
            }

            patientDecisionPending = true;

            const header = document.createElement('div');
            header.className = 'text-xs font-bold text-slate-600';
            header.textContent = (data.type === 'mobile' && list.length > 1)
                ? 'এই নম্বরে ' + list.length + ' জন রোগী আছে — সঠিক জনকে বেছে নিন:'
                : 'পাওয়া রোগী — নির্বাচন করুন:';
            patientMatches.appendChild(header);

            list.forEach(function (p, idx) {
                const card = document.createElement('div');
                card.className = 'border border-slate-200 rounded-xl p-3 bg-white';
                const meta = [p.age != null ? p.age + ' বছর' : '', p.gender || '', p.weight ? p.weight + ' kg' : ''].filter(Boolean).join(' · ');

                let html =
                    '<div class="flex items-start justify-between gap-2">' +
                        '<div class="min-w-0">' +
                            '<p class="text-sm font-bold text-slate-800">' + escHtml(p.name) +
                                ' <span class="ml-1 text-[10px] font-bold bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded">' + escHtml(p.code || '') + '</span></p>' +
                            '<p class="text-[11px] text-slate-500">' + escHtml(meta) + (p.mobile ? ' · ' + escHtml(p.mobile) : '') + '</p>' +
                            '<p class="text-[11px] text-slate-400">' + (p.last_visit ? 'শেষ ভিজিট: ' + escHtml(p.last_visit) + ' · ' : '') + escHtml(p.visits) + ' টি ভিজিট</p>' +
                        '</div>' +
                        '<button type="button" class="pick-patient shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg" data-idx="' + idx + '">Select</button>' +
                    '</div>';

                (p.pending || []).forEach(function (x) {
                    html +=
                        '<div class="mt-2 bg-amber-50 border border-amber-200 rounded-lg p-2 flex items-center justify-between gap-2">' +
                            '<div class="min-w-0">' +
                                '<p class="text-[11px] font-semibold text-amber-800">⏳ Report-এর অপেক্ষায় · ' + escHtml(x.date) + '</p>' +
                                '<p class="text-[11px] text-amber-700 truncate">' + escHtml(x.investigations || '') + '</p>' +
                            '</div>' +
                            '<a href="' + escHtml(x.url) + '" class="shrink-0 bg-amber-500 hover:bg-amber-600 text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg">Continue with Reports</a>' +
                        '</div>';
                });

                card.innerHTML = html;
                patientMatches.appendChild(card);
            });

            const newBtn = document.createElement('button');
            newBtn.type = 'button';
            newBtn.className = 'w-full border-2 border-dashed border-slate-300 hover:border-indigo-400 hover:bg-indigo-50 text-slate-600 text-xs font-bold rounded-xl py-2.5';
            newBtn.innerHTML = '<i class="fa-solid fa-user-plus mr-1"></i> ' +
                (data.type === 'mobile' ? 'এটি অন্য একজন — নতুন রোগী হিসেবে যোগ করুন' : 'নতুন রোগী যোগ করুন');
            newBtn.addEventListener('click', startNewPatient);
            patientMatches.appendChild(newBtn);

            patientMatches.querySelectorAll('.pick-patient').forEach(function (b) {
                b.addEventListener('click', function () { selectPatient(lastMatches[parseInt(b.dataset.idx, 10)]); });
            });

            patientMatches.classList.remove('hidden');
        }

        function searchPatients(q, auto) {
            q = (q || '').trim();
            if (!q) return Promise.resolve();

            const p = (async function () {
                try {
                    const res = await fetch(LOOKUP_URL + '?q=' + encodeURIComponent(q), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!res.ok) throw new Error('lookup failed');
                    renderMatches(await res.json(), q, !!auto);
                } catch (err) {
                    patientDecisionPending = false;
                    if (!auto) {
                        patientMatches.innerHTML = '<div class="bg-rose-50 border border-rose-200 rounded-lg p-3 text-xs text-rose-700">রোগী খুঁজতে সমস্যা হয়েছে, আবার চেষ্টা করুন।</div>';
                        patientMatches.classList.remove('hidden');
                    }
                }
            })();

            patientSearchInFlight = p;
            p.finally(function () { if (patientSearchInFlight === p) patientSearchInFlight = null; });
            return p;
        }

        // Mobile field-er pasher search button / Enter
        byId('lookupBtn').addEventListener('click', function () { searchPatients(patientMobileInput.value, false); });
        patientMobileInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); searchPatients(patientMobileInput.value, false); }
        });

        // Mobile likhe field theke berolei auto check: ei number-e ager rogi thakle tālika dekhabe
        patientMobileInput.addEventListener('change', function () {
            if (HAS_PARENT || patientIdInput.value) return;
            patientDecisionPending = false;
            searchPatients(patientMobileInput.value, true);
        });

        // "Find Existing Patient" box (Mobile ba Patient ID)
        byId('patientSearchBtn').addEventListener('click', function () { searchPatients(patientSearchInput.value, false); });
        patientSearchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); searchPatients(patientSearchInput.value, false); }
        });

        // Investigation visit theke ashle patient fix, search lukano
        if (HAS_PARENT) { byId('patientSearchWrap').classList.add('hidden'); }
        if (INIT_PATIENT && INIT_PATIENT.id) { showBanner('existing', INIT_PATIENT); }

        /* ================= Serial queue: "Start" → rogir info auto load ================= */
        const queueList = byId('queueList');
        const DONE_STATUS = ['done', 'cancelled'];
        const isDone = (s) => DONE_STATUS.indexOf(s) !== -1;
        let activeSerialId = serialIdInput.value ? String(serialIdInput.value) : '';

        function renderQueue() {
            if (!queueList) return;
            byId('queueCount').textContent = SERIALS.filter(s => !isDone(s.status)).length;
            queueList.innerHTML = SERIALS.map(function (s) {
                const done = isDone(s.status);
                const active = String(s.id) === activeSerialId;
                const meta = [s.age != null && s.age !== '' ? s.age + ' বছর' : '', s.gender || '', s.mobile || ''].filter(Boolean).join(' · ');
                const vit = [s.bp ? 'BP ' + s.bp : '', s.pulse ? 'P ' + s.pulse : '', s.temperature ? s.temperature + '°F' : '', s.weight ? s.weight + 'kg' : ''].filter(Boolean).join(' · ');
                return '<div class="flex items-center gap-3 rounded-xl border px-3 py-2 ' +
                        (active ? 'border-indigo-500 bg-indigo-50 ring-2 ring-indigo-100' : 'border-slate-100 bg-white hover:border-indigo-200') + (done ? ' opacity-60' : '') + '">' +
                    '<div class="w-9 h-9 shrink-0 rounded-lg flex items-center justify-center text-sm font-bold ' + (active ? 'bg-indigo-600 text-white' : 'bg-indigo-50 text-indigo-700') + '">' + escHtml(s.no != null ? s.no : '#') + '</div>' +
                    '<div class="min-w-0 flex-1">' +
                        '<p class="text-sm font-bold text-slate-800 truncate">' + escHtml(s.name || 'নামহীন রোগী') +
                            (s.code ? ' <span class="ml-1 text-[10px] font-bold bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded">' + escHtml(s.code) + '</span>' : '') + '</p>' +
                        '<p class="text-[11px] text-slate-400 truncate">' + escHtml(meta) + '</p>' +
                        (vit ? '<p class="text-[11px] font-semibold text-teal-700 truncate">' + escHtml(vit) + '</p>' : '') +
                    '</div>' +
                    (done ? '<span class="shrink-0 text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">সম্পন্ন</span>'
                          : '<button type="button" class="q-start shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg" data-id="' + escHtml(s.id) + '"><i class="fa-solid fa-play mr-1"></i>' + (active ? 'চলছে' : 'Start') + '</button>') +
                '</div>';
            }).join('');
        }

        function clearVisit() {
            ['vBp', 'vPulse', 'vTemp', 'symptomsInput', 'riskInput', 'diagnosisInput', 'investigations', 'investigationFindings', 'adviceInput', 'followupDate']
                .forEach(id => { byId(id).value = ''; });
            prescriptionList.innerHTML = '';
            checkEmpty();
            ['patternPanel', 'aiMedPanel', 'conflictBox'].forEach(id => byId(id).classList.add('hidden'));
            byId('diagSuggestions').innerHTML = '';
            if (typeof renderAdvicePresets === 'function') renderAdvicePresets();
        }

        async function startSerial(s) {
            if (!s) return;
            const switching = String(s.id) !== activeSerialId;

            if (switching && dirty && (val('patientName') || medRowCount() > 0)) {
                const r = await appDialog({
                    type: 'warning', title: 'অন্য রোগী শুরু করবেন?',
                    message: 'বর্তমান ফর্মের তথ্য (রোগী' + (medRowCount() ? ' ও ' + medRowCount() + 'টি ঔষধ' : '') + ') মুছে গিয়ে "' + (s.name || 'নতুন রোগী') + '"-এর তথ্য বসবে।',
                    confirmText: 'হ্যাঁ, শুরু করুন', cancelText: 'না',
                });
                if (r !== 'confirm') return;
            }
            if (switching) clearVisit();

            // Patient info
            setPatientFields({ name: s.name, age: s.age, weight: s.weight, gender: s.gender, blood_group: s.blood_group, address: s.address });
            patientMobileInput.value = s.mobile || '';
            patientIdInput.value = s.patient_id || '';
            patientDecisionPending = false;
            patientMatches.classList.add('hidden');
            if (s.patient_id) showBanner('existing', { code: s.code, name: s.name });
            else showBanner('new');

            // Vitals + complaints
            if (s.bp) byId('vBp').value = s.bp;
            if (s.pulse) byId('vPulse').value = s.pulse;
            if (s.temperature) byId('vTemp').value = s.temperature;
            if (s.reason) byId('symptomsInput').value = s.reason;

            // Serial banner
            byId('serialBanner').classList.remove('hidden');
            byId('serialBannerTitle').textContent = 'সিরিয়াল #' + (s.no != null ? s.no : '') + ' · ' + (s.name || '');
            byId('serialBannerReason').textContent = s.reason ? ' সমস্যা: ' + s.reason : '';

            activeSerialId = String(s.id);
            serialIdInput.value = s.id;

            // Server-e serial "চলছে" kore dei (ager chalu thaka ta abar "অপেক্ষায়"), jate doctor-er kiu-te thik dekhay
            SERIALS.forEach(x => { if (x.status === 'in_progress' && String(x.id) !== String(s.id)) x.status = 'waiting'; });
            s.status = 'in_progress';
            fetch(START_URL.replace('__ID__', s.id), {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
            }).catch(() => {});

            renderQueue();
            dirty = true;

            goStep(2, { noScroll: true });
            byId('stepClinical').scrollIntoView({ behavior: 'smooth', block: 'start' });
            byId('symptomsInput').focus({ preventScroll: true });
        }

        if (queueList) {
            queueList.addEventListener('click', function (e) {
                const b = e.target.closest('.q-start'); if (!b) return;
                startSerial(SERIALS.find(x => String(x.id) === String(b.dataset.id)));
            });
            renderQueue();
        }

        /* ================= Favorites ================= */
        fetch(TOP_MED_URL)
            .then(res => res.json())
            .then(data => {
                if (!Array.isArray(data) || !data.length) return;
                byId('favWrap').style.display = 'block';
                byId('favChips').innerHTML = data.map(m => `
                    <span data-id="${escHtml(m.id)}" data-label="${escHtml(m.label)}" data-generic="${escHtml(m.generic_name || '')}" class="fav-chip bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-1 rounded-lg text-xs font-medium cursor-pointer hover:bg-indigo-100">💊 ${escHtml(m.label)}</span>
                `).join('');
                document.querySelectorAll('.fav-chip').forEach(chip => {
                    chip.addEventListener('click', function () {
                        medSearch.value = this.dataset.label;
                        medId.value = this.dataset.id;
                        pendingGeneric = (this.dataset.generic || '').toLowerCase();
                        (medType.value === 'Syrup' ? medDoseText : medDose).focus();
                    });
                });
            })
            .catch(() => {});

        /* ================= Usual Treatment (doctor's own pattern) ================= */
        let patternDebounce;
        byId('diagnosisInput').addEventListener('input', function () {
            clearTimeout(patternDebounce);
            const diagnosis = this.value.trim();
            const panel = byId('patternPanel');
            if (diagnosis.length < 3 || currentMode() === 'investigation_only') {
                panel.classList.add('hidden');
                return;
            }

            patternDebounce = setTimeout(() => {
                fetch(`${PATTERN_URL}?diagnosis=${encodeURIComponent(diagnosis)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!Array.isArray(data) || !data.length) { panel.classList.add('hidden'); return; }

                        const list = byId('patternList');
                        list.innerHTML = data.map(m => `
                            <div class="bg-white border border-emerald-100 rounded-lg p-3 flex justify-between items-center gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800">${escHtml(m.label)} <span class="text-[9px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full ml-1">${escHtml(m.used_count)} বার ব্যবহৃত</span></p>
                                    <p class="text-[11px] text-slate-400">${escHtml(m.generic_name)} • ${escHtml(m.dose)} • ${escHtml(m.duration || '')} • ${escHtml(m.instruction || '')}</p>
                                </div>
                                <button type="button" class="pattern-add-btn shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg"
                                    data-id="${escHtml(m.medicine_id || '')}" data-name="${escHtml(m.medicine_name)}" data-type="${escHtml(m.medicine_type)}" data-generic="${escHtml(m.generic_name || '')}"
                                    data-dose="${escHtml(m.dose)}" data-duration="${escHtml(m.duration || '')}" data-instruction="${escHtml(m.instruction || '')}">
                                    + Add
                                </button>
                            </div>
                        `).join('');
                        panel.classList.remove('hidden');

                        list.querySelectorAll('.pattern-add-btn').forEach(b => {
                            b.addEventListener('click', function () {
                                medSearch.value = this.dataset.name;
                                medId.value = this.dataset.id;
                                pendingGeneric = (this.dataset.generic || '').toLowerCase();
                                setSelect(medType, this.dataset.type);
                                syncDoseField();
                                if (medType.value === 'Syrup') medDoseText.value = this.dataset.dose;
                                else medDose.value = this.dataset.dose;
                                byId('med-duration').value = this.dataset.duration;
                                byId('med-instruction').value = this.dataset.instruction;
                                addMedicineRow();
                            });
                        });
                    })
                    .catch(() => panel.classList.add('hidden'));
            }, 500);
        });

        /* ================= Add medicine row ================= */
        function parseDose(str) {
            const bn = { '০':'0','১':'1','২':'2','৩':'3','৪':'4','৫':'5','৬':'6','৭':'7','৮':'8','৯':'9' };
            const ascii = String(str || '').replace(/[০-৯]/g, d => bn[d]);
            const parts = ascii.split('+').map(v => { const x = parseFloat(v); return isNaN(x) ? 0 : x; });
            while (parts.length < 3) parts.push(0);
            return parts.slice(0, 3);
        }

        const PREFIX_MAP = { Tablet: 'Tab.', Capsule: 'Cap.', Syrup: 'Syr.', Injection: 'Inj.', Drop: 'Drop', Cream: 'Cream', Inhaler: 'Inhaler', Other: '' };
        let medIdx = 0;

        function appendMedicineRow(e) {
            const emptyRow = byId('empty-row');
            if (emptyRow) emptyRow.remove();

            const index = medIdx++;
            const nums = e.doseText ? [0, 0, 0] : parseDose(e.dose);
            const displayLabel = (PREFIX_MAP[e.type] ? PREFIX_MAP[e.type] + ' ' : '') + e.name;

            const tr = document.createElement('tr');
            tr.dataset.generic = (e.generic || '').toLowerCase();
            tr.innerHTML = `
                <td class="py-3 font-semibold text-slate-900">
                    💊 ${escHtml(displayLabel)}
                    ${e.id ? '' : '<span class="ml-1 text-[10px] font-bold bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full" title="Medicine তালিকা (DB) থেকে নির্বাচন করা হয়নি">DB-তে নেই</span>'}
                    <input type="hidden" name="medicines[${index}][medicine_id]" value="${escHtml(e.id)}">
                    <input type="hidden" name="medicines[${index}][medicine_name]" value="${escHtml(e.name)}">
                    <input type="hidden" name="medicines[${index}][medicine_type]" value="${escHtml(e.type)}">
                </td>
                <td data-label="Dose" class="py-3 text-slate-600">
                    <span class="bg-slate-100 px-2 py-0.5 rounded text-xs whitespace-nowrap">${escHtml(e.doseText || e.dose)}</span>
                    <input type="hidden" name="medicines[${index}][dose_morning]" value="${nums[0]}">
                    <input type="hidden" name="medicines[${index}][dose_noon]" value="${nums[1]}">
                    <input type="hidden" name="medicines[${index}][dose_night]" value="${nums[2]}">
                    <input type="hidden" name="medicines[${index}][dose_text]" value="${escHtml(e.doseText || '')}">
                </td>
                <td data-label="Duration" class="py-3 text-slate-600">
                    ${escHtml(e.duration)}
                    <input type="hidden" name="medicines[${index}][duration]" value="${escHtml(e.duration)}">
                </td>
                <td data-label="Instruction" class="py-3 text-slate-600">
                    ${escHtml(e.instruction)}
                    <input type="hidden" name="medicines[${index}][instruction]" value="${escHtml(e.instruction)}">
                </td>
                <td class="py-3 text-right">
                    <button type="button" onclick="this.closest('tr').remove(); checkEmpty(); dirty = true;" class="text-rose-500 hover:text-rose-700 p-1"><i class="fa-solid fa-trash-can"></i></button>
                </td>
            `;
            prescriptionList.appendChild(tr);
        }

        async function addMedicineRow() {
            const name = medSearch.value.trim();
            const id = medId.value;
            const type = medType.value;
            const isSyrup = type === 'Syrup';
            const dose = isSyrup ? '' : medDose.value.trim();
            const doseText = isSyrup ? medDoseText.value.trim() : '';
            const duration = byId('med-duration').value.trim();
            const instruction = byId('med-instruction').value.trim();

            if (!name) {
                await appDialog({ type: 'warning', title: 'ঔষধের নাম দিন', message: 'আগে ঔষধের নাম লিখে নিচে আসা তালিকা থেকে নির্বাচন করুন।' });
                medSearch.focus(); return;
            }
            if (!dose && !doseText) {
                await appDialog({ type: 'warning', title: 'ডোজ দিন', message: '"' + name + '" এর ডোজ লিখুন, অথবা Dose ঘরে ট্যাপ করে তালিকা থেকে বাছুন।' });
                (isSyrup ? medDoseText : medDose).focus(); return;
            }
            if (!id) {
                const r = await appDialog({
                    type: 'warning', title: 'ঔষধটি আপনার তালিকায় নেই',
                    message: '"' + name + '" তালিকা (DB) থেকে নির্বাচন করা হয়নি। এটি যোগ করলে AI Conflict Check ও usage tracking-এ ধরা পড়বে না।',
                    confirmText: 'তবুও যোগ করুন', cancelText: 'বাতিল',
                });
                if (r !== 'confirm') { medSearch.focus(); return; }
            }
            if (id && getMedicineIds().indexOf(parseInt(id, 10)) !== -1) {
                const r = await appDialog({
                    type: 'warning', title: 'ঔষধটি ইতিমধ্যে যোগ করা আছে',
                    message: '"' + name + '" তালিকায় আগেই আছে। আবার যোগ করবেন?',
                    confirmText: 'আবার যোগ করুন', cancelText: 'বাতিল',
                });
                if (r !== 'confirm') return;
            } else if (pendingGeneric) {
                const same = Array.from(prescriptionList.querySelectorAll('tr')).find(tr => tr.dataset.generic && tr.dataset.generic === pendingGeneric);
                if (same) {
                    const r = await appDialog({
                        type: 'warning', title: 'একই generic আবার?',
                        message: 'তালিকায় আগেই একই generic (' + pendingGeneric + ') আছে। দুটি ঔষধ একসাথে দিলে ডোজ দ্বিগুণ হয়ে যেতে পারে।',
                        confirmText: 'তবুও যোগ করুন', cancelText: 'বাতিল',
                    });
                    if (r !== 'confirm') return;
                }
            }

            appendMedicineRow({ id, name, type, dose, doseText, duration, instruction, generic: pendingGeneric });
            dirty = true;
            rxToast('✓ ঔষধ যোগ হয়েছে (মোট ' + medRowCount() + 'টি)');

            medSearch.value = ''; medId.value = ''; pendingGeneric = '';
            medType.value = 'Tablet';
            syncDoseField();
            medDose.value = '';
            medDoseText.value = '';
            byId('med-duration').value = '';
            byId('med-instruction').value = '';
            medSearch.focus();
        }

        function checkEmpty() {
            if (prescriptionList.children.length === 0) {
                prescriptionList.innerHTML = `<tr id="empty-row"><td colspan="5" class="text-xs text-slate-400 italic text-center py-8">No medications added yet.</td></tr>`;
            }
        }

        // Validation fail hole ager ঔষধ গুলো ফিরিয়ে আনা
        (OLD_MEDS || []).forEach(function (m) {
            const doseText = m.dose_text || '';
            const num = v => { const x = parseFloat(v); return isNaN(x) ? 0 : x; };
            appendMedicineRow({
                id: m.medicine_id || '', name: m.medicine_name || '', type: m.medicine_type || 'Tablet',
                dose: doseText ? '' : [num(m.dose_morning), num(m.dose_noon), num(m.dose_night)].join('+'),
                doseText: doseText, duration: m.duration || '', instruction: m.instruction || '', generic: '',
            });
        });

        /* ================= AI: Conflict Check ================= */
        const CONFLICT_STYLE = {
            contraindicated: { box: 'bg-rose-50 border-rose-300 text-rose-900',     tag: 'bg-rose-600 text-white',   label: 'একসাথে দেওয়া নিষেধ' },
            major:           { box: 'bg-orange-50 border-orange-300 text-orange-900', tag: 'bg-orange-600 text-white', label: 'গুরুতর (Major)' },
            moderate:        { box: 'bg-amber-50 border-amber-300 text-amber-900',   tag: 'bg-amber-500 text-white',  label: 'মাঝারি (Moderate)' },
            minor:           { box: 'bg-sky-50 border-sky-300 text-sky-900',         tag: 'bg-sky-600 text-white',    label: 'সামান্য (Minor)' },
        };
        const AI_CONFLICT_NOTE = 'এটি AI-এর বিশ্লেষণ; AI ভুল করতে পারে বা কিছু বাদ দিতে পারে। চূড়ান্ত সিদ্ধান্ত চিকিৎসকের।';
        let conflictSignature = null;
        let conflictResult = null;

        function getMedicineIds() {
            return Array.from(document.querySelectorAll('#prescription-list input[name$="[medicine_id]"]'))
                .map(i => parseInt(i.value, 10))
                .filter(Boolean);
        }

        function medSignature(ids) {
            return ids.slice().sort((a, b) => a - b).join(',');
        }

        function renderConflicts(data, unchecked) {
            const box = byId('conflictBox');
            const uncheckedNote = unchecked > 0
                ? `<p class="text-[11px] text-slate-500 mt-2">⚠ ${unchecked}টি ঔষধ আপনার DB-র তালিকা থেকে নির্বাচন করা হয়নি, তাই সেগুলো চেক হয়নি।</p>`
                : '';

            if (data.has_warnings) {
                box.innerHTML = `
                    <div class="border border-rose-200 bg-white rounded-xl p-4">
                        <p class="text-sm font-bold text-rose-700 mb-3"><i class="fa-solid fa-robot mr-1.5"></i> AI ${data.warnings.length}টি সতর্কতা পেয়েছে</p>
                        <div class="space-y-2">
                            ${data.warnings.map(w => {
                                const s = CONFLICT_STYLE[w.severity] || CONFLICT_STYLE.moderate;
                                return `
                                <div class="border rounded-lg p-3 ${s.box}">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-bold">${escHtml(w.pair)}</span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${s.tag}">${s.label}</span>
                                        ${w.type === 'duplicate' ? '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-600 text-white">একই ধরনের ঔষধ</span>' : ''}
                                    </div>
                                    <p class="text-xs mt-1.5">${escHtml(w.effect)}</p>
                                    ${w.recommendation ? `<p class="text-xs mt-1"><b>পরামর্শ:</b> ${escHtml(w.recommendation)}</p>` : ''}
                                </div>`;
                            }).join('')}
                        </div>
                        <p class="text-[11px] text-slate-500 mt-3 italic">${escHtml(AI_CONFLICT_NOTE)}</p>
                        ${uncheckedNote}
                    </div>`;
            } else {
                box.innerHTML = `
                    <div class="border border-emerald-200 bg-emerald-50 rounded-xl p-4">
                        <p class="text-sm font-bold text-emerald-800"><i class="fa-solid fa-circle-check mr-1.5"></i> চমৎকার! AI নির্বাচিত ${data.checked}টি ঔষধের মধ্যে কোনো উল্লেখযোগ্য conflict পায়নি।</p>
                        <p class="text-[11px] text-emerald-700 mt-1 italic">${escHtml(AI_CONFLICT_NOTE)}</p>
                        ${uncheckedNote}
                    </div>`;
            }
            box.classList.remove('hidden');
        }

        async function checkConflicts() {
            const ids = getMedicineIds();
            const totalRows = medRowCount();

            if (ids.length < 2) {
                conflictSignature = null; conflictResult = null;
                await appDialog({ type: 'info', title: 'কমপক্ষে ২টি ঔষধ লাগবে', message: 'Conflict দেখতে তালিকা (DB) থেকে নির্বাচন করা অন্তত ২টি ঔষধ যোগ করুন।' });
                return;
            }

            const btn = byId('conflictBtn');
            const original = btn.innerHTML;
            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-wait');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> AI চেক করছে...';

            try {
                const res = await fetch(CHECK_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({
                        medicine_ids: ids,
                        age: byId('patientAge').value,
                        gender: byId('patientGender').value,
                        risk_factors: val('riskInput'),
                        diagnosis: val('diagnosisInput'),
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.error || data.message || ('Request failed (status ' + res.status + ')'));
                conflictSignature = medSignature(ids);
                conflictResult = data;
                renderConflicts(data, totalRows - ids.length);
            } catch (err) {
                console.error(err);
                await appDialog({ type: 'danger', title: 'AI Conflict Check ব্যর্থ হয়েছে', message: err.message || 'আবার চেষ্টা করুন।' });
            } finally {
                btn.disabled = false;
                btn.classList.remove('opacity-70', 'cursor-wait');
                btn.innerHTML = original;
            }
        }

        // Medicine list bodlale ager conflict result ar valid noy — lukiye din
        new MutationObserver(function () {
            if (conflictSignature !== null && medSignature(getMedicineIds()) !== conflictSignature) {
                byId('conflictBox').classList.add('hidden');
                conflictSignature = null; conflictResult = null;
            }
        }).observe(prescriptionList, { childList: true, subtree: true });

        /* ================= AI: Suggest Diagnosis ================= */
        async function suggestDiagnosis() {
            const symptoms = val('symptomsInput');
            if (!symptoms) {
                await appDialog({ type: 'info', title: 'আগে উপসর্গ লিখুন', message: 'AI Diagnosis পরামর্শের জন্য "Clinical Complaints" ঘরে রোগীর উপসর্গ লিখুন।' });
                byId('symptomsInput').focus(); return;
            }

            const btn = byId('diagBtn');
            const original = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Thinking...';

            try {
                const res = await fetch(AI_DIAG_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: JSON.stringify({
                        symptoms,
                        age: byId('patientAge').value,
                        gender: byId('patientGender').value,
                        bp: val('vBp'),
                        pulse: val('vPulse'),
                        temperature: val('vTemp'),
                        risk_factors: val('riskInput'),
                    }),
                });
                const text = await res.text();
                let data;
                try { data = JSON.parse(text); }
                catch (e) { throw new Error(`Server didn't return JSON (status ${res.status}). ` + text.slice(0, 200)); }
                if (!res.ok && !data.error) throw new Error(`Request failed (status ${res.status}).`);
                if (data.error) throw new Error(data.error);

                const list = data.diagnoses || [];
                const wrap = byId('diagSuggestions');
                wrap.innerHTML = list.map(d => `
                    <span class="ai-diag-chip bg-purple-50 text-purple-700 border border-purple-200 px-2.5 py-1 rounded-lg text-xs font-medium cursor-pointer hover:bg-purple-100" data-val="${escHtml(d)}">✨ ${escHtml(d)}</span>
                `).join('');
                wrap.querySelectorAll('.ai-diag-chip').forEach(chip => {
                    chip.addEventListener('click', function () {
                        const dx = byId('diagnosisInput');
                        dx.value = this.dataset.val;
                        dx.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                });
                if (!list.length) await appDialog({ type: 'info', title: 'কোনো পরামর্শ পাওয়া যায়নি', message: 'AI এই উপসর্গ থেকে কোনো Diagnosis দেয়নি। উপসর্গ আরও বিস্তারিত লিখে আবার চেষ্টা করুন।' });
            } catch (err) {
                console.error(err);
                await appDialog({ type: 'danger', title: 'AI suggestion ব্যর্থ হয়েছে', message: err.message || 'আবার চেষ্টা করুন।' });
            } finally {
                btn.disabled = false; btn.innerHTML = original;
            }
        }

        /* ================= AI: Generate Medicines ================= */
        async function generateAiMedicines() {
            const diagnosis = val('diagnosisInput');
            const symptoms = val('symptomsInput');
            if (!diagnosis) {
                await appDialog({ type: 'info', title: 'আগে Diagnosis লিখুন', message: 'AI ঔষধ পরামর্শের জন্য Diagnosis লিখুন (বা "Suggest Diagnosis (AI)" ব্যবহার করুন)।' });
                goStep(3, { noScroll: true });
                byId('diagnosisInput').focus(); return;
            }

            const btn = byId('aiMedBtn');
            const original = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';

            try {
                const res = await fetch(AI_MED_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF_TOKEN },
                    body: JSON.stringify({
                        diagnosis,
                        symptoms,
                        age: byId('patientAge').value,
                        gender: byId('patientGender').value,
                        risk_factors: val('riskInput'),
                    }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || data.error) throw new Error(data.error || data.message || ('Request failed (status ' + res.status + ')'));

                const meds = data.medicines || [];
                if (!meds.length) {
                    await appDialog({ type: 'info', title: 'কোনো ঔষধ পরামর্শ নেই', message: 'AI এই Diagnosis-এর জন্য কোনো ঔষধ সাজেস্ট করেনি।' });
                    return;
                }

                const list = byId('aiMedList');
                list.innerHTML = meds.map((m) => `
                    <div class="bg-white border border-purple-100 rounded-lg p-3 flex justify-between items-center gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800">${escHtml(m.label)} ${m.is_sponsored ? '<span class="text-[9px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full ml-1"><i class="fa-solid fa-star"></i> Sponsored</span>' : ''}</p>
                            <p class="text-[11px] text-slate-400">${escHtml(m.generic_name)} • ${escHtml(m.dose)} • ${escHtml(m.duration)} • ${escHtml(m.instruction)}</p>
                        </div>
                        <button type="button" class="ai-add-btn shrink-0 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold px-3 py-1.5 rounded-lg"
                            data-id="${escHtml(m.medicine_id || '')}" data-label="${escHtml(m.label)}" data-generic="${escHtml(m.generic_name || '')}" data-dose="${escHtml(m.dose)}" data-duration="${escHtml(m.duration)}" data-instruction="${escHtml(m.instruction)}">
                            + Add
                        </button>
                    </div>
                `).join('');

                list.querySelectorAll('.ai-add-btn').forEach(b => {
                    b.addEventListener('click', function () {
                        medSearch.value = this.dataset.label;
                        medId.value = this.dataset.id;
                        pendingGeneric = (this.dataset.generic || '').toLowerCase();
                        syncDoseField();
                        (medType.value === 'Syrup' ? medDoseText : medDose).value = this.dataset.dose;
                        byId('med-duration').value = this.dataset.duration;
                        byId('med-instruction').value = this.dataset.instruction;
                        addMedicineRow();
                    });
                });

                lastAiAdvice = data.advice || '';
                byId('aiAdviceText').textContent = lastAiAdvice;
                byId('aiAdviceBox').classList.toggle('hidden', !lastAiAdvice);
                byId('aiMedPanel').classList.remove('hidden');
            } catch (err) {
                console.error(err);
                await appDialog({ type: 'danger', title: 'AI suggestion ব্যর্থ হয়েছে', message: err.message || 'আবার চেষ্টা করুন।' });
            } finally {
                btn.disabled = false; btn.innerHTML = original;
            }
        }

        function useAiAdvice() {
            const a = byId('adviceInput');
            a.value = a.value ? (a.value + '\n' + lastAiAdvice) : lastAiAdvice;
            dirty = true;
            if (typeof renderAdvicePresets === 'function') renderAdvicePresets();
        }

        /* ================= Submit: sob important popup ekhane ================= */
        async function preSubmitChecks() {
            const inv = currentMode() === 'investigation_only';

            if (patientSearchInFlight) { await patientSearchInFlight; }

            if (!HAS_CHAMBER) {
                await showNoChamberDialog();
                return false;
            }

            if (!val('patientMobile') || !val('patientName')) {
                await appDialog({ type: 'warning', title: 'রোগীর তথ্য অসম্পূর্ণ', message: 'রোগীর নাম ও মোবাইল নম্বর দিতে হবে।' });
                goStep(1, { noScroll: true });
                (val('patientMobile') ? byId('patientName') : patientMobileInput).focus();
                byId('stepPatient').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            // Ei number-e ager rogi ache kintu doctor sothik rogi/notun rogi bachai kore ni
            if (patientDecisionPending && !patientIdInput.value) {
                await appDialog({ type: 'warning', title: 'রোগী নির্বাচন করুন', message: 'এই নম্বরে আগে থেকেই রোগী আছে। তালিকা থেকে সঠিক রোগী নির্বাচন করুন, অথবা "নতুন রোগী হিসেবে যোগ করুন" বাছুন।' });
                goStep(1, { noScroll: true });
                patientMatches.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            if (inv) {
                if (!val('investigations')) {
                    await appDialog({ type: 'warning', title: 'Investigation লিখুন', message: 'Investigation Advice দিতে হলে অন্তত একটি পরীক্ষা লিখুন।' });
                    goStep(3, { noScroll: true }); byId('investigations').focus(); return false;
                }
            } else if (medRowCount() === 0) {
                await appDialog({ type: 'warning', title: 'কোনো ঔষধ যোগ করা হয়নি', message: 'Full Prescription-এ অন্তত একটি ঔষধ যোগ করতে হবে।' });
                goStep(4, { noScroll: true });
                byId('medicineCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            const charge = currentCharge();
            if (BALANCE < charge) {
                await appDialog({
                    type: 'danger', title: 'Wallet ব্যালেন্স যথেষ্ট নয়',
                    message: 'এই Save-এর জন্য ' + money(charge) + ' লাগবে, কিন্তু আপনার ব্যালেন্স ' + money(BALANCE) + '। Super Admin দিয়ে Wallet রিচার্জ করান।',
                });
                return false;
            }

            if (!inv) {
                const ids = getMedicineIds();
                const fresh = conflictResult && conflictSignature === medSignature(ids);

                if (ids.length >= 2 && !fresh && ASK_CONFLICT_CHECK) {
                    const r = await appDialog({
                        type: 'warning', title: 'Conflict Check করা হয়নি',
                        message: 'আপনি ' + ids.length + 'টি ঔষধ দিয়েছেন, কিন্তু একে অপরের সাথে conflict আছে কিনা AI দিয়ে দেখা হয়নি। এখন দেখে নেবেন?',
                        confirmText: 'এখন Check করি', extraText: 'এভাবেই Save করুন',
                    });
                    if (r === 'confirm') {
                        goStep(4, { noScroll: true });
                        byId('conflictBtn').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        checkConflicts();
                        return false;
                    }
                    if (r === 'cancel') return false;
                }

                if (fresh && conflictResult.has_warnings) {
                    const serious = (conflictResult.warnings || []).filter(w => w.severity === 'contraindicated' || w.severity === 'major');
                    if (serious.length) {
                        const r = await appDialog({
                            type: 'danger', title: serious.length + 'টি গুরুতর Conflict আছে',
                            message: 'AI নিচের গুরুতর সমস্যাগুলো পেয়েছে। তবুও Save করবেন?',
                            items: serious.map(w => w.pair + ' — ' + w.effect),
                            confirmText: 'তবুও Save করুন', cancelText: 'ঔষধ দেখি',
                        });
                        if (r !== 'confirm') { goStep(4, { noScroll: true }); byId('conflictBox').scrollIntoView({ behavior: 'smooth', block: 'center' }); return false; }
                    }
                }
            }

            if (CONFIRM_BEFORE_SAVE) {
                const r = await appDialog({
                    type: 'info', title: 'Save করে প্রিন্ট করবেন?',
                    message: 'Wallet থেকে ' + money(charge) + ' কাটা হবে। Save-এর পর ব্যালেন্স হবে ' + money(BALANCE - charge) + '।',
                    confirmText: 'Save & Print', cancelText: 'ফিরে যাই',
                });
                if (r !== 'confirm') return false;
            }
            return true;
        }

        const rxForm = byId('prescriptionForm');
        rxForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (submitting) return;
            const btn = byId('submitBtn');
            btn.disabled = true;
            const ok = await preSubmitChecks();
            if (!ok) { btn.disabled = false; return; }

            submitting = true;
            dirty = false;
            byId('submitLabel').textContent = 'Saving...';
            rxForm.submit();   // form.submit() 'submit' event fire kore na, tai loop hobe na
        });

        // Enter chaple form submit hobe na (textarea/button chhara)
        rxForm.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter') return;
            const t = e.target;
            if (t.tagName === 'TEXTAREA' || t.tagName === 'BUTTON' || t.type === 'submit') return;
            e.preventDefault();
            if (t === medSearch) return;
            if (t.id === 'med-dose' || t.id === 'med-dose-text' || t.id === 'med-duration' || t.id === 'med-instruction') addMedicineRow();
        });

        /* ================= Quick Advice presets ================= */
        // Notun advice jog korte chaile je group-e ekta line add korun. Notun group-o banano jabe.
        const ADVICE_PRESETS = [
            { key: 'general', icon: '🩺', label: 'সাধারণ', items: [
                'প্রচুর পানি পান করুন।',
                'পর্যাপ্ত বিশ্রাম নিন।',
                'ঔষধ নিয়মিত ও সময়মতো খান, কোর্স শেষ করুন।',
                'সুষম ও পুষ্টিকর খাবার খান।',
                'তৈলাক্ত, ঝাল ও ভাজাপোড়া খাবার এড়িয়ে চলুন।',
                'ধূমপান ও তামাক এড়িয়ে চলুন।',
                'রিপোর্ট নিয়ে আবার দেখা করুন।',
                'সমস্যা বাড়লে বা নতুন উপসর্গ দেখা দিলে দ্রুত যোগাযোগ করুন।',
            ]},
            { key: 'heart', icon: '❤️', label: 'হৃদরোগ', items: [
                'লবণ কম খান।',
                'তৈলাক্ত, চর্বিযুক্ত ও ভাজাপোড়া খাবার এড়িয়ে চলুন।',
                'ধূমপান সম্পূর্ণ বন্ধ করুন।',
                'ভারী কাজ ও অতিরিক্ত পরিশ্রম এড়িয়ে চলুন, ডাক্তারের পরামর্শ মতো হালকা হাঁটুন।',
                'নিয়মিত BP ও Pulse মাপুন।',
                'ঔষধ নিজে থেকে বন্ধ করবেন না।',
                'বুকে ব্যথা, শ্বাসকষ্ট বা অতিরিক্ত ঘাম হলে সাথে সাথে হাসপাতালে যান।',
            ]},
            { key: 'diabetes', icon: '🍬', label: 'ডায়াবেটিস', items: [
                'চিনি ও মিষ্টি জাতীয় খাবার এড়িয়ে চলুন।',
                'ভাত/রুটি পরিমিত খান, শাকসবজি বেশি খান।',
                'প্রতিদিন অন্তত ৩০ মিনিট হাঁটুন।',
                'নিয়মিত Blood Sugar পরীক্ষা করুন।',
                'ঔষধ/ইনসুলিন সময়মতো নিন, খাবার বাদ দেবেন না।',
                'পায়ের যত্ন নিন, খালি পায়ে হাঁটবেন না।',
                'অতিরিক্ত ঘাম, কাঁপুনি বা মাথা ঘোরালে চিনি/গ্লুকোজ খান এবং ডাক্তারের সাথে যোগাযোগ করুন।',
            ]},
            { key: 'stroke', icon: '🧠', label: 'স্ট্রোক', items: [
                'BP নিয়মিত নিয়ন্ত্রণে রাখুন।',
                'লবণ ও তৈলাক্ত খাবার কম খান।',
                'ধূমপান ও তামাক সম্পূর্ণ বন্ধ করুন।',
                'ডাক্তারের পরামর্শ মতো ঔষধ নিয়মিত খান, নিজে থেকে বন্ধ করবেন না।',
                'ফিজিওথেরাপি ও ব্যায়াম নিয়মিত করুন।',
                'হঠাৎ মুখ বেঁকে যাওয়া, হাত-পা দুর্বল হওয়া বা কথা জড়িয়ে গেলে সাথে সাথে হাসপাতালে নিন।',
            ]},
            { key: 'bp', icon: '💓', label: 'উচ্চ রক্তচাপ', items: [
                'লবণ কম খান, খাবারে অতিরিক্ত কাঁচা লবণ নেবেন না।',
                'নিয়মিত BP মাপুন ও লিখে রাখুন।',
                'মানসিক চাপ এড়িয়ে চলুন, পর্যাপ্ত ঘুমান।',
                'ওজন নিয়ন্ত্রণে রাখুন।',
                'প্রতিদিন হালকা ব্যায়াম বা হাঁটাহাঁটি করুন।',
            ]},
            { key: 'fever', icon: '🌡️', label: 'জ্বর/ডেঙ্গু', items: [
                'প্রচুর তরল (পানি, ORS, ডাবের পানি) পান করুন।',
                'পূর্ণ বিশ্রামে থাকুন।',
                'ডাক্তারের পরামর্শ ছাড়া এসপিরিন বা অন্য ব্যথানাশক (NSAID) খাবেন না।',
                'জ্বর ৩ দিনের বেশি থাকলে আবার দেখা করুন।',
                'রক্তক্ষরণ, তীব্র পেটে ব্যথা, বারবার বমি বা অতিরিক্ত দুর্বলতা হলে সাথে সাথে হাসপাতালে যান।',
            ]},
            { key: 'gastric', icon: '🍽️', label: 'গ্যাস্ট্রিক', items: [
                'নিয়মিত সময়ে খাবার খান, দীর্ঘক্ষণ খালি পেটে থাকবেন না।',
                'ঝাল, তেল-মসলাযুক্ত ও টক খাবার এড়িয়ে চলুন।',
                'চা-কফি ও ধূমপান কমান।',
                'খাবারের পর সাথে সাথে শুয়ে পড়বেন না।',
                'রাতের খাবার ঘুমের ২-৩ ঘণ্টা আগে খান।',
            ]},
            { key: 'resp', icon: '🫁', label: 'শ্বাসকষ্ট/কাশি', items: [
                'ধুলাবালি, ধোঁয়া ও ঠান্ডা এড়িয়ে চলুন।',
                'ধূমপান থেকে দূরে থাকুন।',
                'ইনহেলার ডাক্তারের নির্দেশ মতো ব্যবহার করুন।',
                'কুসুম গরম পানি পান করুন ও গরম পানির ভাপ নিন।',
                'শ্বাসকষ্ট হঠাৎ বেড়ে গেলে সাথে সাথে হাসপাতালে যান।',
            ]},
        ];
        let adviceGroup = ADVICE_PRESETS[0].key;

        function currentAdviceItems() {
            const g = ADVICE_PRESETS.find(x => x.key === adviceGroup) || ADVICE_PRESETS[0];
            return g.items;
        }

        function renderAdvicePresets() {
            byId('adviceTabs').innerHTML = ADVICE_PRESETS.map(g => {
                const on = g.key === adviceGroup;
                return `<button type="button" data-group="${escHtml(g.key)}" class="advice-tab px-2.5 py-1 rounded-full text-xs font-bold border ${on ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-indigo-50'}">${g.icon} ${escHtml(g.label)}</button>`;
            }).join('');

            const ta = byId('adviceInput').value;
            byId('adviceChips').innerHTML = currentAdviceItems().map((txt, i) => {
                const used = ta.indexOf(txt) !== -1;
                return `<span data-i="${i}" class="advice-chip ${used ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white text-slate-700 border-slate-200 hover:bg-indigo-50 hover:border-indigo-200'} border px-2.5 py-1 rounded-lg text-xs cursor-pointer" title="ক্লিক করে Advice-এ যোগ করুন">${used ? '✓ ' : '+ '}${escHtml(txt)}</span>`;
            }).join('');
        }

        function addAdviceLines(lines) {
            const ta = byId('adviceInput');
            const existing = ta.value.split('\n').map(s => s.trim()).filter(Boolean);
            lines.forEach(l => { if (existing.indexOf(l) === -1) existing.push(l); });
            ta.value = existing.join('\n');
            ta.rows = Math.min(10, Math.max(2, existing.length));
            dirty = true;
            renderAdvicePresets();
        }

        byId('adviceTabs').addEventListener('click', function (e) {
            const b = e.target.closest('.advice-tab'); if (!b) return;
            adviceGroup = b.dataset.group;
            renderAdvicePresets();
        });
        byId('adviceChips').addEventListener('click', function (e) {
            const c = e.target.closest('.advice-chip'); if (!c) return;
            addAdviceLines([currentAdviceItems()[parseInt(c.dataset.i, 10)]]);
        });
        byId('adviceAddAll').addEventListener('click', function () { addAdviceLines(currentAdviceItems()); });
        byId('adviceInput').addEventListener('input', renderAdvicePresets);
        renderAdvicePresets();

        /* ================= Unsaved changes warning ================= */
        rxForm.addEventListener('input', function () { dirty = true; });
        rxForm.addEventListener('change', function () { dirty = true; });
        window.addEventListener('beforeunload', function (e) {
            if (dirty && !submitting) { e.preventDefault(); e.returnValue = ''; }
        });


        /* ================= Toast ================= */
        function rxToast(msg) {
            let t = byId('rxToast');
            if (!t) { t = document.createElement('div'); t.id = 'rxToast'; document.body.appendChild(t); }
            t.textContent = msg; t.classList.add('show');
            clearTimeout(t._h); t._h = setTimeout(function () { t.classList.remove('show'); }, 1800);
        }

        /* ================= Mobile step system (lg-er niche) ================= */
        // Desktop-e sob ek page-e dekha jay; mobile/tablet-e ek ek step kore.
        // Notun step lagle RX_STEPS-e jog korun ebong card-e data-step="N" attribute দিন।
        const RX_STEPS = [
            { n: 1, t: 'রোগী',      full: 'রোগীর তথ্য' },
            { n: 2, t: 'Vitals',    full: 'Vitals ও সমস্যা' },
            { n: 3, t: 'Dx/Test',   full: 'Diagnosis ও Test' },
            { n: 4, t: 'ঔষধ',       full: 'ঔষধ (Rx)' },
            { n: 5, t: 'Save',      full: 'Advice ও Save' },
        ];
        let curStep = 1;
        const stepperTrack = byId('stepperTrack');
        const stepBack = byId('stepBack');
        const stepNextBtn = byId('stepNext');
        const stepInfo = byId('stepInfo');

        function stepsNow() { return RX_STEPS.filter(function (s) { return !(s.n === 4 && currentMode() === 'investigation_only'); }); }
        function isStepView() { return window.matchMedia('(max-width: 1023px)').matches; }

        function renderStepper() {
            const list = stepsNow();
            if (!list.some(function (s) { return s.n === curStep; })) curStep = list[list.length - 1].n;
            const idx = list.findIndex(function (s) { return s.n === curStep; });
            const meds = medRowCount();

            stepperTrack.innerHTML = list.map(function (s, i) {
                const st = i < idx ? 'done' : (i === idx ? 'active' : '');
                const badge = (s.n === 4 && meds > 0) ? '<span class="rx-badge">' + meds + '</span>' : '';
                return '<button type="button" class="rx-dot ' + st + '" data-go="' + s.n + '">' +
                    '<span class="rx-dot-c">' + (i < idx ? '<i class="fa-solid fa-check"></i>' : (i + 1)) + '</span>' +
                    '<span>' + s.t + '</span>' + badge + '</button>';
            }).join('');

            document.querySelectorAll('.rx-step').forEach(function (el) {
                el.classList.toggle('is-active', Number(el.dataset.step) === curStep);
            });

            stepInfo.textContent = 'ধাপ ' + (idx + 1) + '/' + list.length + ' · ' + list[idx].full;
            stepBack.disabled = idx === 0;
            const last = idx === list.length - 1;
            stepNextBtn.classList.toggle('rx-save', last);
            stepNextBtn.innerHTML = last
                ? '<i class="fa-solid fa-floppy-disk"></i> Save'
                : 'পরবর্তী <i class="fa-solid fa-arrow-right"></i>';
        }

        function goStep(n, o) {
            o = o || {};
            curStep = n;
            renderStepper();
            if (!o.noScroll && isStepView()) byId('stepper').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function onStepModeChange() {
            if (currentMode() === 'investigation_only' && curStep === 4) curStep = 5;
            renderStepper();
        }

        async function stepNext() {
            const list = stepsNow();
            const idx = list.findIndex(function (s) { return s.n === curStep; });
            if (idx === list.length - 1) { byId('submitBtn').click(); return; }

            if (curStep === 1) {
                if (patientSearchInFlight) { await patientSearchInFlight; }
                if (!val('patientMobile') || !val('patientName')) {
                    await appDialog({ type: 'warning', title: 'রোগীর তথ্য অসম্পূর্ণ', message: 'রোগীর নাম ও মোবাইল নম্বর দিতে হবে।' });
                    (val('patientMobile') ? byId('patientName') : patientMobileInput).focus();
                    return;
                }
                if (patientDecisionPending && !patientIdInput.value) {
                    await appDialog({ type: 'warning', title: 'রোগী নির্বাচন করুন', message: 'এই নম্বরে আগে থেকেই রোগী আছে। তালিকা থেকে সঠিক রোগী নির্বাচন করুন, অথবা "নতুন রোগী হিসেবে যোগ করুন" বাছুন।' });
                    patientMatches.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
            }
            goStep(list[idx + 1].n);
        }

        stepNextBtn.addEventListener('click', stepNext);
        stepBack.addEventListener('click', function () {
            const list = stepsNow();
            const idx = list.findIndex(function (s) { return s.n === curStep; });
            if (idx > 0) goStep(list[idx - 1].n);
        });
        stepperTrack.addEventListener('click', function (e) {
            const b = e.target.closest('[data-go]'); if (!b) return;
            goStep(Number(b.dataset.go));
        });
        new MutationObserver(renderStepper).observe(prescriptionList, { childList: true });
        window.addEventListener('resize', function () { if (!isStepView()) renderStepper(); });

        /* ================= Dose / Duration / Instruction suggestions ================= */
        // Notun option lagle nicher list-e line jog korun. Format: 'Text|alias|alias'
        const BN_DIGITS = '০১২৩৪৫৬৭৮৯';
        const toBn = (s) => String(s).replace(/[0-9]/g, function (d) { return BN_DIGITS[d]; });
        const toEn = (s) => String(s).replace(/[০-৯]/g, function (d) { return String(BN_DIGITS.indexOf(d)); });
        const sugNorm = (s) => toEn(s).toLowerCase().replace(/\s+/g, ' ').trim();

        function sugFromLines(lines) {
            const seen = {};
            const out = [];
            lines.forEach(function (ln) {
                const p = ln.split('|');
                const k = sugNorm(p[0]);
                if (!k || seen[k]) return;
                seen[k] = 1;
                out.push({ v: p[0], label: p[0], hint: '', key: sugNorm(p.join(' ')) });
            });
            return out;
        }

        // ---- Dose (M+A+N): shob possible combination (0, 0.5, 1, 1.5, 2) ----
        const DOSE_OPTS = (function () {
            const common = ['1+0+0', '0+1+0', '0+0+1', '1+0+1', '1+1+0', '0+1+1', '1+1+1', '2+0+2', '1+2+1', '2+2+2', '0.5+0+0.5', '0.5+0.5+0.5', '2+0+0', '0+0+2', '0.5+0+0', '0+0+0.5', '1+0+2', '2+0+1', '2+1+2', '2+1+1', '1+1+2', '2+2+0', '0+2+2', '0+2+0'];
            const vals = [0, 0.5, 1, 1.5, 2];
            const all = common.slice();
            vals.forEach(function (a) { vals.forEach(function (b) { vals.forEach(function (c) {
                const s = a + '+' + b + '+' + c;
                if (s !== '0+0+0') all.push(s);
            }); }); });
            const slot = ['সকাল', 'দুপুর', 'রাত'];
            const seen = {};
            const out = [];
            all.forEach(function (s) {
                if (seen[s]) return; seen[s] = 1;
                const nums = s.split('+').map(Number);
                const hint = nums.map(function (n, i) { return n > 0 ? slot[i] + (n !== 1 ? ' ' + toBn(n) : '') : null; }).filter(Boolean).join(' · ');
                out.push({ v: s, label: toBn(s), hint: hint, key: sugNorm(s + ' ' + toBn(s) + ' ' + hint) });
            });
            return out;
        })();

        // ---- Syrup dose (text) ----
        const SYRUP_OPTS = (function () {
            const amounts = ['½ চামচ|half spoon', '১ চামচ|1 spoon|tsp', '১½ চামচ', '২ চামচ|2 spoon|tsp', '৩ চামচ', '২.৫ মিলি|ml', '৫ মিলি|5 ml|ml', '৭.৫ মিলি|ml', '১০ মিলি|ml', '১৫ মিলি|ml', '২০ মিলি|ml'].map(function (x) { return x.split('|')[0]; });
            const freqs = ['দিনে ১ বার', 'দিনে ২ বার', 'দিনে ৩ বার', 'দিনে ৪ বার', 'রাতে ১ বার', 'সকালে ১ বার'];
            const lines = amounts.slice();
            ['প্রতি ৪ ঘণ্টা পর', 'প্রতি ৬ ঘণ্টা পর', 'প্রতি ৮ ঘণ্টা পর', 'প্রতি ১২ ঘণ্টা পর', 'প্রয়োজনে ৫ মিলি', 'প্রয়োজনে ১ চামচ', 'প্রয়োজনে ২ চামচ'].forEach(function (x) { lines.push(x); });
            amounts.forEach(function (a) { freqs.forEach(function (f) { lines.push(a + ' করে ' + f); }); });
            return sugFromLines(lines.map(function (l) { return l + '|' + (l.indexOf('চামচ') !== -1 ? 'spoon tsp' : (l.indexOf('মিলি') !== -1 ? 'ml' : '')); }));
        })();

        // ---- Duration ----
        const DUR_OPTS = (function () {
            const lines = ['৩ দিন|3 day', '৫ দিন|5 day', '৭ দিন|7 day', '১০ দিন|10 day', '১৪ দিন|14 day', '১ মাস|1 month', 'চলবে|continue', 'প্রয়োজনে|prn|sos'];
            [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 14, 15, 20, 21, 28, 30, 45, 60].forEach(function (n) { lines.push(toBn(n) + ' দিন|' + n + ' day|' + n + 'd'); });
            [1, 2, 3, 4].forEach(function (n) { lines.push(toBn(n) + ' সপ্তাহ|' + n + ' week|' + n + 'w'); });
            [1, 2, 3, 4, 5, 6, 9].forEach(function (n) { lines.push(toBn(n) + ' মাস|' + n + ' month|' + n + 'm'); });
            lines.push('১ বছর|1 year', 'আজীবন|lifelong', 'একবার মাত্র|stat|once', 'শেষ না হওয়া পর্যন্ত|until finished', 'রিপোর্ট দেখা পর্যন্ত|till report', 'জ্বর থাকলে|fever', 'ব্যথা থাকলে|pain', 'উপসর্গ না কমা পর্যন্ত|till relief');
            return sugFromLines(lines);
        })();

        // ---- Instruction (type onujayi age ashbe, baki-gulo likhle pawa jabe) ----
        const INSTR_BASE = ['খাবার পরে|after meal|ac', 'খাবার আগে|before meal|pc', 'খাবারের মাঝে', 'খালি পেটে|empty stomach', 'সকালে খালি পেটে', 'সকালে|morning', 'দুপুরে|noon', 'বিকালে|evening', 'রাতে|night', 'রাতে ঘুমানোর আগে|bedtime|hs', 'ঘুমানোর আগে', 'প্রয়োজনে|prn|sos', 'প্রয়োজন হলে', 'জ্বর হলে|fever', 'ব্যথা হলে|pain', 'বমি হলে|vomiting', 'পাতলা পায়খানা হলে|diarrhea', 'চুলকানি হলে|itching', 'কাশি হলে|cough', 'শ্বাসকষ্ট হলে|breathing', 'প্রতি ৪ ঘণ্টা পর পর', 'প্রতি ৬ ঘণ্টা পর পর', 'প্রতি ৮ ঘণ্টা পর পর', 'প্রতি ১২ ঘণ্টা পর পর', 'প্রতি ২৪ ঘণ্টায় একবার', 'নিয়মিত খাবেন', 'প্রতিদিন একই সময়ে', 'ভরা পেটে', 'খাবারের ৩০ মিনিট আগে', 'খাবারের ১ ঘণ্টা পর', 'পানির সাথে খাবেন', 'প্রচুর পানি পান করবেন', 'দুধের সাথে নয়', 'চা/কফির সাথে নয়', 'অ্যান্টাসিডের ২ ঘণ্টা পর', 'অন্য ওষুধের ১ ঘণ্টা পর', 'কোর্স শেষ করবেন', 'ডাক্তারের পরামর্শ অনুযায়ী'];
        const INSTR_BY_TYPE = {
            Tablet:    ['চিবিয়ে খাবেন', 'গিলে খাবেন', 'গুঁড়ো করে পানিতে গুলিয়ে খাবেন', 'জিহ্বার নিচে রাখবেন', 'চুষে খাবেন'],
            Capsule:   ['না ভেঙে গিলে খাবেন', 'পূর্ণ গ্লাস পানি দিয়ে খাবেন', 'শোয়া অবস্থায় খাবেন না'],
            Syrup:     ['ঝাঁকিয়ে খাবেন', 'মেপে খাবেন', 'ঠান্ডা জায়গায় রাখবেন', 'খোলার ৭ দিনের মধ্যে ব্যবহার করবেন', 'ফ্রিজে রাখবেন'],
            Injection: ['মাংসপেশিতে (IM)', 'শিরায় (IV)', 'চামড়ার নিচে (SC)', 'ধীরে ধীরে পুশ করবেন', 'স্যালাইনের সাথে দিবেন', 'একবার মাত্র (Stat)', 'Test dose করে দিবেন'],
            Drop:      ['চোখে ১ ফোঁটা করে', 'চোখে ২ ফোঁটা করে', 'কানে ২ ফোঁটা করে', 'কানে ৩ ফোঁটা করে', 'নাকে ২ ফোঁটা করে', 'প্রতি নাকে ২ ফোঁটা করে', 'মুখে ১ মিলি দিবেন', 'ফোঁটা দেওয়ার পর ১ মিনিট চোখ বন্ধ রাখবেন'],
            Cream:     ['আক্রান্ত স্থানে লাগাবেন', 'পাতলা করে লাগাবেন', 'দিনে ২ বার লাগাবেন', 'দিনে ৩ বার লাগাবেন', 'রাতে ঘুমানোর আগে লাগাবেন', 'লাগিয়ে হালকা মালিশ করবেন', 'পরিষ্কার করে শুকিয়ে লাগাবেন', 'গোসলের পর লাগাবেন', 'ক্ষতস্থানে লাগাবেন', 'চোখে লাগাবেন না', 'সূর্যের আলো এড়িয়ে চলবেন'],
            Inhaler:   ['১ পাফ করে', '২ পাফ করে', 'শ্বাসকষ্ট হলে ২ পাফ', 'স্পেসার দিয়ে ব্যবহার করবেন', 'ব্যবহারের পর কুলকুচি করবেন', 'প্রতিদিন সকাল-রাত নিয়মিত'],
            Other:     [],
        };
        const instrCache = {};
        function instrOptsFor(type) {
            if (instrCache[type]) return instrCache[type];
            const lines = (INSTR_BY_TYPE[type] || []).concat(INSTR_BASE);
            Object.keys(INSTR_BY_TYPE).forEach(function (t) { if (t !== type) lines.push.apply(lines, INSTR_BY_TYPE[t]); });
            return (instrCache[type] = sugFromLines(lines));
        }

        // ---- Generic dropdown (investigation-er moto) ----
        function attachSuggest(input, getOpts, o) {
            o = o || {};
            const host = input.parentNode;
            if (getComputedStyle(host).position === 'static') host.style.position = 'relative';
            const box = document.createElement('div');
            box.className = 'fld-sug';
            host.appendChild(box);
            let list = [], active = -1;
            const isOpen = function () { return box.style.display === 'block'; };

            function tok() {
                const v = input.value;
                if (!o.multi) return { s: 0, e: v.length, q: v.trim() };
                const pos = input.selectionStart == null ? v.length : input.selectionStart;
                const s = pos === 0 ? 0 : v.lastIndexOf(',', pos - 1) + 1;
                const nx = v.indexOf(',', pos);
                const e = nx === -1 ? v.length : nx;
                return { s: s, e: e, q: v.slice(s, e).trim() };
            }
            function close() { box.style.display = 'none'; list = []; active = -1; }
            function place() {
                const r = input.getBoundingClientRect();
                const vh = window.visualViewport ? window.visualViewport.height : window.innerHeight;
                const below = vh - r.bottom - 12, above = r.top - 12;
                const flip = below < 170 && above > below;
                box.classList.toggle('up', flip);
                box.style.maxHeight = Math.max(120, Math.min(260, flip ? above : below)) + 'px';
            }
            function paint() {
                box.querySelectorAll('.fld-opt').forEach(function (el, i) { el.classList.toggle('on', i === active); });
                const el = box.children[active];
                if (el && el.scrollIntoView) el.scrollIntoView({ block: 'nearest' });
            }
            function render(fromFocus) {
                const t = tok();
                let q = sugNorm(t.q);
                const all = getOpts();
                if (fromFocus && !o.multi && all.some(function (x) { return sugNorm(x.v) === q; })) q = '';
                const taken = o.multi
                    ? input.value.slice(0, t.s).concat(',', input.value.slice(t.e)).split(',').map(sugNorm).filter(Boolean)
                    : [];
                const res = [];
                all.forEach(function (x, i) {
                    if (taken.indexOf(sugNorm(x.v)) !== -1) return;
                    let r = 0;
                    if (q) {
                        if (x.key.indexOf(q) !== -1) {
                            r = (x.key.indexOf(q) === 0) ? 0 : (x.key.indexOf(' ' + q) !== -1 ? 1 : 2);
                        } else if (q.split(' ').every(function (w) { return x.key.indexOf(w) !== -1; })) {
                            r = 3;
                        } else return;
                    }
                    res.push({ x: x, r: r, i: i });
                });
                res.sort(function (a, b) { return a.r - b.r || a.i - b.i; });
                list = res.slice(0, 80).map(function (z) { return z.x; });
                if (!list.length) { close(); return; }
                const exact = q && sugNorm(list[0].v) === q;
                active = (q && !exact) ? 0 : -1;
                box.innerHTML = list.map(function (x, i) {
                    return '<div class="fld-opt' + (i === active ? ' on' : '') + '" data-i="' + i + '"><span>' + escHtml(x.label || x.v) + '</span>' +
                        (x.hint ? '<small>' + escHtml(x.hint) + '</small>' : '') + '</div>';
                }).join('');
                box.style.display = 'block';
                place();
            }
            function pick(i) {
                const x = list[i]; if (!x) return;
                if (o.multi) {
                    const t = tok();
                    const parts = input.value.split(',').map(function (s) { return s.trim(); });
                    const idx = input.value.slice(0, t.s).split(',').length - 1;
                    parts[idx] = x.v;
                    input.value = parts.filter(Boolean).join(', ');
                } else {
                    input.value = x.v;
                }
                dirty = true;
                close();
                const fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
                if (!o.multi && o.next && fine) { const n = byId(o.next); if (n) n.focus(); }
            }

            input.addEventListener('focus', function () { render(true); });
            input.addEventListener('click', function () { render(true); });
            input.addEventListener('input', function () { render(false); });
            input.addEventListener('keydown', function (e) {
                if (!isOpen() || !list.length) {
                    if (e.key === 'ArrowDown') { e.preventDefault(); render(true); }
                    return;
                }
                if (e.key === 'ArrowDown') { e.preventDefault(); active = (active + 1) % list.length; paint(); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); active = (active - 1 + list.length) % list.length; paint(); }
                else if ((e.key === 'Enter' || e.key === 'Tab') && active >= 0) { e.preventDefault(); e.stopPropagation(); pick(active); }
                else if (e.key === 'Escape') { e.preventDefault(); close(); }
            });
            input.addEventListener('blur', function () { setTimeout(close, 120); });
            box.addEventListener('mousedown', function (e) { e.preventDefault(); });   // input-er focus jate na jay
            box.addEventListener('click', function (e) {
                const row = e.target.closest('.fld-opt');
                if (row) pick(parseInt(row.dataset.i, 10));
            });
            document.addEventListener('pointerdown', function (e) { if (!host.contains(e.target)) close(); });
        }

        attachSuggest(medDose, function () { return DOSE_OPTS; }, { next: 'med-duration' });
        attachSuggest(medDoseText, function () { return SYRUP_OPTS; }, { next: 'med-duration' });
        attachSuggest(byId('med-duration'), function () { return DUR_OPTS; }, { next: 'med-instruction' });
        attachSuggest(byId('med-instruction'), function () { return instrOptsFor(medType.value); }, { multi: true });

        /* ================= Init ================= */
        syncDoseField();
        checkEmpty();
        setMode(byId('typeInput').value || 'full');
        renderStepper();
        dirty = false;

        // Page khulei important popup (banner-er bodole)
        window.addEventListener('load', function () {
            if (!HAS_CHAMBER) { showNoChamberDialog(); return; }
            if (SERVER_ERROR) {
                appDialog({ type: 'danger', title: 'Save করা যায়নি', message: SERVER_ERROR });
            }
        });
    </script>
@endpush

@push('scripts')
@include('doctor.prescriptions._default-medicines')
@endpush