@extends('layouts.admin')

@section('title', 'Doctor Details')

@section('content')
<!-- Custom Modern CSS Extensions (Matching Onboarding Workspace Style) -->
<style>
    .profile-workspace {
        background-color: #f8fafc;
        min-height: 100vh;
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
    }
    .data-row {
        padding: 0.875rem 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    .data-row:last-child {
        border-bottom: none;
    }
    .meta-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    .meta-value {
        font-size: 0.95rem;
        color: #1e293b;
        font-weight: 500;
    }
    .icon-wrapper {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
</style>

<div class="profile-workspace py-5">
    <div class="container max-w-[1200px]">
        
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <span class="badge text-uppercase tracking-wider px-2.5 py-1 mb-2 font-semibold" style="font-size: 0.65rem; background-color: #e0e7ff; color: #4338ca;">Practitioner Record</span>
                <h1 class="h2 fw-bold text-slate-900 m-0">Doctor Profile</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.doctors.edit', $doctor->id) }}" class="btn btn-primary rounded-pill px-4 small fw-medium shadow-sm d-flex align-items-center gap-2" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); border: none;">
                    <i class="fas fa-edit"></i> Modify Record
                </a>
                <a href="{{ route('admin.doctors.index') }}" class="btn btn-light border rounded-pill px-4 text-secondary small fw-medium">
                    <i class="fas fa-arrow-left me-2"></i>Back to Roster
                </a>
            </div>
        </div>

        {{-- Registration review --}}
        <div class="glass-card p-4 mb-4" style="border-left: 5px solid {{ $doctor->isPending() ? '#f59e0b' : ($doctor->isRejected() ? '#dc2626' : '#10b981') }};">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <div class="meta-label mb-1">Registration status</div>
                    @if($doctor->isPending())
                        <span class="badge rounded-pill px-3 py-2" style="background:#fef3c7;color:#92400e;">Pending review</span>
                        <p class="small text-muted mt-2 mb-0">Check the BMDC number and certificate below, then approve or reject.</p>
                    @elseif($doctor->isRejected())
                        <span class="badge rounded-pill px-3 py-2" style="background:#fee2e2;color:#991b1b;">Rejected</span>
                        @if($doctor->rejection_reason)<p class="small mt-2 mb-0"><strong>Reason:</strong> {{ $doctor->rejection_reason }}</p>@endif
                    @else
                        <span class="badge rounded-pill px-3 py-2" style="background:#d1fae5;color:#065f46;">Approved</span>
                        @if($doctor->approved_at)<p class="small text-muted mt-2 mb-0">Approved on {{ $doctor->approved_at->format('d M Y, h:i A') }}</p>@endif
                    @endif
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    @if($doctor->bmdc_certificate)
                        <a href="{{ route('admin.doctors.certificate', $doctor->id) }}" target="_blank" rel="noopener" class="btn btn-outline-dark rounded-pill px-4">
                            <i class="fas fa-file-shield me-1"></i> View BMDC certificate
                        </a>
                    @endif
                    @unless($doctor->isApproved())
                        <form method="POST" action="{{ route('admin.doctors.approve', $doctor->id) }}" class="m-0" onsubmit="return confirm('Approve this doctor?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-success rounded-pill px-4"><i class="fas fa-check me-1"></i> Approve</button>
                        </form>
                    @endunless
                </div>
            </div>

            @unless($doctor->isRejected())
                <form method="POST" action="{{ route('admin.doctors.reject', $doctor->id) }}" class="mt-4 pt-3 border-top" onsubmit="return confirm('{{ $doctor->isApproved() ? 'Suspend this doctor? They will lose access immediately.' : 'Reject this application?' }}')">
                    @csrf @method('PATCH')
                    <label class="meta-label mb-2" for="rejection_reason">{{ $doctor->isApproved() ? 'Suspend access (reason is shown to the doctor)' : 'Reject application (reason is shown to the doctor)' }}</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <input type="text" id="rejection_reason" name="rejection_reason" value="{{ old('rejection_reason') }}" minlength="5" maxlength="500" required
                               class="form-control @error('rejection_reason') is-invalid @enderror" style="max-width: 560px;" placeholder="e.g. BMDC number does not match the certificate">
                        <button type="submit" class="btn btn-outline-danger rounded-pill px-4">{{ $doctor->isApproved() ? 'Suspend' : 'Reject' }}</button>
                    </div>
                    @error('rejection_reason') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </form>
            @endunless
        </div>

        <div class="row g-4">
            <!-- Left Column: Primary Identity Card -->
            <div class="col-lg-4">
                <div class="glass-card text-center p-4">
                    
                    <!-- Portrait Box -->
                    <div class="mb-4 mt-2 d-inline-block position-relative">
                        @if($doctor->photo)
                            <img src="{{ asset('storage/'.$doctor->photo) }}" 
                                 class="rounded-circle border border-4 border-white shadow-sm" 
                                 style="width: 140px; height: 140px; object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border border-2 border-dashed border-secondary shadow-sm" style="width: 140px; height: 140px;">
                                <i class="fas fa-user-md fa-4x text-secondary opacity-40"></i>
                            </div>
                        @endif
                        
                        <!-- Mini Badge for System Designation Status -->
                        <span class="position-absolute bottom-0 end-0 bg-emerald-500 text-white rounded-circle p-2 d-flex align-items-center justify-content-center shadow" style="width: 32px; height: 32px;" title="Active Provider">
                            <i class="fas fa-check text-white small" style="font-size: 10px;"></i>
                        </span>
                    </div>

                    <!-- Primary Typography Identification -->
                    <h3 class="fw-bold text-slate-900 mb-1">{{ $doctor->user->name }}</h3>
                    <p class="text-indigo-600 fw-semibold mb-3 small">{{ $doctor->designation ?? 'Medical Practitioner' }}</p>
                    
                    <div class="bg-light rounded-3 p-3 mb-2 text-start">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-semibold">Identifier Code:</span>
                            <span class="badge bg-dark tracking-wide font-monospace py-1.5 px-2.5">{{ $doctor->doctor_code ?? 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-semibold">Experience State:</span>
                            <span class="text-slate-800 fw-bold small"><i class="fas fa-history text-muted me-1"></i> {{ $doctor->experience ?? '0 Years' }}</span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right Column: Verification & Credential Details Matrix -->
            <div class="col-lg-8">
                
                <!-- Section 1: Security & Communications Mapping -->
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="icon-wrapper" style="background-color: #e0e7ff; color: #4f46e5;">
                            <i class="fas fa-address-book"></i>
                        </div>
                        <h5 class="fw-bold m-0 text-slate-800">Communication Variables</h5>
                    </div>

                    <div class="px-2">
                        <div class="data-row d-flex align-items-center justify-content-between">
                            <span class="meta-label">Primary Inbox Route</span>
                            <span class="meta-value text-indigo-600 fw-semibold"><i class="far fa-envelope me-1 opacity-75"></i> {{ $doctor->user->email }}</span>
                        </div>
                        <div class="data-row d-flex align-items-center justify-content-between">
                            <span class="meta-label">Mobile Sub-Connection</span>
                            <span class="meta-value"><i class="fas fa-phone-alt me-1 text-muted small"></i> {{ $doctor->user->phone ?? 'Not Linked' }}</span>
                        </div>
                        <div class="data-row d-flex align-items-center justify-content-between">
                            <span class="meta-label">Biological Gender</span>
                            <span class="meta-value">
                                <span class="badge rounded-pill bg-light text-slate-700 px-3 py-1.5 border">{{ $doctor->gender ?? 'Unspecified' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Clinical Profiling & Domain Expertise -->
                <div class="glass-card p-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="icon-wrapper" style="background-color: #d1fae5; color: #10b981;">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h5 class="fw-bold m-0 text-slate-800">Domain Matrix Credentials</h5>
                    </div>

                    <div class="px-2">
                        <div class="data-row d-flex align-items-center justify-content-between">
                            <span class="meta-label">BMDC Verification ID</span>
                            <span class="meta-value font-monospace fw-bold text-emerald-700"><i class="fas fa-id-card-alt me-1 opacity-75"></i> {{ $doctor->bmdc_number }}</span>
                        </div>
                        <div class="data-row d-flex align-items-center justify-content-between">
                            <span class="meta-label">Domain Speciality Focus</span>
                            <span class="meta-value text-slate-900 fw-bold">{{ $doctor->speciality ?? 'General Practice' }}</span>
                        </div>
                        <div class="data-row d-flex align-items-center justify-content-between">
                            <span class="meta-label">Academic Qualifications</span>
                            <span class="meta-value text-muted">{{ $doctor->qualification }}</span>
                        </div>
                        <div class="data-row d-flex align-items-center justify-content-between">
                            <span class="meta-label">Medical College</span>
                            <span class="meta-value">{{ $doctor->medical_college ?? '—' }}</span>
                        </div>
                        <div class="data-row d-flex align-items-center justify-content-between">
                            <span class="meta-label">Date of Birth</span>
                            <span class="meta-value">{{ $doctor->date_of_birth?->format('d M Y') ?? '—' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection