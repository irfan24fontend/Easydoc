<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chamber;
use App\Models\PrescriptionTemplate;
use App\Services\ChamberRequestService;
use Illuminate\Http\Request;
use Throwable;

class ChamberRequestController extends Controller
{
    public function __construct(private ChamberRequestService $service)
    {
    }

    public function index(Request $request)
    {
        $filter = $request->query('filter', 'waiting_request');

        $query = Chamber::with('doctor')->orderByDesc('id');

        match ($filter) {
            'waiting_request' => $query->where('request_status', Chamber::REQUEST_WAITING),
            'active'          => $query->where('request_status', Chamber::REQUEST_APPROVED)->where('status', true),
            'inactive'        => $query->where('request_status', Chamber::REQUEST_APPROVED)->where('status', false),
            'rejected'        => $query->where('request_status', Chamber::REQUEST_REJECTED),
            default           => null,
        };

        $counts = [
            'waiting_request' => Chamber::where('request_status', Chamber::REQUEST_WAITING)->count(),
            'active'          => Chamber::where('request_status', Chamber::REQUEST_APPROVED)->where('status', true)->count(),
            'inactive'        => Chamber::where('request_status', Chamber::REQUEST_APPROVED)->where('status', false)->count(),
            'rejected'        => Chamber::where('request_status', Chamber::REQUEST_REJECTED)->count(),
            'all'             => Chamber::count(),
        ];

        return view('admin.chamber-requests.index', [
            'chambers' => $query->paginate(20)->withQueryString(),
            'filter'   => $filter,
            'counts'   => $counts,
        ]);
    }

    public function show(Chamber $chamber)
    {
        $chamber->load(['doctor', 'template']);

        return view('admin.chamber-requests.show', [
            'chamber'   => $chamber,
            'templates' => PrescriptionTemplate::where('status', true)->orderBy('name')->get(),
            'fee'       => ChamberRequestService::FEE,
        ]);
    }

    public function approve(Request $request, Chamber $chamber)
    {
        $data = $request->validate([
            'prescription_template_id' => ['required', 'integer', 'exists:prescription_templates,id'],
        ]);

        try {
            $this->service->approve($chamber, (int) $data['prescription_template_id'], $request->user()->id);
        } catch (Throwable $e) {
            return back()->withErrors(['approve' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.chamber-requests.index')
            ->with('success', 'চেম্বার Active করা হয়েছে।');
    }

    public function reject(Request $request, Chamber $chamber)
    {
        $data = $request->validate([
            'reject_reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->service->reject($chamber, $data['reject_reason'], $request->user()->id);
        } catch (Throwable $e) {
            return back()->withErrors(['reject' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.chamber-requests.index')
            ->with('success', 'রিকোয়েস্ট বাতিল করা হয়েছে এবং ফি ফেরত দেওয়া হয়েছে।');
    }

    public function toggle(Chamber $chamber)
    {
        try {
            $this->service->toggleStatus($chamber);
        } catch (Throwable $e) {
            return back()->withErrors(['toggle' => $e->getMessage()]);
        }

        return back()->with('success', 'স্ট্যাটাস আপডেট হয়েছে।');
    }
}