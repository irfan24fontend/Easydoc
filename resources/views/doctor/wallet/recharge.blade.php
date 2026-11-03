@extends('layouts.doctor')

@section('title', 'Recharge Wallet')

@push('styles')
    @include('doctor.wallet._styles')
    <style>
        .wl-hero { position:relative;overflow:hidden;display:flex;justify-content:space-between;align-items:center;gap:1.5rem;border-radius:22px;padding:1.5rem 1.8rem;margin-bottom:1.25rem;color:#fff;background:linear-gradient(115deg,#a70f53,#e2136e 58%,#f04491);box-shadow:0 14px 34px rgba(226,19,110,.2); }
        .wl-hero::after { content:"";position:absolute;right:22%;top:-130px;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,.055); }
        .wl-hero-copy,.wl-hero-balance { position:relative;z-index:1; }
        .wl-hero-kicker { display:inline-flex;align-items:center;gap:.45rem;margin-bottom:.55rem;padding:.35rem .7rem;border:1px solid rgba(255,255,255,.25);border-radius:999px;background:rgba(255,255,255,.12);font-size:.68rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase; }
        .wl-hero h1 { margin:0;font-size:clamp(1.35rem,2vw,1.8rem);font-weight:800; }
        .wl-hero p { position:relative;z-index:1;max-width:640px;margin:.35rem 0 0;font-size:.86rem;opacity:.9; }
        .wl-hero-balance { min-width:190px;padding:1rem 1.2rem;border:1px solid rgba(255,255,255,.24);border-radius:16px;background:rgba(255,255,255,.12);backdrop-filter:blur(8px); }
        .wl-hero-balance span { display:block;font-size:.7rem;font-weight:700;opacity:.8;text-transform:uppercase;letter-spacing:.07em; }
        .wl-hero-balance strong { display:block;margin-top:.25rem;font-size:1.65rem;line-height:1.1; }
        .wl-gateway-grid { align-items:stretch; }
        .wl-gateway-card { height:100%;padding:1.35rem;border:1px solid #e7e9ee;border-radius:18px;background:#fff;box-shadow:0 5px 20px rgba(15,23,42,.045); }
        .wl-gateway-head { display:flex;align-items:flex-start;gap:.8rem;margin-bottom:1.1rem; }
        .wl-gateway-icon { display:grid;place-items:center;flex:none;width:42px;height:42px;border-radius:13px;background:#fce7f1;color:#c30f60;font-size:1rem; }
        .wl-gateway-head h2 { margin:0;color:#172b32;font-size:1rem;font-weight:800; }
        .wl-gateway-head p { margin:.2rem 0 0;color:#78868a;font-size:.78rem; }
        .wl-pay { padding:1.1rem 1.15rem;border:1px solid #f2bfd5;border-radius:15px;background:linear-gradient(135deg,#fff7fa,#fff); }
        .wl-pay-top { display:flex;justify-content:space-between;align-items:center;gap:.5rem;margin-bottom:.8rem; }
        .wl-brand-bkash { color:#d40f67;font-size:1.12rem;font-weight:900;letter-spacing:-.04em; }
        .wl-account-type { padding:.26rem .55rem;border-radius:999px;background:#fce7f1;color:#a20f51;font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em; }
        .wl-pay .num { color:#a70f53;font-size:clamp(1.35rem,2.1vw,1.75rem);font-weight:850;letter-spacing:.045em;line-height:1.2;font-variant-numeric:tabular-nums; }
        .wl-copy { flex:none;border:1px solid #e9c4d4;border-radius:10px;background:#fff;color:#a70f53;font-weight:750; }
        .wl-copy:hover { background:#fce7f1;color:#8e0d48; }
        .wl-recipient-caption { display:block;margin-top:.45rem;color:#79888c;font-size:.72rem; }
        .wl-steps { display:grid;gap:.75rem;list-style:none;counter-reset:step;margin:1.05rem 0 0;padding:0; }
        .wl-steps li { display:grid;grid-template-columns:30px 1fr;gap:.7rem;align-items:start;color:#53666a;font-size:.82rem;line-height:1.55;counter-increment:step; }
        .wl-steps li::before { content:counter(step);display:grid;place-items:center;width:30px;height:30px;border-radius:10px;background:#fce7f1;color:#b20e58;font-size:.78rem;font-weight:850; }
        .wl-steps strong { color:#253c41; }
        .wl-note { margin-top:1rem;padding:.75rem .85rem;border:1px solid #f4dfaa;border-radius:11px;background:#fffaf0;color:#775a16;font-size:.76rem;line-height:1.5; }
        .wl-form-card { border-color:#e8e9ed; }
        .wl-form-title { display:flex;justify-content:space-between;align-items:center;gap:1rem;padding-bottom:1rem;margin-bottom:1.1rem;border-bottom:1px solid #edf0f2; }
        .wl-form-title .wl-gateway-head { margin:0; }
        .wl-secure { display:inline-flex;align-items:center;gap:.35rem;color:#647c78;font-size:.7rem;font-weight:700;white-space:nowrap; }
        .wl-label-f { display:block;margin-bottom:.3rem;color:#3a5055;font-size:.76rem;font-weight:700; }
        .wl-form-card .form-control,.wl-form-card .input-group-text { min-height:46px;border-color:#dce4e5;border-radius:10px;background:#fbfcfc; }
        .wl-form-card .input-group-text { color:#a70f53;background:#fff7fa;font-weight:800; }
        .wl-form-card .form-control:focus { border-color:#df6b9c;background:#fff;box-shadow:0 0 0 .2rem rgba(226,19,110,.1); }
        .wl-form-card .form-text { color:#88979a;font-size:.7rem; }
        .wl-amount-options { display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.6rem; }
        .wl-amount-option { padding:.32rem .65rem;border:1px solid #e4e9e9;border-radius:8px;background:#fff;color:#52666a;font-size:.72rem;font-weight:750;transition:background .15s,border-color .15s,color .15s; }
        .wl-amount-option:hover,.wl-amount-option.active { border-color:#e79abb;background:#fff3f8;color:#a70f53; }
        .wl-submit { width:100%;min-height:49px;margin-top:1.15rem;border:0;border-radius:11px;background:linear-gradient(100deg,#bd105b,#e2136e);box-shadow:0 8px 18px rgba(226,19,110,.2);font-weight:800; }
        .wl-submit:hover { background:linear-gradient(100deg,#a70f53,#cf1264);box-shadow:0 10px 22px rgba(226,19,110,.26); }
        .wl-submit:disabled { box-shadow:none; }
        .wl-form-foot { margin-top:.7rem;color:#879397;font-size:.69rem;line-height:1.45;text-align:center; }
        .wl-history { padding:1.35rem; }
        .wl-history-head { display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:.8rem; }
        .wl-history .table-responsive { border:1px solid #edf0f2;border-radius:12px; }
        .wl-history .wl-table { margin:0; }
        .wl-err { font-size:.75rem; }
        @media(max-width:767.98px) {
            .wl-hero { align-items:stretch;flex-direction:column;padding:1.25rem; }
            .wl-hero-balance { min-width:0;padding:.75rem 1rem; }
            .wl-gateway-card,.wl-history { padding:1rem; }
            .wl-form-title { align-items:flex-start; }
            .wl-secure { font-size:.65rem; }
        }
    </style>
@endpush

@section('content')
<header class="wl-hero">
    <div class="wl-hero-copy">
        <span class="wl-hero-kicker"><i class="fa-solid fa-shield-halved"></i> Manual bKash payment</span>
        <h1><i class="fa-solid fa-wallet me-2"></i>Recharge your wallet</h1>
        <p>Send money to EasyDoc’s bKash account, then enter your payment details below. Your balance is added after verification.</p>
    </div>
    <div class="wl-hero-balance"><span>Available balance</span><strong>৳{{ number_format((float) $wallet->balance, 2) }}</strong></div>
</header>

<div class="row g-4 mb-4 wl-gateway-grid">
    <div class="col-lg-5">
        <section class="wl-gateway-card">
            <div class="wl-gateway-head">
                <span class="wl-gateway-icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
                <div><h2>Send money with bKash</h2><p>Complete these steps before submitting.</p></div>
            </div>

            <div class="wl-pay">
                <div class="wl-pay-top">
                    <span class="wl-brand-bkash">bKash</span>
                    <span class="wl-account-type">{{ $bkash['account_type'] ?? 'Personal' }} account</span>
                </div>
                <div class="wl-label mb-1">Send money to this number</div>
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <span class="num" id="bkashNumber">{{ $bkash['number'] }}</span>
                    <button type="button" class="btn btn-sm wl-copy" id="copyBkash"><i class="fa-regular fa-copy me-1"></i> Copy number</button>
                </div>
                <span class="wl-recipient-caption">EasyDoc wallet · Minimum ৳{{ number_format($minAmount) }}</span>
            </div>

            <ol class="wl-steps">
                <li>Open your bKash app and choose <strong>Send Money</strong>.</li>
                <li>Send ৳{{ number_format($minAmount) }} or more to the EasyDoc number above.</li>
                <li>After payment, copy the <strong>TrxID</strong> from the confirmation screen or SMS.</li>
                <li>Enter the exact amount, sender number and TrxID in the form.</li>
            </ol>

            <div class="wl-note"><i class="fa-solid fa-circle-info me-1"></i> Your payment is added after an admin verifies it. Submit each TrxID only once.</div>
        </section>
    </div>

    <div class="col-lg-7">
        <section class="wl-gateway-card wl-form-card">
            <div class="wl-form-title">
                <div class="wl-gateway-head">
                    <span class="wl-gateway-icon"><i class="fa-solid fa-receipt"></i></span>
                    <div><h2>Payment details</h2><p>Use the details from your bKash confirmation.</p></div>
                </div>
                <span class="wl-secure"><i class="fa-solid fa-lock"></i> Secure submission</span>
            </div>

            @if($pendingCount >= $maxPending)
                <div class="alert alert-warning small">You already have {{ $pendingCount }} requests waiting for approval. Please wait until they are reviewed before sending a new one.</div>
            @endif

            <form method="POST" action="{{ route('doctor.wallet.recharge.store') }}" novalidate>
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="wl-label-f" for="amount">Amount (BDT) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="{{ $minAmount }}" step="1" inputmode="numeric" class="form-control @error('amount') is-invalid @enderror" placeholder="{{ $minAmount }}" required>
                        </div>
                        <div class="form-text">Minimum ৳{{ number_format($minAmount) }}. Enter the amount you sent.</div>
                        <div class="wl-amount-options" aria-label="Choose a recharge amount">
                            @foreach([500, 1000, 2000, 5000] as $quickAmount)
                                @if($quickAmount >= $minAmount)
                                    <button type="button" class="wl-amount-option" data-amount="{{ $quickAmount }}">৳{{ number_format($quickAmount) }}</button>
                                @endif
                            @endforeach
                        </div>
                        @error('amount') <div class="wl-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="wl-label-f" for="sender_number">Paid from this number <span class="text-danger">*</span></label>
                        <input type="tel" id="sender_number" name="sender_number" value="{{ old('sender_number') }}" inputmode="numeric" maxlength="14" class="form-control @error('sender_number') is-invalid @enderror" placeholder="01XXXXXXXXX" required>
                        <div class="form-text">The bKash number you sent money from.</div>
                        @error('sender_number') <div class="wl-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="wl-label-f" for="trx_id">bKash transaction ID (TrxID) <span class="text-danger">*</span></label>
                        <input type="text" id="trx_id" name="trx_id" value="{{ old('trx_id') }}" maxlength="14" autocomplete="off" style="text-transform:uppercase" class="form-control @error('trx_id') is-invalid @enderror" placeholder="e.g. 9H7XJ2K1LM" required>
                        <div class="form-text">Enter the TrxID exactly as shown in the payment confirmation.</div>
                        @error('trx_id') <div class="wl-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="wl-label-f" for="doctor_note">Note <span class="text-muted fw-normal">(optional)</span></label>
                        <input type="text" id="doctor_note" name="doctor_note" value="{{ old('doctor_note') }}" maxlength="300" class="form-control" placeholder="Anything we should know">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary wl-submit" @disabled($pendingCount >= $maxPending)>
                    <i class="fa-solid fa-lock me-2"></i> Submit payment for verification
                </button>
                <div class="wl-form-foot">Your wallet is credited only after the payment has been checked.</div>
            </form>
        </section>
    </div>
</div>

<section class="wl-card wl-history">
    <div class="wl-history-head">
        <h2 class="mb-0"><i class="fa-solid fa-clock-rotate-left"></i> My recharge requests</h2>
        <a href="{{ route('doctor.wallet.invoices') }}" class="btn btn-sm btn-light border"><i class="fa-solid fa-file-invoice me-1"></i> All invoices</a>
    </div>

    @if($requests->isEmpty())
        <div class="wl-empty"><i class="fa-solid fa-inbox"></i>No request yet.</div>
    @else
        <div class="table-responsive">
            <table class="table wl-table align-middle mb-0">
                <thead><tr><th>Date</th><th>Amount</th><th>TrxID</th><th>bKash number</th><th>Status</th><th class="text-end">Details</th></tr></thead>
                <tbody>
                @foreach($requests as $r)
                    <tr>
                        <td class="text-nowrap">{{ $r->created_at->format('d M Y, h:i A') }}</td>
                        <td class="fw-bold">৳{{ number_format((float) $r->amount, 2) }}</td>
                        <td><code>{{ $r->trx_id }}</code></td>
                        <td>{{ $r->sender_number }}</td>
                        <td><span class="wl-badge {{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                        <td class="text-end">
                            @if($r->isApproved() && $r->invoice)
                                <a href="{{ route('doctor.wallet.invoices.show', $r->invoice->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-download me-1"></i> Invoice</a>
                            @elseif($r->isRejected())
                                <span class="small text-danger">{{ $r->admin_note }}</span>
                            @else
                                <span class="small text-muted">Waiting for approval</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection

@push('scripts')
<script>
(function () {
    var copyButton = document.getElementById('copyBkash');
    var amountInput = document.getElementById('amount');

    if (copyButton) copyButton.addEventListener('click', function () {
        var number = document.getElementById('bkashNumber').textContent.trim();
        var original = copyButton.innerHTML;
        function copied() {
            copyButton.innerHTML = '<i class="fa-solid fa-check me-1"></i> Copied';
            setTimeout(function () { copyButton.innerHTML = original; }, 1800);
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(number).then(copied).catch(function () {});
        } else {
            var field = document.createElement('textarea');
            field.value = number;
            document.body.appendChild(field);
            field.select();
            try { document.execCommand('copy'); copied(); } catch (e) {}
            document.body.removeChild(field);
        }
    });

    document.querySelectorAll('[data-amount]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!amountInput) return;
            amountInput.value = button.getAttribute('data-amount');
            document.querySelectorAll('.wl-amount-option').forEach(function (option) { option.classList.remove('active'); });
            button.classList.add('active');
            amountInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    if (amountInput) amountInput.addEventListener('input', function () {
        document.querySelectorAll('.wl-amount-option').forEach(function (button) {
            button.classList.toggle('active', Number(button.getAttribute('data-amount')) === Number(amountInput.value));
        });
    });
})();
</script>
@endpush
