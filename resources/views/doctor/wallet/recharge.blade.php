@extends('layouts.doctor')

@section('title', 'Recharge Wallet')

@push('styles')
    @include('doctor.wallet._styles')
    <style>
        .wl-pay { background:linear-gradient(135deg,#fdf2f8,#fff); border:1px solid #fbcfe8; border-radius:16px; padding:1rem 1.15rem; }
        .wl-pay .num { font-size:1.65rem; font-weight:800; letter-spacing:.04em; color:var(--wl-bkash); line-height:1.2; }
        .wl-steps { list-style:none; counter-reset:s; margin:1rem 0 0; padding:0; display:grid; gap:.7rem; }
        .wl-steps li { counter-increment:s; display:grid; grid-template-columns:28px 1fr; gap:.7rem; font-size:.88rem; color:#334155; align-items:start; }
        .wl-steps li::before { content:counter(s); width:28px; height:28px; border-radius:50%; background:#e0e7ff; color:#3730a3; font-weight:800; font-size:.8rem; display:grid; place-items:center; }
        .wl-note { background:#fff8e6; border:1px solid #f1dca0; color:#6b4e00; border-radius:12px; padding:.7rem .9rem; font-size:.82rem; margin-top:1rem; }
    </style>
@endpush

@section('content')
<div class="wl-hero">
    <h1><i class="fa-solid fa-wallet me-2"></i>Recharge Wallet</h1>
    <p>Send money by bKash, then submit the amount, your bKash number and the TrxID. After we verify the payment, your balance is added and you receive a paid invoice.</p>
</div>

<div class="row g-4 mb-4">
    {{-- Balance + how to pay --}}
    <div class="col-lg-5">
        <div class="wl-card">
            <div class="wl-label">Current balance</div>
            <div class="wl-balance mb-3">৳{{ number_format((float) $wallet->balance, 2) }}</div>

            <h2><i class="fa-solid fa-mobile-screen-button"></i> How to pay</h2>

            <div class="wl-pay">
                <div class="wl-label" style="color:#9d174d">bKash {{ $bkash['account_type'] ?? 'Personal' }} number</div>
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <span class="num" id="bkashNumber">{{ $bkash['number'] }}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="copyBkash"><i class="fa-regular fa-copy me-1"></i> Copy</button>
                </div>
            </div>

            <ol class="wl-steps">
                <li>bKash app খুলে <strong>Send Money</strong> বেছে নিন।</li>
                <li>উপরের নম্বর <strong>{{ $bkash['number'] }}</strong> এ টাকা পাঠান (সর্বনিম্ন ৳{{ number_format($minAmount) }})।</li>
                <li>টাকা পাঠানো শেষে SMS বা bKash app থেকে <strong>TrxID</strong> কপি করুন।</li>
                <li>ডানের ফর্মে <strong>Amount</strong>, যে নম্বর থেকে পাঠিয়েছেন সেই <strong>bKash নম্বর</strong> এবং <strong>TrxID</strong> দিয়ে Submit করুন।</li>
                <li>যাচাই শেষে ব্যালেন্স যোগ হবে, আপনি Notification ও Paid Invoice পাবেন।</li>
            </ol>

            <div class="wl-note"><i class="fa-solid fa-triangle-exclamation me-1"></i> ভুল TrxID বা নম্বর দিলে আবেদন বাতিল হতে পারে। একটি TrxID শুধু একবারই ব্যবহার করা যায়।</div>
        </div>
    </div>

    {{-- Submit form --}}
    <div class="col-lg-7">
        <div class="wl-card">
            <h2><i class="fa-solid fa-paper-plane"></i> Submit your payment</h2>

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
                            <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="{{ $minAmount }}" step="1" inputmode="numeric"
                                   class="form-control @error('amount') is-invalid @enderror" placeholder="{{ $minAmount }}" required>
                        </div>
                        <div class="form-text">Minimum ৳{{ number_format($minAmount) }}. The same amount you sent.</div>
                        @error('amount') <div class="wl-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="wl-label-f" for="sender_number">Your bKash number <span class="text-danger">*</span></label>
                        <input type="tel" id="sender_number" name="sender_number" value="{{ old('sender_number') }}" inputmode="numeric" maxlength="14"
                               class="form-control @error('sender_number') is-invalid @enderror" placeholder="01XXXXXXXXX" required>
                        <div class="form-text">The number you paid from.</div>
                        @error('sender_number') <div class="wl-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="wl-label-f" for="trx_id">bKash TrxID <span class="text-danger">*</span></label>
                        <input type="text" id="trx_id" name="trx_id" value="{{ old('trx_id') }}" maxlength="14" autocomplete="off" style="text-transform:uppercase"
                               class="form-control @error('trx_id') is-invalid @enderror" placeholder="e.g. 9H7XJ2K1LM" required>
                        <div class="form-text">Copy it from your payment SMS or the bKash app.</div>
                        @error('trx_id') <div class="wl-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="wl-label-f" for="doctor_note">Note (optional)</label>
                        <input type="text" id="doctor_note" name="doctor_note" value="{{ old('doctor_note') }}" maxlength="300" class="form-control" placeholder="Anything we should know">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold mt-4" @disabled($pendingCount >= $maxPending)>
                    <i class="fa-solid fa-paper-plane me-1"></i> Submit for approval
                </button>
            </form>
        </div>
    </div>
</div>

{{-- My requests --}}
<div class="wl-card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
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
</div>
@endsection

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('copyBkash');
    if (btn) btn.addEventListener('click', function () {
        var text = document.getElementById('bkashNumber').textContent.trim(), original = btn.innerHTML;
        function done() { btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Copied'; setTimeout(function () { btn.innerHTML = original; }, 1800); }
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(text).then(done); }
        else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); done(); } catch (e) {} document.body.removeChild(t); }
    });
})();
</script>
@endpush
