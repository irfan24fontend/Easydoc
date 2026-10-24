@extends('layouts.admin')

@section('content')
<style>
    .cr-back { display: inline-flex; align-items: center; gap: 6px; color: #64748b; text-decoration: none; font-weight: 700; font-size: .82rem; margin-bottom: .5rem; }
    .cr-back:hover { color: #4f46e5; }
    .cr-title { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0; }

    .cr-card { background: #fff; border: 1px solid #eef0f4; border-radius: 16px; padding: 1.3rem 1.4rem; box-shadow: 0 1px 2px rgba(0,0,0,.03); }
    .cr-card + .cr-card { margin-top: 1rem; }
    .cr-card h6 { font-weight: 800; color: #0f172a; font-size: .88rem; margin-bottom: 1rem; }

    .cr-dl dt { font-size: .7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }
    .cr-dl dd { font-size: .88rem; color: #0f172a; font-weight: 600; margin: .1rem 0 .85rem; }

    .cr-pad { max-height: 30rem; width: 100%; border-radius: 12px; border: 1px solid #eef0f4; object-fit: contain; background: #f8fafc; }

    .cr-tpl-select { border: 1px solid #e2e8f0; border-radius: 12px; padding: .6rem .9rem; font-size: .89rem; background: #f8fafc; }
    .cr-tpl-select:focus { background: #fff; border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79,70,229,.1); }

    .cr-btn-approve { border: 0; border-radius: 12px; padding: .6rem 1.2rem; font-weight: 800; font-size: .86rem; color: #fff; background: linear-gradient(135deg,#16a34a,#15803d); box-shadow: 0 8px 18px rgba(21,128,61,.25); }
    .cr-btn-approve:hover { color: #fff; filter: brightness(1.05); }
    .cr-btn-reject { border: 1px solid #fecaca; border-radius: 12px; padding: .55rem 1.2rem; font-weight: 800; font-size: .86rem; color: #b91c1c; background: #fef2f2; }
    .cr-btn-reject:hover { background: #fee2e2; color: #b91c1c; }
    .cr-btn-toggle { border: 0; border-radius: 12px; padding: .6rem 1.2rem; font-weight: 800; font-size: .86rem; color: #fff; }

    .cr-note { font-size: .78rem; color: #64748b; margin-bottom: .9rem; }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <a href="{{ route('admin.chamber-requests.index') }}" class="cr-back"><i class="fa-solid fa-arrow-left"></i> Back to requests</a>
        <h1 class="cr-title">{{ $chamber->chamber_name }}</h1>
    </div>
    @include('partials.chamber-status-badge', ['status' => $chamber->display_status])
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius:14px;">
        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2 mb-4" style="border-radius:14px;">
        <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
    </div>
@endif

<div class="row g-4">

    {{-- Details --}}
    <div class="col-lg-5">
        <div class="cr-card">
            <h6><i class="fa-solid fa-circle-info text-primary me-1"></i> Request details</h6>
            <dl class="cr-dl mb-0">
                <dt>Doctor</dt>
                <dd>{{ $chamber->doctor->name ?? ($chamber->doctor->user->name ?? 'Doctor #'.$chamber->doctor_id) }}</dd>

                <dt>Hospital / clinic</dt>
                <dd>{{ $chamber->hospital_name }}</dd>

                <dt>Phone</dt>
                <dd>{{ $chamber->phone }}</dd>

                @if($chamber->email)
                    <dt>Email</dt>
                    <dd>{{ $chamber->email }}</dd>
                @endif

                <dt>Address</dt>
                <dd>{{ $chamber->address }}</dd>

                <dt>Visiting days</dt>
                <dd>{{ $chamber->visiting_days }}</dd>

                <dt>Visiting time</dt>
                <dd>{{ $chamber->visiting_time }}</dd>

                <dt>Requested</dt>
                <dd>{{ optional($chamber->requested_at)->format('d M Y, h:i A') ?? '-' }}</dd>

                @if($chamber->template)
                    <dt>Assigned template</dt>
                    <dd>{{ $chamber->template->name }}</dd>
                @endif

                @if($chamber->reject_reason)
                    <dt>Reject reason</dt>
                    <dd class="text-danger">{{ $chamber->reject_reason }}</dd>
                @endif
            </dl>
        </div>
    </div>

    {{-- Pad image + actions --}}
    <div class="col-lg-7">
        <div class="cr-card">
            <h6><i class="fa-solid fa-file-image text-primary me-1"></i> Prescription pad</h6>
            @if($chamber->pad_image)
                <a href="{{ asset('storage/'.$chamber->pad_image) }}" target="_blank" rel="noopener">
                    <img src="{{ asset('storage/'.$chamber->pad_image) }}" alt="Prescription pad" class="cr-pad">
                </a>
            @else
                <p class="text-muted small mb-0">No pad image uploaded.</p>
            @endif
        </div>

        @if($chamber->request_status === 'waiting_request')
            <div class="cr-card">
                <h6><i class="fa-solid fa-file-medical text-primary me-1"></i> Assign template and activate</h6>
                <p class="cr-note mb-3">
                    Create a template from the pad image first, then pick it here.
                    @if(Route::has('admin.templates.create'))
                        <a href="{{ route('admin.templates.create') }}" class="text-decoration-none fw-bold">Create template</a>
                    @endif
                </p>

                <form method="POST" action="{{ route('admin.chamber-requests.approve', $chamber) }}" class="d-flex flex-wrap gap-2 align-items-stretch mb-4">
                    @csrf
                    <select name="prescription_template_id" class="cr-tpl-select flex-grow-1" style="min-width:14rem;" required>
                        <option value="">Select template</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" @selected(old('prescription_template_id') == $template->id)>{{ $template->name }} ({{ $template->paper_size }})</option>
                        @endforeach
                    </select>
                    <button class="cr-btn-approve"><i class="fa-solid fa-check me-1"></i> Activate chamber</button>
                </form>

                <hr class="my-3">

                <form method="POST" action="{{ route('admin.chamber-requests.reject', $chamber) }}">
                    @csrf
                    <label class="form-label fw-bold small">Reject request</label>
                    <p class="cr-note">৳{{ $fee }} will be refunded to the doctor's wallet.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <input type="text" name="reject_reason" value="{{ old('reject_reason') }}" placeholder="Reason shown to the doctor" class="cr-tpl-select flex-grow-1" style="min-width:14rem;" required>
                        <button class="cr-btn-reject"><i class="fa-solid fa-xmark me-1"></i> Reject and refund</button>
                    </div>
                </form>
            </div>

        @elseif($chamber->request_status === 'approved')
            <div class="cr-card">
                <form method="POST" action="{{ route('admin.chamber-requests.toggle', $chamber) }}">
                    @csrf @method('PATCH')
                    <button class="cr-btn-toggle" style="background: {{ $chamber->status ? '#64748b' : 'linear-gradient(135deg,#16a34a,#15803d)' }};">
                        <i class="fa-solid fa-power-off me-1"></i> {{ $chamber->status ? 'Set inactive' : 'Set active' }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection