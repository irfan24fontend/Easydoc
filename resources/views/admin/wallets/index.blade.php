@extends('layouts.admin')

@section('title', 'Doctor Wallets')

@section('content')
<style>
    .wallet-workspace { 
        background-color: #f8fafc; 
        min-height: 100vh; 
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .saas-table th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
        background-color: #f1f5f9;
        border-bottom: 2px solid #e2e8f0;
        padding: 1rem 1.25rem;
    }
    .saas-table td {
        font-size: 0.875rem;
        color: #334155;
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #e2e8f0;
    }
    .saas-table tr:hover td { 
        background-color: #f8fafc; 
    }
    .btn-action-text {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 0.4rem 0.75rem;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
    }
    .btn-action-text.recharge { 
        color: #047857; 
        background-color: #d1fae5; 
        border: 1px solid #a7f3d0; 
    }
    .btn-action-text.recharge:hover { 
        background-color: #059669; 
        color: #ffffff; 
    }
</style>

<div class="wallet-workspace py-4">
    <div class="container-fluid px-4">

        <!-- Header Panel -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 fw-bold text-dark m-0">Doctor Wallets</h1>
                <p class="text-muted small m-0">Monitor balances and issue account credits to medical practitioners.</p>
            </div>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-light border px-4 fw-medium text-secondary">
                <i class="fa-solid fa-file-invoice me-1"></i> Recharge Invoices
            </a>
        </div>

        <!-- Wallet Data Grid -->
        <div class="glass-card overflow-hidden">
            <div class="table-responsive">
                <table class="table saas-table m-0 border-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th>Doctor</th>
                            <th>Phone</th>
                            <th>Doctor Code</th>
                            <th>Current Balance</th>
                            <th width="150" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wallets as $wallet)
                            <tr>
                                <td class="text-muted fw-bold">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="fw-bold text-dark">
                                    {{ $wallet->doctor->user->name ?? 'N/A' }}
                                </td>

                                <td class="text-secondary fw-medium">
                                    {{ $wallet->doctor->user->phone ?? '—' }}
                                </td>

                                <td>
                                    <span class="font-monospace fw-bold px-2 py-1 rounded" style="font-size: 0.85rem; background-color: #f1f5f9; color: #475569;">
                                        {{ $wallet->doctor->doctor_code ?? '—' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="fw-bold px-2.5 py-1 rounded" style="font-size: 0.9rem; background-color: #eff6ff; color: #1d4ed8;">
                                        ৳ {{ number_format($wallet->balance, 2) }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('admin.wallets.create', $wallet->id) }}" class="btn-action-text recharge">
                                        + Recharge
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted fw-medium">
                                    No Doctor Wallet Found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($wallets, 'hasPages') && $wallets->hasPages())
                <div class="px-4 py-3 border-top bg-light">
                    {{ $wallets->links() }}
                </div>
            @endif

        </div>
    </div>
</div>
@endsection