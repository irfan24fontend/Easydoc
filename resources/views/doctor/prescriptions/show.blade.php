@extends('layouts.doctor')

@section('title', 'Prescription Details')

@section('content')
<style>
    .page-head h1 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
    .page-head p { color: #64748b; font-size: 0.875rem; margin: 0.25rem 0 0; }

    .panel-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; padding: 1.4rem; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .panel-card h6 { font-weight: 800; color: #0f172a; font-size: 0.9rem; margin-bottom: 1rem; }

    .info-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: .04em; margin-bottom: 2px; }
    .info-value { font-size: 0.9rem; font-weight: 600; color: #0f172a; }

    .med-table thead th { font-size: 0.72rem; text-transform: uppercase; color: #64748b; font-weight: 700; border-bottom: 2px solid #eef0f4; padding: 0.7rem 0.5rem; }
    .med-table tbody td { padding: 0.8rem 0.5rem; font-size: 0.85rem; border-bottom: 1px dashed #eef0f4; }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 page-head mb-4">
    <div>
        <a href="{{ route('doctor.prescriptions.index') }}" class="small text-decoration-none" style="color:#4f46e5; font-weight:700;">
            <i class="fa-solid fa-arrow-left me-1"></i> Prescription List
        </a>
        <h1 class="mt-2">Prescription #{{ $prescription->id }}</h1>
        <p>{{ $prescription->created_at->format('d M Y, h:i A') }} — {{ $prescription->patient->name ?? '' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('doctor.prescriptions.edit', $prescription->id) }}" class="btn d-inline-flex align-items-center gap-2" style="background:#fff7ed; color:#c2410c; border:1px solid #fed7aa; border-radius:10px; font-weight:700; font-size:0.85rem; padding:0.55rem 1rem;">
            <i class="fa-solid fa-pen"></i> Edit
        </a>
        <a href="{{ route('doctor.prescriptions.print', $prescription->id) }}" target="_blank" class="btn d-inline-flex align-items-center gap-2" style="background:#4f46e5; color:#fff; border-radius:10px; font-weight:700; font-size:0.85rem; padding:0.55rem 1rem;">
            <i class="fa-solid fa-print"></i> Print
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-4">
    <!-- LEFT: Patient + Vitals -->
    <div class="col-12 col-lg-4">
        <div class="panel-card mb-4">
            <h6><i class="fa-solid fa-circle-user text-primary me-1"></i> Patient Information</h6>
            <div class="row g-3">
                <div class="col-6">
                    <p class="info-label">Name</p>
                    <p class="info-value">{{ $prescription->patient->name ?? '—' }}</p>
                </div>
                <div class="col-6">
                    <p class="info-label">Phone</p>
                    <p class="info-value">{{ $prescription->patient->mobile ?? '—' }}</p>
                </div>
                <div class="col-4">
                    <p class="info-label">Age</p>
                    <p class="info-value">{{ $prescription->patient->age ?? '—' }}</p>
                </div>
                <div class="col-4">
                    <p class="info-label">Gender</p>
                    <p class="info-value">{{ $prescription->patient->gender ?? '—' }}</p>
                </div>
                <div class="col-4">
                    <p class="info-label">Weight</p>
                    <p class="info-value">{{ $prescription->patient->weight ?? '—' }}</p>
                </div>
                <div class="col-6">
                    <p class="info-label">Blood Group</p>
                    <p class="info-value">{{ $prescription->patient->blood_group ?? '—' }}</p>
                </div>
                <div class="col-6">
                    <p class="info-label">Address</p>
                    <p class="info-value">{{ $prescription->patient->address ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="panel-card mb-4">
            <h6><i class="fa-solid fa-heart-pulse text-danger me-1"></i> Clinical Vitals</h6>
            <div class="row g-3">
                <div class="col-4">
                    <p class="info-label">BP</p>
                    <p class="info-value">{{ $prescription->bp ?? '—' }}</p>
                </div>
                <div class="col-4">
                    <p class="info-label">Pulse</p>
                    <p class="info-value">{{ $prescription->pulse ?? '—' }}</p>
                </div>
                <div class="col-4">
                    <p class="info-label">Temp</p>
                    <p class="info-value">{{ $prescription->temperature ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <h6><i class="fa-solid fa-stethoscope text-success me-1"></i> C/C & Diagnosis</h6>
            <p class="info-label">Clinical Complaints</p>
            <p class="info-value fw-normal mb-3">{{ $prescription->clinical_complaints ?: '—' }}</p>
            <p class="info-label">Diagnosis</p>
            <p class="info-value fw-normal mb-3">{{ $prescription->diagnosis ?: '—' }}</p>
            <p class="info-label">Risk Factors</p>
            <p class="info-value fw-normal">{{ $prescription->risk_factors ?: '—' }}</p>
        </div>
    </div>

    <!-- RIGHT: Medicines + Advice -->
    <div class="col-12 col-lg-8">
        <div class="panel-card mb-4">
            <h6><span class="text-primary font-serif fst-italic fs-5 me-1">Rx</span> Medicines</h6>
            <div class="table-responsive">
                <table class="table mb-0 med-table">
                    <thead>
                        <tr>
                            <th>Medicine</th>
                            <th>Dose</th>
                            <th>Duration</th>
                            <th>Instruction</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prescription->medicines as $med)
                            <tr>
                                <td class="fw-semibold">💊 {{ $med->displayName() }}</td>
                                <td>{{ $med->doseLabel() }}</td>
                                <td>{{ $med->duration ?? '—' }}</td>
                                <td>{{ $med->instruction ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary py-4">কোনো মেডিসিন যোগ করা হয়নি।</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel-card mb-4">
            <h6><i class="fa-solid fa-vial text-info me-1"></i> Investigations</h6>
            <p class="info-value fw-normal mb-0">{{ $prescription->investigations ?: '—' }}</p>
        </div>

        <div class="panel-card mb-4">
            <h6><i class="fa-solid fa-comment-medical text-primary me-1"></i> Advice</h6>
            <p class="info-value fw-normal mb-0">{{ $prescription->advice ?: '—' }}</p>
        </div>

        <div class="panel-card">
            <h6><i class="fa-solid fa-calendar-check text-warning me-1"></i> Follow-up</h6>
            <p class="info-value fw-normal mb-0">{{ optional($prescription->followup_date)->format('d M Y') ?? '—' }}</p>
        </div>
    </div>
</div>
@endsection
