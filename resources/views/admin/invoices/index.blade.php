@extends('layouts.admin')

@section('title', 'Recharge Invoices')

@push('styles')
<style>
    .glass-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .saas-table th {
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
        color: #475569; background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0; padding: 1rem 1.25rem;
    }
    .saas-table td {
        font-size: 0.875rem; color: #334155; padding: 1rem 1.25rem; vertical-align: middle; border-bottom: 1px solid #e2e8f0;
    }
    .saas-table tr:hover td { background-color: #f8fafc; }
    .btn-action-text {
        font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.02em;
        padding: 0.4rem 0.75rem; border-radius: 6px; text-decoration: none; display: inline-block; transition: all 0.2s ease;
    }
    .btn-action-text.view { color: #4f46e5; background-color: #eeebff; border: 1px solid #e0e7ff; }
    .btn-action-text.view:hover { background-color: #4f46e5; color: #ffffff; }
</style>
@endpush

@section('content')
<div class="py-2">
    <div class="container-fluid px-4">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h1 class="h4 fw-bold text-dark m-0">Recharge Invoices</h1>
                <p class="text-muted small m-0">Every wallet recharge made by Super Admin, with a printable invoice.</p>
            </div>
            <a href="{{ route('admin.wallets.index') }}" class="btn btn-primary px-4 fw-semibold" style="background-color: #4f46e5; border-color: #4f46e5;">
                <i class="fa-solid fa-bolt me-1"></i> New Recharge
            </a>
        </div>

        <form method="GET" class="glass-card p-3 mb-4 d-flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="Invoice no, doctor name or reference...">
            <button class="btn btn-dark px-4 fw-semibold">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-light border">Clear</a>
            @endif
        </form>

        <div class="glass-card overflow-hidden">
            <div class="table-responsive">
                <table class="table saas-table m-0 border-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Doctor</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th width="140" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="text-muted fw-bold">{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>
                                <td>
                                    <span class="font-monospace fw-bold px-2 py-1 rounded" style="font-size: 0.85rem; background-color: #f1f5f9; color: #475569;">
                                        {{ $inv->invoice_no }}
                                    </span>
                                </td>
                                <td class="text-secondary">{{ $inv->issued_at->format('d M Y, h:i A') }}</td>
                                <td class="fw-bold text-dark">
                                    Dr. {{ $inv->doctor->user->name ?? 'N/A' }}
                                    <div class="text-muted fw-normal" style="font-size: 0.75rem;">{{ $inv->doctor->doctor_code ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-3 py-2" style="background-color:#eef2ff;color:#4338ca;font-weight:600;">
                                        {{ $inv->payment_method ?: '—' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold px-2 py-1 rounded" style="background-color:#d1fae5;color:#065f46;">
                                        ৳ {{ number_format($inv->amount, 2) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.invoices.show', $inv->id) }}" class="btn-action-text view">View / Print</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted fw-medium">No Invoice Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
                <div class="px-4 py-3 border-top bg-light">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection