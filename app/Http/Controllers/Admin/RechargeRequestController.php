<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RechargeRequest;
use App\Services\RechargeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RechargeRequestController extends Controller
{
    public function __construct(protected RechargeRequestService $recharges)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $status = in_array($status, ['pending', 'approved', 'rejected'], true) ? $status : null;

        $requests = RechargeRequest::with(['doctor.user', 'invoice', 'reviewer'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($request->query('search'), function ($q, $term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('trx_id', 'like', '%' . strtoupper($term) . '%')
                       ->orWhere('sender_number', 'like', "%{$term}%")
                       ->orWhereHas('doctor.user', fn ($u) => $u->where('name', 'like', "%{$term}%"));
                });
            })
            // pending first, then newest
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all'      => RechargeRequest::count(),
            'pending'  => RechargeRequest::where('status', 'pending')->count(),
            'approved' => RechargeRequest::where('status', 'approved')->count(),
            'rejected' => RechargeRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.recharge-requests.index', compact('requests', 'counts', 'status'));
    }

    public function approve(RechargeRequest $rechargeRequest): RedirectResponse
    {
        try {
            $invoice = $this->recharges->approve($rechargeRequest, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            'Approved. ৳' . number_format((float) $rechargeRequest->amount, 2) . ' added to the wallet and invoice ' . $invoice->invoice_no . ' issued.'
        );
    }

    public function reject(Request $request, RechargeRequest $rechargeRequest): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['required', 'string', 'min:3', 'max:300'],
        ], [
            'admin_note.required' => 'Write a short reason. The doctor will see it.',
        ]);

        try {
            $this->recharges->reject($rechargeRequest, auth()->user(), $data['admin_note']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Request rejected and the doctor was notified.');
    }
}
