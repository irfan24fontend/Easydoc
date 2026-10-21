@extends('layouts.admin')

@section('title', 'Recharge Requests')

@push('styles')
<style>
    .rr-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,.05); }
    .rr-table th { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#475569; background:#f1f5f9; border-bottom:2px solid #e2e8f0; padding:.9rem 1.1rem; white-space:nowrap; }
    .rr-table td { font-size:.86rem; color:#334155; padding:.9rem 1.1rem; vertical-align:middle; border-bottom:1px solid #e2e8f0; }
    .rr-badge { display:inline-block; font-size:.72rem; font-weight:700; padding:.28rem .7rem; border-radius:999px; }
    .rr-badge.pending { background:#fef3c7; color:#92400e; }
    .rr-badge.approved { background:#d1fae5; color:#065f46; }
    .rr-badge.rejected { background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h4 fw-bold text-dark m-0">Recharge Requests</h1>
        <p class="text-muted small m-0">Doctors pay by bKash and submit the TrxID. Check it against your bKash history, then approve or reject.</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" style="min-width:220px" placeholder="Search TrxID, number or doctor">
        <button class="btn btn-sm btn-dark" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
    @php
        $tabs = [
            [null, 'All', $counts['all']],
            ['pending', 'Pending', $counts['pending']],
            ['approved', 'Approved', $counts['approved']],
            ['rejected', 'Rejected', $counts['rejected']],
        ];
    @endphp
    @foreach($tabs as [$key, $label, $count])
        <a href="{{ route('admin.recharge-requests.index', $key ? ['status' => $key] : []) }}"
           class="btn btn-sm rounded-pill px-3 {{ $status === $key ? 'btn-dark' : 'btn-light border' }}">
            {{ $label }}
            <span class="badge {{ $key === 'pending' && $count > 0 ? 'bg-warning text-dark' : 'bg-secondary-subtle text-secondary' }} ms-1">{{ $count }}</span>
        </a>
    @endforeach
</div>

<div class="rr-card">
    <div class="table-responsive">
        <table class="table rr-table mb-0 align-middle">
            <thead>
                <tr><th>Date</th><th>Doctor</th><th>Amount</th><th>bKash number</th><th>TrxID</th><th>Status</th><th class="text-end">Action</th></tr>
            </thead>
            <tbody>
            @forelse($requests as $r)
                <tr>
                    <td class="text-nowrap">{{ $r->created_at->format('d M Y') }}<div class="small text-muted">{{ $r->created_at->format('h:i A') }}</div></td>
                    <td>
                        <div class="fw-semibold">{{ $r->doctor?->displayName() ?? '—' }}</div>
                        <div class="small text-muted">{{ $r->doctor?->bmdc_number }}</div>
                    </td>
                    <td class="fw-bold">৳{{ number_format((float) $r->amount, 2) }}</td>
                    <td>{{ $r->sender_number }}</td>
                    <td><code>{{ $r->trx_id }}</code>@if($r->doctor_note)<div class="small text-muted mt-1">{{ $r->doctor_note }}</div>@endif</td>
                    <td>
                        <span class="rr-badge {{ $r->status }}">{{ ucfirst($r->status) }}</span>
                        @if($r->isRejected() && $r->admin_note)<div class="small text-danger mt-1">{{ $r->admin_note }}</div>@endif
                        @if($r->reviewed_at)<div class="small text-muted mt-1">{{ $r->reviewer?->name }} &middot; {{ $r->reviewed_at->format('d M, h:i A') }}</div>@endif
                    </td>
                    <td class="text-end text-nowrap">
                        @if($r->isPending())
                            <form method="POST" action="{{ route('admin.recharge-requests.approve', $r->id) }}" class="d-inline"
                                  data-confirm="Approve ৳{{ number_format((float) $r->amount, 2) }} for {{ $r->doctor?->displayName() }}? TrxID: {{ $r->trx_id }}. Check it in your bKash history first.">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success"><i class="fa-solid fa-check me-1"></i> Approve</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger rr-reject" data-id="{{ $r->id }}"
                                    data-info="{{ $r->doctor?->displayName() }} · ৳{{ number_format((float) $r->amount, 2) }} · {{ $r->trx_id }}">
                                <i class="fa-solid fa-xmark me-1"></i> Reject
                            </button>
                        @elseif($r->isApproved() && $r->invoice)
                            <a href="{{ route('admin.invoices.show', $r->invoice->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file-invoice me-1"></i> {{ $r->invoice->invoice_no }}</a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">No request found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $requests->links('pagination::bootstrap-5') }}</div>

{{-- Reject modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="rejectForm" class="modal-content">
            @csrf @method('PATCH')
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="rejectTitle">Reject request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2" id="rejectInfo"></p>
                <label class="form-label fw-semibold small" for="admin_note">Reason (the doctor will read this)</label>
                <textarea id="admin_note" name="admin_note" rows="3" maxlength="300" required class="form-control @error('admin_note') is-invalid @enderror" placeholder="e.g. TrxID not found in bKash history">{{ old('admin_note') }}</textarea>
                @error('admin_note') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger px-4">Reject</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var modalEl = document.getElementById('rejectModal'), modal = new bootstrap.Modal(modalEl);
    var form = document.getElementById('rejectForm');
    var template = @json(route('admin.recharge-requests.reject', ['rechargeRequest' => '__ID__']));

    document.querySelectorAll('form[data-confirm]').forEach(function (f) {
        f.addEventListener('submit', function (e) { if (!confirm(f.getAttribute('data-confirm'))) e.preventDefault(); });
    });

    document.querySelectorAll('.rr-reject').forEach(function (b) {
        b.addEventListener('click', function () {
            form.action = template.replace('__ID__', b.getAttribute('data-id'));
            document.getElementById('rejectInfo').textContent = b.getAttribute('data-info');
            document.getElementById('admin_note').value = '';
            modal.show();
        });
    });
})();
</script>
@endpush
