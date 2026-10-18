@extends('layouts.doctor')

@section('title', 'Prescription List')

@push('styles')
<style>
    .glass-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .saas-table th {
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
        color: #475569; background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0; padding: 1rem 1.25rem; white-space: nowrap;
    }
    .saas-table td {
        font-size: 0.875rem; color: #334155; padding: 0.9rem 1.25rem; vertical-align: middle; border-bottom: 1px solid #e2e8f0;
    }
    .saas-table tr:hover td { background-color: #f8fafc; }

    .pid-badge {
        display: inline-block; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-weight: 700; font-size: 0.8rem; padding: 0.25rem 0.6rem; border-radius: 6px;
        background: #eef2ff; color: #4338ca; border: 1px solid #e0e7ff; white-space: nowrap;
    }
    .type-badge { font-size: 0.7rem; font-weight: 700; padding: 0.3rem 0.65rem; border-radius: 999px; white-space: nowrap; display: inline-block; }
    .type-full { background: #e0e7ff; color: #3730a3; }
    .type-inv { background: #ccfbf1; color: #115e59; }
    .state-pending { background: #fef3c7; color: #92400e; }
    .state-done { background: #dcfce7; color: #166534; }

    .btn-action-text {
        font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em;
        padding: 0.4rem 0.7rem; border-radius: 6px; text-decoration: none; display: inline-block; transition: all 0.2s ease; white-space: nowrap;
    }
    .btn-action-text.view  { color: #475569; background-color: #f1f5f9; border: 1px solid #e2e8f0; }
    .btn-action-text.view:hover { background-color: #475569; color: #fff; }
    .btn-action-text.print { color: #4f46e5; background-color: #eeebff; border: 1px solid #e0e7ff; }
    .btn-action-text.print:hover { background-color: #4f46e5; color: #fff; }
    .btn-action-text.edit  { color: #b45309; background-color: #fef3c7; border: 1px solid #fde68a; }
    .btn-action-text.edit:hover { background-color: #b45309; color: #fff; }
    .btn-action-text.cont  { color: #fff; background-color: #f59e0b; border: 1px solid #f59e0b; }
    .btn-action-text.cont:hover { background-color: #d97706; border-color: #d97706; }
</style>
@endpush

@section('content')
<div class="py-2">
    <div class="container-fluid px-0">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h1 class="h4 fw-bold text-dark m-0">Prescription List</h1>
                <p class="text-muted small m-0">আপনার সব প্রেসক্রিপশন — Patient ID, নাম বা মোবাইল দিয়ে খুঁজুন।</p>
            </div>
            <a href="{{ route('doctor.prescriptions.create') }}" class="btn btn-primary px-4 fw-semibold" style="background-color: #4f46e5; border-color: #4f46e5;">
                <i class="fa-solid fa-plus me-1"></i> New Prescription
            </a>
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('doctor.prescriptions.index') }}" class="glass-card p-3 mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-lg-5">
                    <label class="form-label small fw-semibold text-secondary mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="Patient ID (PT-0007), name or mobile">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button class="btn btn-dark px-4 fw-semibold flex-fill"><i class="fa-solid fa-magnifying-glass me-1"></i> Filter</button>
                    @if(request()->hasAny(['search', 'date_from', 'date_to']))
                        <a href="{{ route('doctor.prescriptions.index') }}" class="btn btn-light border">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="glass-card overflow-hidden">
            <div class="table-responsive">
                <table class="table saas-table m-0 border-0">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Patient ID</th>
                            <th>Patient</th>
                            <th>Mobile</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prescriptions as $p)
                            @php
                                $patient = $p->patient;
                                $isInv = $p->type === 'investigation_only';
                                $hasFollowUp = ($p->children_count ?? 0) > 0;
                                $meta = collect([
                                    $patient?->age ? $patient->age . ' yrs' : null,
                                    $patient?->gender,
                                ])->filter()->implode(' · ');
                            @endphp
                            <tr>
                                <td class="text-muted fw-bold">{{ $loop->iteration + ($prescriptions->currentPage() - 1) * $prescriptions->perPage() }}</td>

                                <td>
                                    <span class="pid-badge">{{ $patient?->patient_code ?? '—' }}</span>
                                </td>

                                <td>
                                    <div class="fw-bold text-dark">{{ $patient->name ?? 'N/A' }}</div>
                                    @if($meta)<div class="text-muted" style="font-size: 0.75rem;">{{ $meta }}</div>@endif
                                </td>

                                <td class="text-secondary">{{ $patient->mobile ?? '—' }}</td>

                                <td>
                                    @if($isInv)
                                        <span class="type-badge type-inv"><i class="fa-solid fa-flask me-1"></i>Investigation</span>
                                        @if($hasFollowUp)
                                            <span class="type-badge state-done ms-1">Report দেখা হয়েছে</span>
                                        @else
                                            <span class="type-badge state-pending ms-1">Report pending</span>
                                        @endif
                                    @else
                                        <span class="type-badge type-full"><i class="fa-solid fa-file-medical me-1"></i>Prescription</span>
                                    @endif
                                </td>

                                <td class="text-secondary" style="white-space: nowrap;">
                                    {{ $p->created_at->format('d M Y') }}
                                    <div class="text-muted" style="font-size: 0.72rem;">{{ $p->created_at->format('h:i A') }}</div>
                                </td>

                                <td class="text-end" style="white-space: nowrap;">
                                    @if($isInv && !$hasFollowUp)
                                        <a href="{{ route('doctor.prescriptions.create', ['from' => $p->id]) }}" class="btn-action-text cont">Continue with Reports</a>
                                    @endif
                                    <a href="{{ route('doctor.prescriptions.show', $p->id) }}" class="btn-action-text view">View</a>
                                    <a href="{{ route('doctor.prescriptions.print', $p->id) }}" target="_blank" class="btn-action-text print">Print</a>
                                    <a href="{{ route('doctor.prescriptions.edit', $p->id) }}" class="btn-action-text edit">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted fw-medium">কোনো প্রেসক্রিপশন পাওয়া যায়নি।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($prescriptions->hasPages())
                <div class="px-4 py-3 border-top bg-light">
                    {{ $prescriptions->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection