@extends('layouts.admin')

@section('title', 'Custom Wallet Charge')

@push('styles')
<style>
    .charge-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 8px 24px rgba(15, 23, 42, .06); }
    .charge-label { color: #475569; display: block; font-size: .8rem; font-weight: 700; letter-spacing: .04em; margin-bottom: .45rem; }
    .charge-input { border: 1px solid #cbd5e1; border-radius: 9px; padding: .75rem .9rem; }
    .charge-input:focus { border-color: #0c8c84; box-shadow: 0 0 0 3px rgba(12, 140, 132, .12); }
    .balance-box { background: #0f172a; border-radius: 12px; color: white; padding: 1.25rem; }
    .balance-box .muted { color: #94a3b8; font-size: .85rem; }
</style>
@endpush

@section('content')
@php
    $doctorName = $wallet->doctor?->user?->name ?? 'Doctor';
    $balance = (float) $wallet->balance;
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h1 class="h4 fw-bold text-dark mb-1">Custom wallet charge</h1>
        <p class="text-muted small mb-0">Deduct a one-time amount and record the reason in the doctor's wallet log.</p>
    </div>
    <a href="{{ route('admin.wallets.index') }}" class="btn btn-light border">Back to wallets</a>
</div>

@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="charge-card p-4">
            <div class="mb-4">
                <div class="text-muted small">Doctor</div>
                <div class="fw-bold fs-5">{{ $wallet->doctor?->displayName() ?? $doctorName }}</div>
                <div class="text-muted small">{{ $wallet->doctor?->doctor_code ?? '' }}</div>
            </div>

            <form method="POST" action="{{ route('admin.wallets.charge.store', $wallet->id) }}" id="customChargeForm">
                @csrf
                <input type="hidden" name="charge_token" value="{{ old('charge_token', $chargeToken) }}">

                <div class="mb-3">
                    <label for="amount" class="charge-label">Charge amount (৳)</label>
                    <input type="number" name="amount" id="amount" min="0.01" max="{{ number_format($balance, 2, '.', '') }}" step="0.01" required
                        value="{{ old('amount') }}" class="form-control charge-input @error('amount') is-invalid @enderror"
                        placeholder="0.00" inputmode="decimal">
                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">The charge cannot exceed the current wallet balance.</div>
                </div>

                <div class="mb-4">
                    <label for="description" class="charge-label">Reason</label>
                    <textarea name="description" id="description" rows="3" maxlength="238" required
                        class="form-control charge-input @error('description') is-invalid @enderror"
                        placeholder="Explain what this charge is for">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-danger px-4" id="submitCharge">
                        <i class="fa-solid fa-minus me-1"></i> Deduct from wallet
                    </button>
                    <a href="{{ route('admin.wallets.index') }}" class="btn btn-light border px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="balance-box">
            <div class="muted">Current balance</div>
            <div class="fs-3 fw-bold mb-3">৳{{ number_format($balance, 2) }}</div>
            <div class="d-flex justify-content-between gap-3 border-top border-secondary pt-3">
                <span class="muted">Balance after charge</span>
                <strong id="balanceAfter">৳{{ number_format($balance, 2) }}</strong>
            </div>
        </div>
        <div class="small text-muted mt-3">
            This creates a debit transaction. The amount, reason, and resulting balance will appear in the doctor's wallet log.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const balance = @json($balance);
        const amount = document.getElementById('amount');
        const balanceAfter = document.getElementById('balanceAfter');
        const form = document.getElementById('customChargeForm');
        const submit = document.getElementById('submitCharge');
        const money = value => '৳' + Number(value).toLocaleString('en-BD', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        amount.addEventListener('input', () => {
            const charge = Number(amount.value) || 0;
            balanceAfter.textContent = money(Math.max(0, balance - charge));
        });

        form.addEventListener('submit', event => {
            const charge = Number(amount.value) || 0;
            if (charge <= 0 || charge > balance) {
                event.preventDefault();
                amount.setCustomValidity(charge > balance ? 'Charge exceeds the current wallet balance.' : 'Enter a charge amount greater than zero.');
                amount.reportValidity();
                amount.addEventListener('input', () => amount.setCustomValidity(''), { once: true });
                return;
            }
            submit.disabled = true;
            submit.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Processing…';
        });
    })();
</script>
@endpush
