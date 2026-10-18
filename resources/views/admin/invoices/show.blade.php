@extends('layouts.admin')

@section('title', 'Invoice ' . $invoice->invoice_no)

@push('styles')
<style>
    .inv-wrap { --ink:#0f172a; --muted:#64748b; --line:#e2e8f0; --brand:#4f46e5; color: var(--ink); }
    .inv-sheet {
        width: 210mm; max-width: 100%; min-height: 297mm; margin: 0 auto; background: #fff;
        padding: 16mm 16mm 12mm; border-radius: 8px; box-shadow: 0 4px 24px rgba(15,23,42,.10);
        font-size: 14px;
    }
    .inv-head { display: flex; justify-content: space-between; gap: 20px; border-bottom: 3px solid var(--brand); padding-bottom: 14px; flex-wrap: wrap; }
    .inv-brand { display: flex; gap: 12px; align-items: center; }
    .inv-logo { width: 46px; height: 46px; border-radius: 10px; background: var(--brand); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 18px; }
    .inv-brand h1 { margin: 0; font-size: 20px; font-weight: 800; }
    .inv-brand p { margin: 2px 0 0; color: var(--muted); font-size: 12px; }
    .inv-title { text-align: right; }
    .inv-title h2 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: .08em; color: var(--brand); }
    .inv-title .no { font-weight: 700; margin-top: 4px; }
    .inv-badge { display: inline-block; margin-top: 6px; padding: 3px 10px; border-radius: 999px; background: #dcfce7; color: #166534; font-size: 11px; font-weight: 700; letter-spacing: .05em; }

    .inv-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin: 22px 0; }
    .inv-box h4 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); font-weight: 700; }
    .inv-box p { margin: 2px 0; }
    .inv-box .strong { font-weight: 700; font-size: 15px; }

    .inv-items { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .inv-items th { text-align: left; background: #f1f5f9; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); padding: 10px 12px; }
    .inv-items td { padding: 14px 12px; border-bottom: 1px solid var(--line); vertical-align: top; }
    .inv-items .r { text-align: right; }
    .inv-items small { display: block; color: var(--muted); margin-top: 3px; }

    .inv-totals { margin: 18px 0 0 auto; width: 300px; max-width: 100%; }
    .inv-totals .row-line { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px dashed var(--line); }
    .inv-totals .grand { border-bottom: 0; border-top: 2px solid var(--ink); margin-top: 6px; padding-top: 10px; font-size: 16px; font-weight: 800; }

    .inv-note { margin-top: 22px; padding: 12px 14px; background: #f8fafc; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; }
    .inv-sign { display: flex; justify-content: space-between; margin-top: 70px; }
    .inv-sign div { width: 200px; text-align: center; border-top: 1px solid var(--ink); padding-top: 6px; font-size: 12px; color: var(--muted); }
    .inv-foot { margin-top: 60px; text-align: center; font-size: 11px; color: var(--muted); border-top: 1px solid var(--line); padding-top: 8px; }

    @page { size: A4; margin: 0; }
    @media print {
        .sidebar-container, .topbar, .alert, .d-print-none { display: none !important; }
        .main-content { margin-left: 0 !important; }
        .main-content > .container-fluid { padding: 0 !important; }
        body { background: #fff !important; }
        .inv-sheet { width: 100%; min-height: 100vh; margin: 0; box-shadow: none; border-radius: 0; }
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@endpush

@section('content')
@php
    $doctor  = $invoice->doctor;
    $company = [
        'name'    => config('easydoc.company.name', 'EasyDoc'),
        'tagline' => config('easydoc.company.tagline', 'Doctor & Prescription Management'),
        'address' => config('easydoc.company.address', ''),
        'phone'   => config('easydoc.company.phone', ''),
        'email'   => config('easydoc.company.email', ''),
    ];
    $money = fn ($v) => '৳' . number_format((float) $v, 2);
@endphp

<div class="d-print-none d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-bold text-dark m-0">Invoice {{ $invoice->invoice_no }}</h1>
        <p class="text-muted small m-0">Print করুন অথবা "Save as PDF" বেছে PDF হিসেবে সেভ করুন।</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-light border px-4 fw-medium text-secondary">All Invoices</a>
        <button type="button" onclick="window.print()" class="btn btn-primary px-4 fw-semibold" style="background-color: #4f46e5; border-color: #4f46e5;">
            <i class="fa-solid fa-print me-1"></i> Print / Save as PDF
        </button>
    </div>
</div>

<div class="inv-wrap" style="overflow-x:auto;">
    <div class="inv-sheet">

        <div class="inv-head">
            <div class="inv-brand">
                <div class="inv-logo">ED</div>
                <div>
                    <h1>{{ $company['name'] }}</h1>
                    <p>{{ $company['tagline'] }}</p>
                    @if($company['address'])<p>{{ $company['address'] }}</p>@endif
                    @if($company['phone'] || $company['email'])
                        <p>{{ $company['phone'] }}{{ $company['phone'] && $company['email'] ? ' · ' : '' }}{{ $company['email'] }}</p>
                    @endif
                </div>
            </div>
            <div class="inv-title">
                <h2>INVOICE</h2>
                <div class="no">{{ $invoice->invoice_no }}</div>
                <div class="inv-badge">WALLET CREDITED</div>
            </div>
        </div>

        <div class="inv-meta">
            <div class="inv-box">
                <h4>Billed To</h4>
                <p class="strong">Dr. {{ $doctor->user->name ?? '—' }}</p>
                @if(!empty($doctor->qualification))<p>{{ $doctor->qualification }}</p>@endif
                @if(!empty($doctor->doctor_code))<p>Code: {{ $doctor->doctor_code }}</p>@endif
                @if(!empty($doctor->bmdc_number))<p>BMDC: {{ $doctor->bmdc_number }}</p>@endif
                @if(!empty($doctor->user->email))<p>{{ $doctor->user->email }}</p>@endif
                @if(!empty($doctor->user->phone))<p>{{ $doctor->user->phone }}</p>@endif
            </div>
            <div class="inv-box" style="text-align:right;">
                <h4>Invoice Details</h4>
                <p><b>Date:</b> {{ $invoice->issued_at->format('d M Y, h:i A') }}</p>
                <p><b>Payment Method:</b> {{ $invoice->payment_method ?: '—' }}</p>
                @if($invoice->reference)<p><b>Reference:</b> {{ $invoice->reference }}</p>@endif
                <p><b>Issued By:</b> {{ $invoice->issuer->name ?? 'Super Admin' }}</p>
            </div>
        </div>

        <table class="inv-items">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Description</th>
                    <th class="r" style="width:150px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        Wallet Recharge
                        <small>Prescription wallet-e balance jog kora hoyeche</small>
                    </td>
                    <td class="r">{{ $money($invoice->amount) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="inv-totals">
            <div class="row-line"><span>Previous Balance</span><span>{{ $money($invoice->balance_before) }}</span></div>
            <div class="row-line"><span>Recharge Amount</span><span>+ {{ $money($invoice->amount) }}</span></div>
            <div class="row-line grand"><span>New Balance</span><span>{{ $money($invoice->balance_after) }}</span></div>
        </div>

        @if($invoice->note)
            <div class="inv-note"><b>Note:</b> {{ $invoice->note }}</div>
        @endif

        <div class="inv-sign">
            <div>Received By (Doctor)</div>
            <div>Authorized Signature</div>
        </div>

        <div class="inv-foot">
            This is a computer-generated invoice. · {{ $company['name'] }} · Invoice {{ $invoice->invoice_no }}
        </div>
    </div>
</div>
@endsection