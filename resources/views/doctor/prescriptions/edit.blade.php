@extends('layouts.doctor')

@section('title', 'Edit Prescription')

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
    </style>
@endpush

@section('content')
@php
    $doctor = $doctor ?? auth()->user()->doctor;
    $pt = $prescription->patient;
    $isInv = ($prescription->type ?? 'full') === 'investigation_only';

    // Form-e ager ঔষধগুলো (validation fail hole old(), noile DB theke)
    $medsForJs = old('medicines')
        ? array_values((array) old('medicines'))
        : $prescription->medicines->map(fn ($m) => [
            'medicine_id'   => $m->medicine_id,
            'medicine_name' => $m->medicine_name ?? '',
            'medicine_type' => $m->medicine_type ?? 'Tablet',
            'dose_morning'  => $m->dose_morning,
            'dose_noon'     => $m->dose_noon,
            'dose_night'    => $m->dose_night,
            'dose_text'     => $m->dose_text,
            'duration'      => $m->duration,
            'instruction'   => $m->instruction,
        ])->values()->all();

    $followup = $prescription->followup_date
        ? \Carbon\Carbon::parse($prescription->followup_date)->format('Y-m-d')
        : '';
@endphp

<div class="prescription-builder w-full">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold text-dark mb-1">Edit Prescription <span class="text-muted fw-normal" style="font-size:.9rem;">#{{ $prescription->id }}</span></h1>
            <p class="text-secondary small mb-0">
                {{ $pt->name ?? '' }}@if($pt?->patient_code) · Patient ID: {{ $pt->patient_code }}@endif
                · {{ $prescription->created_at->format('d M Y') }}
                @if($isInv) · <span class="fw-semibold">Investigation Advice</span>@endif
            </p>
        </div>
        <a href="{{ route('doctor.prescriptions.show', $prescription->id) }}" class="btn btn-outline-primary btn-sm fw-semibold">
            <i class="fa-solid fa-arrow-left me-1"></i> Prescription-এ ফিরে যান
        </a>
    </div>

    <form action="{{ route('doctor.prescriptions.update', $prescription->id) }}" method="POST" id="prescriptionForm" autocomplete="off" novalidate>
            @csrf
            @method('PUT')
            <input type="hidden" name="type" id="typeInput" value="{{ $prescription->type ?? 'full' }}">
            <input type="hidden" name="patient_id" value="{{ $prescription->patient_id }}">
            <input type="hidden" name="parent_prescription_id" value="{{ $prescription->parent_prescription_id }}">

            @if($errors->any())
                <div class="max-w-[1400px] mx-auto mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <main class="max-w-[1400px] mx-auto grid grid-cols-12 gap-6">

                <!-- LEFT: Patient (read-only) + Vitals + Diagnosis -->
                <div class="col-span-12 lg:col-span-4 space-y-6 min-w-0">

                    <!-- Patient Info (read-only) -->
                    <div id="stepPatient" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-900 flex items-center"><i class="fa-solid fa-circle-user text-indigo-600 mr-2 text-lg"></i> Patient Information</h3>
                            @if($pt?->patient_code)
                                <span class="text-[11px] font-bold bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded">{{ $pt->patient_code }}</span>
                            @endif
                        </div>
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Mobile</label>
                                    <input type="text" name="patient_mobile" id="patientMobile" value="{{ old('patient_mobile', $pt->mobile ?? '') }}" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-600">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Name</label>
                                    <input type="text" name="patient_name" id="patientName" value="{{ old('patient_name', $pt->name ?? '') }}" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-600">
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Age</label>
                                    <input type="text" name="patient_age" id="patientAge" value="{{ old('patient_age', $pt->age ?? '') }}" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-600">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Weight</label>
                                    <input type="text" name="patient_weight" id="patientWeight" value="{{ old('patient_weight', $pt->weight ?? '') }}" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-600">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Gender</label>
                                    <input type="text" name="patient_gender" id="patientGender" value="{{ old('patient_gender', $pt->gender ?? '') }}" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-600">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Blood Group</label>
                                    <input type="text" name="blood_group" id="patientBlood" value="{{ old('blood_group', $pt->blood_group ?? '') }}" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-600">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Address</label>
                                    <input type="text" name="patient_address" id="patientAddress" value="{{ old('patient_address', $pt->address ?? '') }}" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-600">
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400"><i class="fa-solid fa-lock mr-1"></i> রোগীর তথ্য এখানে পরিবর্তন করা যায় না।</p>
                        </div>
                    </div>

                    <!-- Clinical Vitals -->
                    <div id="stepClinical" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
                        <h3 class="font-bold text-slate-900 flex items-center mb-2"><i class="fa-solid fa-heart-pulse text-rose-500 mr-2 text-lg"></i> Clinical Vitals</h3>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">BP (mmHg)</span>
                                <input type="text" name="bp" id="vBp" value="{{ old('bp', $prescription->bp ?? '') }}" placeholder="120/80" class="w-full bg-transparent border-0 p-0 text-sm font-semibold text-slate-700 focus:ring-0 focus:outline-none mt-1">
                            </div>
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Pulse</span>
                                <input type="text" name="pulse" id="vPulse" value="{{ old('pulse', $prescription->pulse ?? '') }}" placeholder="72" class="w-full bg-transparent border-0 p-0 text-sm font-semibold text-slate-700 focus:ring-0 focus:outline-none mt-1">
                            </div>
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100">
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Temp (°F)</span>
                                <input type="text" name="temperature" id="vTemp" value="{{ old('temperature', $prescription->temperature ?? '') }}" placeholder="98.6" class="w-full bg-transparent border-0 p-0 text-sm font-semibold text-slate-700 focus:ring-0 focus:outline-none mt-1">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Clinical Complaints</label>
                            <textarea name="clinical_complaints" id="symptomsInput" rows="2" placeholder="e.g., Fever with chest pain..." class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('clinical_complaints', $prescription->clinical_complaints ?? '') }}</textarea>

                            <button type="button" onclick="suggestDiagnosis()" id="diagBtn" class="mt-2 text-xs font-bold bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-3 py-1.5 rounded-lg flex items-center gap-1 hover:opacity-90">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Suggest Diagnosis (AI)
                            </button>
                            <div id="diagSuggestions" class="flex flex-wrap gap-1.5 mt-2"></div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Risk Factors</label>
                            <textarea name="risk_factors" id="riskInput" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('risk_factors', $prescription->risk_factors ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Diagnosis & Investigations -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 space-y-4">
                        <h3 class="font-bold text-slate-900 flex items-center mb-1"><i class="fa-solid fa-stethoscope text-teal-600 mr-2 text-lg"></i> Diagnosis & Tests</h3>

                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Diagnosis <span id="dxHint" class="normal-case font-normal text-slate-400"></span></label>
                            <textarea name="diagnosis" id="diagnosisInput" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('diagnosis', $prescription->diagnosis ?? '') }}</textarea>

                            <div id="patternPanel" class="hidden mt-3 bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                                <p class="text-xs font-bold text-emerald-800 flex items-center mb-2">
                                    <i class="fa-solid fa-brain mr-1.5"></i> আপনার Usual Treatment এই Diagnosis-এর জন্য
                                </p>
                                <div id="patternList" class="space-y-2"></div>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Investigations / Tests Advice <span id="invReq" class="text-rose-500 hidden">*</span></label>
                            <textarea name="investigations" id="investigations" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">{{ old('investigations', $prescription->investigations ?? '') }}</textarea>

                            <!-- Quick test chips (JS diye render hoy, QUICK_TESTS array te notun test jog korun) -->
                            <div id="quickTests" class="flex flex-wrap gap-1.5 mt-2"></div>
                        </div>

                        <div id="findingsWrap" class="{{ $prescription->parent_prescription_id ? 'bg-amber-50 border border-amber-200 rounded-xl p-3' : '' }}">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Investigation Findings <span class="normal-case font-normal text-slate-400">(রিপোর্টের ফলাফল)</span></label>
                            <textarea name="investigation_findings" id="investigationFindings" rows="3" placeholder="e.g., CBC: Hb 11.2, WBC 14000, Platelet 90000. NS-1: Positive" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">{{ old('investigation_findings', $prescription->investigation_findings) }}</textarea>
                        </div>
                    </div>
                </div>


                <!-- RIGHT -->
                <div class="col-span-12 lg:col-span-8 space-y-6 min-w-0">

                    <!-- Investigation-only note -->
                    <div id="investigationNote" class="hidden bg-teal-50 border border-teal-200 rounded-2xl p-6">
                        <h3 class="font-bold text-teal-900 flex items-center mb-2"><i class="fa-solid fa-flask mr-2"></i> Investigation Advice মোড</h3>
                        <p class="text-sm text-teal-800">এটি একটি Investigation Advice, তাই এখানে ঔষধ নেই। বাম দিকে পরীক্ষাগুলো সংশোধন করে Update করুন।</p>
                        <p class="text-sm text-teal-800 mt-2">রোগী রিপোর্ট নিয়ে ফিরলে নতুন Prescription থেকে মোবাইল নম্বর বা Patient ID দিয়ে খুঁজে <b>Continue with Reports</b> চাপুন।</p>
                    </div>

                    <!-- Medicine card -->
                    <div id="medicineCard" class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-100 flex flex-col min-h-[560px]">
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
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div class="md:col-span-4 relative">
                                <label class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Search Medicine</label>
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-sm"></i>
                                    <input type="text" id="med-search" placeholder="Type sec, napa, pcm..." autocomplete="off" class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <input type="hidden" id="med-id">
                                </div>
                                <div id="search-results" class="hidden absolute left-0 right-0 md:right-4 mt-1 bg-white border border-slate-200 rounded-lg shadow-xl z-50 max-h-52 overflow-y-auto text-sm"></div>
                            </div>
                            <div class="md:col-span-2">
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
                            <div class="md:col-span-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase block mb-1" id="med-dose-label">Dose (M+A+N)</label>
                                <input type="text" id="med-dose" placeholder="e.g., 1+0+1" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <input type="text" id="med-dose-text" placeholder="২ চামচ" class="hidden w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Duration</label>
                                <input type="text" id="med-duration" placeholder="৭ দিন" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="md:col-span-2 flex items-end">
                                <button type="button" onclick="addMedicineRow()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-lg text-sm shadow-sm">
                                    <i class="fa-solid fa-plus mr-1"></i> Add
                                </button>
                            </div>
                            <div class="md:col-span-12">
                                <label class="text-[11px] font-bold text-slate-400 uppercase block mb-1">Instruction (optional)</label>
                                <input type="text" id="med-instruction" placeholder="খাবার পরে" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Quick chips -->
                        <div class="flex flex-wrap gap-x-5 gap-y-3 mb-4 text-xs">
                            <div>
                                <span class="text-slate-400 font-semibold mr-1 block mb-1">Dose:</span>
                                <div class="flex flex-wrap gap-1">
                                    <span onclick="fillDose('1+0+0')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">১+০+০</span>
                                    <span onclick="fillDose('0+1+0')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">০+১+০</span>
                                    <span onclick="fillDose('0+0+1')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">০+০+১</span>
                                    <span onclick="fillDose('1+0+1')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">১+০+১</span>
                                    <span onclick="fillDose('1+1+0')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">১+১+০</span>
                                    <span onclick="fillDose('0+1+1')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">০+১+১</span>
                                    <span onclick="fillDose('1+1+1')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">১+১+১</span>
                                    <span onclick="fillDose('2+0+2')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">২+০+২</span>
                                    <span onclick="fillDose('1+2+1')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">১+২+১</span>
                                    <span onclick="fillDose('2+2+2')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">২+২+২</span>
                                    <span onclick="fillDose('0.5+0+0.5')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">০.৫+০+০.৫</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-400 font-semibold mr-1 block mb-1">Duration:</span>
                                <div class="flex flex-wrap gap-1">
                                    <span onclick="fillDuration('৩ দিন')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">৩ দিন</span>
                                    <span onclick="fillDuration('৫ দিন')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">৫ দিন</span>
                                    <span onclick="fillDuration('৭ দিন')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">৭ দিন</span>
                                    <span onclick="fillDuration('১০ দিন')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">১০ দিন</span>
                                    <span onclick="fillDuration('১৪ দিন')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">১৪ দিন</span>
                                    <span onclick="fillDuration('২১ দিন')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">২১ দিন</span>
                                    <span onclick="fillDuration('১ মাস')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">১ মাস</span>
                                    <span onclick="fillDuration('৩ মাস')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">৩ মাস</span>
                                    <span onclick="fillDuration('চলবে')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">চলবে</span>
                                    <span onclick="fillDuration('প্রয়োজনে')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">প্রয়োজনে</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-slate-400 font-semibold mr-1 block mb-1">Instr:</span>
                                <div class="flex flex-wrap gap-1">
                                    <span onclick="fillInstruction('খাবার পরে')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">খাবার পরে</span>
                                    <span onclick="fillInstruction('খাবার আগে')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">খাবার আগে</span>
                                    <span onclick="fillInstruction('সকালে খালি পেটে')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">সকালে খালি পেটে</span>
                                    <span onclick="fillInstruction('রাতে ঘুমানোর আগে')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">রাতে ঘুমানোর আগে</span>
                                    <span onclick="fillInstruction('প্রয়োজনে')" class="bg-white border border-slate-200 px-2 py-0.5 rounded cursor-pointer hover:bg-indigo-50">প্রয়োজনে</span>
                                </div>
                            </div>
                        </div>

                        <!-- Favorites -->
                        <div class="mb-4" id="favWrap" style="display:none;">
                            <span class="text-xs font-semibold text-slate-400 block mb-2"><i class="fa-solid fa-star text-amber-400 mr-1"></i> Quick Favorites:</span>
                            <div class="flex flex-wrap gap-2" id="favChips"></div>
                        </div>

                        <!-- Table -->
                        <div class="flex-grow overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[520px]">
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

                    <!-- Advice + Follow-up + Update -->
                    <div class="bg-white rounded-2xl p-4 sm:p-6 shadow-sm border border-slate-100 space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-slate-500 block mb-1">Advice</label>
                            <textarea name="advice" id="adviceInput" rows="2" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('advice', $prescription->advice) }}</textarea>

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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-slate-500 block mb-1" id="followupLabel">Follow-up Date</label>
                                <input type="date" name="followup_date" id="followupDate" value="{{ old('followup_date', $followup) }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="flex items-end gap-2 md:col-span-2">
                                <a href="{{ route('doctor.prescriptions.show', $prescription->id) }}" class="shrink-0 px-4 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 bg-white text-slate-700 hover:bg-slate-50">বাতিল</a>
                                <button type="submit" id="submitBtn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl text-sm shadow-md flex items-center justify-center transition-colors">
                                    <i class="fa-solid fa-floppy-disk mr-2"></i> <span id="submitLabel">Update Prescription</span>
                                </button>
                            </div>
                        </div>
                    </div>
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
        const TOP_MED_URL = "{{ route('doctor.prescriptions.top-medicines') }}";
        const AI_DIAG_URL = "{{ route('doctor.prescriptions.ai-diagnosis') }}";
        const AI_MED_URL = "{{ route('doctor.prescriptions.ai-medicine') }}";
        const PATTERN_URL = "{{ route('doctor.prescriptions.pattern-suggest') }}";
        const CHECK_URL = "{{ route('doctor.prescriptions.check-interactions') }}";
        const CHAMBERS_URL = "{{ route('doctor.chambers.index') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const RX_TYPE = @json($prescription->type ?? 'full');
        const SERVER_ERROR = @json(session('error'));
        const OLD_MEDS = @json($medsForJs);
        const ASK_CONFLICT_CHECK = true;      // Update-er age "Conflict check koren ni" bole jiggesh korbe (false = bondho)
        let lastAiAdvice = '';
        let dirty = false;
        let submitting = false;

        const byId = (id) => document.getElementById(id);
        const val = (id) => { const e = byId(id); return e ? e.value.trim() : ''; };

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

        /* ================= Mode helpers (edit-e type fixed) ================= */
        function currentMode() { return RX_TYPE === 'investigation_only' ? 'investigation_only' : 'full'; }
        function medRowCount() { return document.querySelectorAll('#prescription-list tr:not(#empty-row)').length; }

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
                                    <div class="text-[11px] text-slate-400">${escHtml(item.generic_name || '')}</div>
                                </div>
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

        /* ================= Investigation quick-test chips ================= */
        // Notun test jog korte chaile ei list-e ekta line add korun.
        // Chip-er label alada hole ['Full Name', 'Short Label'] likhun. Test-er naame comma (,) dibenna.
        const QUICK_TESTS = [
            // --- common (ager gulo) ---
            'CBC', 'ESR', 'Dengue NS-1', 'ECG', 'Lipid Profile',
            ['Serum Creatinine', 'Creatinine'], 'RBS', ['Widal Test', 'Widal'], 'Urine R/E', 'X-Ray Chest',
            // --- blood / sugar ---
            'FBS', '2 Hrs ABF', 'HbA1c', 'Hb%', 'Platelet Count', 'PBF', 'CRP', 'Blood Grouping & Rh',
            'Prothrombin Time (PT/INR)', 'D-Dimer',
            // --- liver / kidney / minerals ---
            'SGPT (ALT)', 'Serum Bilirubin', 'LFT', 'RFT', 'Serum Electrolytes', 'Serum Uric Acid', 'Serum Calcium',
            // --- hormone / vitamin ---
            'TSH', 'Thyroid Profile', 'Vitamin D', 'S. Ferritin', 'PSA', 'Pregnancy Test (UPT)',
            // --- infection ---
            'Blood Culture', 'Urine C/S', 'Stool R/E', 'Typhidot', 'Dengue IgG/IgM', 'MP (Malaria Parasite)',
            'HBsAg', 'Anti-HCV', 'Sputum for AFB', 'Mantoux Test',
            // --- cardiac ---
            'Troponin-I', 'CK-MB', 'Echocardiogram',
            // --- imaging ---
            'USG W/A', 'USG KUB', 'CT Scan Brain', 'MRI',
        ];
        const QUICK_VISIBLE = 14;
        let quickExpanded = false;

        function renderQuickTests() {
            const wrap = byId('quickTests');
            const list = quickExpanded ? QUICK_TESTS : QUICK_TESTS.slice(0, QUICK_VISIBLE);

            wrap.innerHTML = list.map(t => {
                const value = Array.isArray(t) ? t[0] : t;
                const label = Array.isArray(t) ? t[1] : t;
                return `<span data-test="${escHtml(value)}" class="quick-test bg-teal-50 text-teal-700 border border-teal-100 px-2 py-0.5 rounded text-xs font-medium cursor-pointer hover:bg-teal-100">🔬 ${escHtml(label)}</span>`;
            }).join('');

            if (QUICK_TESTS.length > QUICK_VISIBLE) {
                const hidden = QUICK_TESTS.length - QUICK_VISIBLE;
                wrap.innerHTML += `<span class="quick-toggle px-2 py-0.5 rounded text-xs font-bold text-indigo-600 cursor-pointer hover:underline">${quickExpanded ? 'কম দেখুন ▲' : `আরও দেখুন (+${hidden}) ▼`}</span>`;
            }
        }

        byId('quickTests').addEventListener('click', function (e) {
            const chip = e.target.closest('.quick-test');
            if (chip) { addQuickTest(chip.dataset.test); return; }
            if (e.target.closest('.quick-toggle')) { quickExpanded = !quickExpanded; renderQuickTests(); }
        });

        function addQuickTest(name) {
            const ta = byId('investigations');
            const parts = ta.value.split(',').map(s => s.trim()).filter(Boolean);
            if (!parts.includes(name)) parts.push(name);
            ta.value = parts.join(', ');
            dirty = true;
        }

        renderQuickTests();

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
                <td class="py-3 text-slate-600">
                    <span class="bg-slate-100 px-2 py-0.5 rounded text-xs whitespace-nowrap">${escHtml(e.doseText || e.dose)}</span>
                    <input type="hidden" name="medicines[${index}][dose_morning]" value="${nums[0]}">
                    <input type="hidden" name="medicines[${index}][dose_noon]" value="${nums[1]}">
                    <input type="hidden" name="medicines[${index}][dose_night]" value="${nums[2]}">
                    <input type="hidden" name="medicines[${index}][dose_text]" value="${escHtml(e.doseText || '')}">
                </td>
                <td class="py-3 text-slate-600">
                    ${escHtml(e.duration)}
                    <input type="hidden" name="medicines[${index}][duration]" value="${escHtml(e.duration)}">
                </td>
                <td class="py-3 text-slate-600">
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
                await appDialog({ type: 'warning', title: 'ডোজ দিন', message: '"' + name + '" এর ডোজ লিখুন বা নিচের কুইক বাটন থেকে বাছুন।' });
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

        /* ================= Update: important popup gulo ================= */
        async function preSubmitChecks() {
            const inv = currentMode() === 'investigation_only';

            if (inv) {
                if (!val('investigations')) {
                    await appDialog({ type: 'warning', title: 'Investigation লিখুন', message: 'Investigation Advice-এ অন্তত একটি পরীক্ষা থাকতে হবে।' });
                    byId('investigations').focus(); return false;
                }
            } else if (medRowCount() === 0) {
                await appDialog({ type: 'warning', title: 'কোনো ঔষধ নেই', message: 'Full Prescription-এ অন্তত একটি ঔষধ থাকতে হবে।' });
                byId('medicineCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }

            if (!inv) {
                const ids = getMedicineIds();
                const fresh = conflictResult && conflictSignature === medSignature(ids);

                if (ids.length >= 2 && !fresh && ASK_CONFLICT_CHECK) {
                    const r = await appDialog({
                        type: 'warning', title: 'Conflict Check করা হয়নি',
                        message: 'আপনি ' + ids.length + 'টি ঔষধ দিয়েছেন, কিন্তু একে অপরের সাথে conflict আছে কিনা AI দিয়ে দেখা হয়নি। এখন দেখে নেবেন?',
                        confirmText: 'এখন Check করি', extraText: 'এভাবেই Update করুন',
                    });
                    if (r === 'confirm') {
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
                            message: 'AI নিচের গুরুতর সমস্যাগুলো পেয়েছে। তবুও Update করবেন?',
                            items: serious.map(w => w.pair + ' — ' + w.effect),
                            confirmText: 'তবুও Update করুন', cancelText: 'ঔষধ দেখি',
                        });
                        if (r !== 'confirm') { byId('conflictBox').scrollIntoView({ behavior: 'smooth', block: 'center' }); return false; }
                    }
                }
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
            byId('submitLabel').textContent = 'Updating...';
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

        /* ================= Init ================= */
        syncDoseField();
        checkEmpty();

        if (RX_TYPE === 'investigation_only') {
            byId('medicineCard').classList.add('hidden');
            byId('investigationNote').classList.remove('hidden');
            byId('findingsWrap').classList.add('hidden');
            byId('invReq').classList.remove('hidden');
            byId('dxHint').textContent = '(Provisional)';
            byId('followupLabel').textContent = 'Report নিয়ে আসার তারিখ';
            prescriptionList.querySelectorAll('input').forEach(i => { i.disabled = true; });
        }
        dirty = false;

        window.addEventListener('load', function () {
            if (SERVER_ERROR) {
                appDialog({ type: 'danger', title: 'Update করা যায়নি', message: SERVER_ERROR });
            }
        });
    </script>
@endpush