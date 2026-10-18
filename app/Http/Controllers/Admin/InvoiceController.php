<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletInvoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = WalletInvoice::with('doctor.user')
            ->when($request->search, function ($q, $term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('invoice_no', 'like', "%{$term}%")
                       ->orWhere('reference', 'like', "%{$term}%")
                       ->orWhereHas('doctor.user', fn ($u) => $u->where('name', 'like', "%{$term}%"));
                });
            })
            ->latest('issued_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show(WalletInvoice $invoice)
    {
        $invoice->load(['doctor.user', 'issuer']);

        return view('admin.invoices.show', compact('invoice'));
    }
}