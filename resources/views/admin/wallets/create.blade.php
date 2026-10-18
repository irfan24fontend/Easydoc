@extends('layouts.admin')

@section('title', 'Recharge Wallet')

@push('styles')
<style>
    .form-workspace { background-color: #f8fafc; }
    .glass-card {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .modern-input, .modern-textarea {
        border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.625rem 0.875rem;
        font-size: 0.875rem; transition: all 0.2s ease; color: #334155;
    }
    .modern-input:focus, .modern-textarea:focus {
        border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1); outline: none;
    }
    .form-label-custom {
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.05em; color: #475569; margin-bottom: 0.5rem; display: block;
    }
    .section-title {
        font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 1.25rem;
        padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; gap: 10px;
    }
    .section-title .ico {
        width: 28px; height: 28px; border-radius: 8px; background: #eef2ff; color: #4f46e5;
        display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem;
    }

    .amount-wrap { position: relative; }
    .amount-wrap .cur { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.4rem; font-weight: 700; }
    .amount-input { font-size: 1.6rem; font-weight: 800; padding: 0.8rem 1rem 0.8rem 2.6rem; color: #0f172a; }

    .quick-amt {
        border: 1px solid #e2e8f0; background: #f8fafc; color: #475569; font-weight: 600; font-size: 0.82rem;
        border-radius: 999px; padding: 0.35rem 0.95rem; transition: all .15s ease;
    }
    .quick-amt:hover { background: #eef2ff; border-color: #c7d2fe; color: #4338ca; }

    .pay-option { position: relative; margin: 0; }
    .pay-option input { position: absolute; opacity: 0; pointer-events: none; }
    .pay-card {
        display: flex; align-items: center; gap: 10px; border: 2px solid #f1f5f9; border-radius: 12px;
        padding: 0.7rem 0.85rem; cursor: pointer; transition: all .15s ease; background: #fff; height: 100%;
    }
    .pay-card:hover { border-color: #cbd5e1; }
    .pay-card .pi {
        width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; flex-shrink: 0;
    }
    .pay-card .pn { font-size: 0.85rem; font-weight: 600; color: #334155; }
    .pay-option input:checked + .pay-card { border-color: #4f46e5; background: #eef2ff; box-shadow: 0 2px 8px rgba(79,70,229,.12); }

    .doc-banner { height: 64px; border-radius: 12px 12px 0 0; background: linear-gradient(135deg, #4f46e5, #7c3aed); }
    .doc-avatar {
        width: 62px; height: 62px; border-radius: 16px; background: #4f46e5; color: #fff; font-weight: 800;
        font-size: 1.25rem; display: flex; align-items: center; justify-content: center;
        border: 4px solid #fff; margin-top: -32px; box-shadow: 0 4px 10px rgba(0,0,0,.1);
    }
    .wallet-summary { background: #0f172a; color: #fff; border-radius: 12px; padding: 1.4rem; }
    .wallet-summary .lbl { color: #94a3b8; font-size: .85rem; }
    .wallet-summary .row-line { display: flex; justify-content: space-between; margin-bottom: .7rem; font-size: .9rem; }
    .wallet-summary .grand { border-top: 1px solid #334155; padding-top: .8rem; margin-top: .2rem; display: flex; justify-content: space-between; align-items: flex-end; }
    .wallet-summary .grand .v { font-size: 1.6rem; font-weight: 800; }

    .confirm-row { display: flex; justify-content: space-between; padding: .65rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: .9rem; }
    .confirm-row:last-child { border-bottom: 0; }
</style>
@endpush

@section('content')
@php
    $doctor      = $wallet->doctor;
    $doctorName  = $doctor?->user?->name ?? 'N/A';
    $balance     = (float) ($wallet->balance ?? 0);
    $methods     = [
        'Cash'          => ['icon' => 'fa-money-bill-wave',  'bg' => '#d1fae5', 'fg' => '#059669'],
        'bKash'         => ['icon' => 'fa-mobile-screen',    'bg' => '#fce7f3', 'fg' => '#db2777'],
        'Nagad'         => ['icon' => 'fa-mobile-screen',    'bg' => '#ffedd5', 'fg' => '#ea580c'],
        'Rocket'        => ['icon' => 'fa-mobile-screen',    'bg' => '#f3e8ff', 'fg' => '#9333ea'],
        'Bank Transfer' => ['icon' => 'fa-building-columns', 'bg' => '#e0f2fe', 'fg' => '#0284c7'],
    ];
    $selectedMethod = old('payment_method', 'Cash');
@endphp

<div class="form-workspace py-2">
    <div class="container-fluid px-4">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h1 class="h4 fw-bold text-dark m-0">Recharge Doctor Wallet</h1>
                <p class="text-muted small m-0">Deposit balance and record transaction context. An invoice is generated automatically.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-light border px-3 fw-medium text-secondary">
                    <i class="fa-solid fa-file-invoice me-1"></i> Invoices
                </a>
                <a href="{{ route('admin.wallets.index') }}" class="btn btn-light border px-4 fw-medium text-secondary">
                    Back to Wallets
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>ফর্মে কিছু সমস্যা আছে:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">

            <!-- ============ FORM ============ -->
            <div class="col-lg-7">
                <form method="POST" action="{{ route('admin.wallets.store', $wallet->id) }}" id="rechargeForm">
                    @csrf

                    <!-- Amount -->
                    <div class="glass-card p-4 mb-4">
                        <div class="section-title"><span class="ico"><i class="fa-solid fa-coins"></i></span> Recharge Amount</div>

                        <label for="amount" class="form-label-custom">Amount (৳) *</label>
                        <div class="amount-wrap">
                            <span class="cur">৳</span>
                            <input type="number" step="0.01" min="1" name="amount" id="amount" placeholder="0.00" required
                                   value="{{ old('amount') }}"
                                   class="form-control modern-input amount-input @error('amount') is-invalid @enderror">
                        </div>
                        @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            @foreach([500, 1000, 2000, 5000, 10000] as $q)
                                <button type="button" class="quick-amt" data-quick="{{ $q }}">+ ৳{{ number_format($q) }}</button>
                            @endforeach
                            <button type="button" class="btn btn-link btn-sm text-muted text-decoration-none" id="clearAmt">মুছুন</button>
                        </div>
                        <div class="text-muted mt-2" style="font-size: .75rem;">কুইক বাটনে ক্লিক করলে বর্তমান পরিমাণের সাথে যোগ হবে।</div>
                    </div>

                    <!-- Payment method -->
                    <div class="glass-card p-4 mb-4">
                        <div class="section-title"><span class="ico"><i class="fa-solid fa-credit-card"></i></span> Payment Method</div>

                        <div class="row g-2">
                            @foreach($methods as $name => $m)
                                <div class="col-6 col-md-4">
                                    <label class="pay-option w-100 h-100">
                                        <input type="radio" name="payment_method" value="{{ $name }}" {{ $selectedMethod === $name ? 'checked' : '' }}>
                                        <div class="pay-card">
                                            <span class="pi" style="background: {{ $m['bg'] }}; color: {{ $m['fg'] }};"><i class="fa-solid {{ $m['icon'] }}"></i></span>
                                            <span class="pn">{{ $name }}</span>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div id="referenceWrap" class="mt-3 {{ $selectedMethod === 'Cash' ? 'd-none' : '' }}">
                            <label for="reference" class="form-label-custom">
                                <span id="referenceLabel">Transaction ID</span> <span class="text-muted text-lowercase fw-normal">(optional)</span>
                            </label>
                            <input type="text" name="reference" id="reference" maxlength="100" placeholder="e.g., 9H7K2L5M1Q"
                                   value="{{ old('reference') }}" class="form-control modern-input @error('reference') is-invalid @enderror">
                            @error('reference') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="glass-card p-4 mb-4">
                        <div class="section-title"><span class="ico"><i class="fa-solid fa-pen-to-square"></i></span> Description / Reference Note <span class="text-muted fw-normal small">(invoice-এ দেখাবে)</span></div>
                        <textarea name="description" rows="3" maxlength="255"
                                  placeholder="Example: Monthly system credit allocation"
                                  class="form-control modern-textarea @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" id="reviewBtn" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm" style="background-color: #4f46e5; border-color: #4f46e5;">
                            <i class="fa-solid fa-bolt me-1"></i> Review &amp; Recharge
                        </button>
                        <a href="{{ route('admin.wallets.index') }}" class="btn btn-light border px-4 py-2 text-secondary fw-medium">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- ============ SUMMARY ============ -->
            <div class="col-lg-5">

                <!-- Doctor card -->
                <div class="glass-card mb-4 overflow-hidden">
                    <div class="doc-banner"></div>
                    <div class="px-4 pb-4">
                        <div class="doc-avatar">{{ strtoupper(mb_substr($doctorName, 0, 2)) }}</div>
                        <h5 class="fw-bold text-dark mt-3 mb-0">Dr. {{ $doctorName }}</h5>
                        @if(!empty($doctor?->qualification))
                            <div class="text-muted small">{{ $doctor->qualification }}</div>
                        @endif

                        <div class="mt-3 small text-secondary d-grid gap-1">
                            @if(!empty($doctor?->doctor_code))
                                <div><i class="fa-solid fa-hashtag text-muted me-2"></i>Code: <span class="font-monospace fw-bold">{{ $doctor->doctor_code }}</span></div>
                            @endif
                            @if(!empty($doctor?->speciality))
                                <div><i class="fa-solid fa-stethoscope text-muted me-2"></i>{{ $doctor->speciality }}</div>
                            @endif
                            @if(!empty($doctor?->bmdc_number))
                                <div><i class="fa-solid fa-id-badge text-muted me-2"></i>BMDC: {{ $doctor->bmdc_number }}</div>
                            @endif
                            @if(!empty($doctor?->user?->email))
                                <div><i class="fa-solid fa-envelope text-muted me-2"></i>{{ $doctor->user->email }}</div>
                            @endif
                            @if(!empty($doctor?->user?->phone))
                                <div><i class="fa-solid fa-phone text-muted me-2"></i>{{ $doctor->user->phone }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Balance preview -->
                <div class="wallet-summary shadow-sm">
                    <div class="text-uppercase fw-bold" style="font-size: .68rem; letter-spacing: .12em; color:#94a3b8;">Wallet Summary</div>
                    <div class="mt-3">
                        <div class="row-line"><span class="lbl">Current Balance</span><span class="fw-semibold">৳{{ number_format($balance, 2) }}</span></div>
                        <div class="row-line"><span class="lbl">Recharge Amount</span><span class="fw-semibold" style="color:#34d399;" id="rechargeAmt">+ ৳0.00</span></div>
                        <div class="grand"><span class="fw-semibold" style="color:#cbd5e1;">New Balance</span><span class="v" id="newBalance">৳{{ number_format($balance, 2) }}</span></div>
                    </div>
                    <div class="mt-3" style="font-size: .72rem; color:#94a3b8;">
                        <i class="fa-solid fa-file-invoice me-1"></i> Recharge সম্পন্ন হলে স্বয়ংক্রিয়ভাবে একটি Invoice তৈরি হবে।
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============ CONFIRM MODAL ============ -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-body p-4 text-center">
                <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle" style="width:56px;height:56px;background:#eef2ff;color:#4f46e5;font-size:1.4rem;">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <h5 class="fw-bold mt-3 mb-1">Recharge নিশ্চিত করুন</h5>
                <p class="text-muted small mb-3">Confirm করলে balance যোগ হবে ও Invoice তৈরি হবে।</p>

                <div class="text-start border rounded-3 mb-3">
                    <div class="confirm-row"><span class="text-muted">Doctor</span><span class="fw-semibold">Dr. {{ $doctorName }}</span></div>
                    <div class="confirm-row"><span class="text-muted">Amount</span><span class="fw-bold text-success" id="mAmount">—</span></div>
                    <div class="confirm-row"><span class="text-muted">Method</span><span class="fw-semibold" id="mMethod">—</span></div>
                    <div class="confirm-row"><span class="text-muted">New Balance</span><span class="fw-bold" id="mNew">—</span></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light border flex-fill fw-semibold" data-bs-dismiss="modal">Back</button>
                    <button type="button" id="confirmBtn" class="btn btn-success flex-fill fw-bold">
                        <i class="fa-solid fa-check me-1"></i> <span id="confirmLabel">Confirm Recharge</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const BALANCE = @json($balance);
        const form = document.getElementById('rechargeForm');
        const amountInput = document.getElementById('amount');
        const rechargeAmt = document.getElementById('rechargeAmt');
        const newBalance = document.getElementById('newBalance');
        const referenceWrap = document.getElementById('referenceWrap');
        const referenceInput = document.getElementById('reference');
        const referenceLabel = document.getElementById('referenceLabel');
        const modalEl = document.getElementById('confirmModal');
        const modal = new bootstrap.Modal(modalEl);

        const money = (n) => '৳' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const currentAmount = () => parseFloat(amountInput.value) || 0;
        const currentMethod = () => (form.querySelector('input[name="payment_method"]:checked') || {}).value || 'Cash';

        function refreshSummary() {
            const amt = currentAmount();
            rechargeAmt.textContent = '+ ' + money(amt);
            newBalance.textContent = money(BALANCE + amt);
        }
        amountInput.addEventListener('input', refreshSummary);

        document.querySelectorAll('.quick-amt').forEach(function (btn) {
            btn.addEventListener('click', function () {
                amountInput.value = (currentAmount() + parseInt(btn.dataset.quick, 10)).toString();
                refreshSummary();
                amountInput.focus();
            });
        });
        document.getElementById('clearAmt').addEventListener('click', function () {
            amountInput.value = '';
            refreshSummary();
        });

        function refreshMethod() {
            const m = currentMethod();
            const isCash = m === 'Cash';
            referenceWrap.classList.toggle('d-none', isCash);
            if (isCash) referenceInput.value = '';
            referenceLabel.textContent = m === 'Bank Transfer' ? 'Bank Reference / Slip No' : 'Transaction ID';
        }
        form.querySelectorAll('input[name="payment_method"]').forEach(function (r) {
            r.addEventListener('change', refreshMethod);
        });

        function openReview() {
            const amt = currentAmount();
            if (amt < 1) {
                amountInput.focus();
                amountInput.classList.add('is-invalid');
                setTimeout(function () { amountInput.classList.remove('is-invalid'); }, 1500);
                return;
            }
            document.getElementById('mAmount').textContent = '+ ' + money(amt);
            document.getElementById('mMethod').textContent = currentMethod();
            document.getElementById('mNew').textContent = money(BALANCE + amt);
            modal.show();
        }

        document.getElementById('reviewBtn').addEventListener('click', openReview);
        // Enter chaple direct submit na hoye review modal khulbe
        form.addEventListener('submit', function (e) { e.preventDefault(); openReview(); });

        document.getElementById('confirmBtn').addEventListener('click', function () {
            this.disabled = true;
            document.getElementById('confirmLabel').textContent = 'Processing...';
            form.submit();   // form.submit() 'submit' event fire kore na, tai atkabe na
        });

        refreshSummary();
        refreshMethod();
    })();
</script>
@endpush