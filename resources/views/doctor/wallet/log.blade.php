@extends('layouts.doctor')

@section('title', 'Wallet Log')

@push('styles')
    @include('doctor.wallet._styles')
@endpush

@section('content')
<div class="wl-hero">
    <h1><i class="fa-solid fa-receipt me-2"></i>Wallet Log</h1>
    <p>Every taka added to your wallet and every charge taken from it, with the balance after each entry.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="wl-stat"><div class="wl-label">Balance</div><div class="n">৳{{ number_format($summary['balance'], 2) }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="wl-stat"><div class="wl-label">Total added</div><div class="n text-success">৳{{ number_format($summary['total_in'], 2) }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="wl-stat"><div class="wl-label">Total spent</div><div class="n text-danger">৳{{ number_format($summary['total_out'], 2) }}</div></div></div>
    <div class="col-6 col-lg-3"><div class="wl-stat"><div class="wl-label">Spent this month</div><div class="n">৳{{ number_format($summary['this_month_out'], 2) }}</div></div></div>
</div>

<div class="wl-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h2 class="mb-0"><i class="fa-solid fa-list"></i> Transactions</h2>
        <div class="btn-group btn-group-sm" role="group" aria-label="Filter">
            <a href="{{ route('doctor.wallet.log') }}" class="btn {{ $type === null ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
            <a href="{{ route('doctor.wallet.log', ['type' => 'debit']) }}" class="btn {{ $type === 'debit' ? 'btn-dark' : 'btn-outline-secondary' }}">Spent</a>
            <a href="{{ route('doctor.wallet.log', ['type' => 'credit']) }}" class="btn {{ $type === 'credit' ? 'btn-dark' : 'btn-outline-secondary' }}">Added</a>
        </div>
    </div>

    @if($transactions->isEmpty())
        <div class="wl-empty"><i class="fa-solid fa-receipt"></i>No transaction yet.</div>
    @else
        <div class="table-responsive">
            <table class="table wl-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th><th>Description</th><th>Type</th><th class="text-end">Amount</th>
                        @unless($type)<th class="text-end">Balance after</th>@endunless
                    </tr>
                </thead>
                <tbody>
                @foreach($transactions as $tx)
                    <tr>
                        <td class="text-nowrap">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                        <td>{{ $tx->description ?: ($tx->type === 'credit' ? 'Wallet recharge' : 'Charge') }}</td>
                        <td><span class="wl-badge {{ $tx->type }}">{{ $tx->type === 'credit' ? 'Added' : 'Spent' }}</span></td>
                        <td class="text-end fw-bold {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                            {{ $tx->type === 'credit' ? '+' : '−' }}৳{{ number_format((float) $tx->amount, 2) }}
                        </td>
                        @unless($type)
                            <td class="text-end text-muted">৳{{ number_format((float) ($tx->balance_after ?? 0), 2) }}</td>
                        @endunless
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $transactions->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
