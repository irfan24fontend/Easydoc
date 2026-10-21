@extends('layouts.doctor')

@section('title', 'My Invoices')

@push('styles')
    @include('doctor.wallet._styles')
@endpush

@section('content')
<div class="wl-hero">
    <h1><i class="fa-solid fa-file-invoice me-2"></i>My Invoices</h1>
    <p>A paid invoice is issued every time a recharge is approved. Open one to print it or save it as a PDF.</p>
</div>

<div class="wl-card">
    @if($invoices->isEmpty())
        <div class="wl-empty"><i class="fa-solid fa-file-invoice"></i>No invoice yet. It appears here after your first recharge is approved.</div>
    @else
        <div class="table-responsive">
            <table class="table wl-table align-middle mb-0">
                <thead><tr><th>Invoice</th><th>Date</th><th>Method</th><th>TrxID</th><th class="text-end">Amount</th><th class="text-end">Download</th></tr></thead>
                <tbody>
                @foreach($invoices as $inv)
                    <tr>
                        <td class="fw-bold">{{ $inv->invoice_no }}</td>
                        <td class="text-nowrap">{{ $inv->issued_at->format('d M Y') }}</td>
                        <td>{{ $inv->payment_method ?: '—' }}</td>
                        <td><code>{{ $inv->reference ?: '—' }}</code></td>
                        <td class="text-end fw-bold">৳{{ number_format((float) $inv->amount, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('doctor.wallet.invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-download me-1"></i> Open</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $invoices->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
