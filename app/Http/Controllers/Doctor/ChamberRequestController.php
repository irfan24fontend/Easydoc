<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\StoreChamberRequestRequest;
use App\Services\ChamberRequestService;
use Illuminate\Http\Request;
use Throwable;

class ChamberRequestController extends Controller
{
    public function __construct(private ChamberRequestService $service)
    {
    }

    public function create(Request $request)
    {
        $doctor = $request->user()->doctor;
        $wallet = $this->service->walletFor($doctor);
        $fee    = ChamberRequestService::FEE;

        return view('doctor.chambers.request', [
            'balance'    => $wallet->balance,
            'fee'        => $fee,
            'hasBalance' => $wallet->balance >= $fee,
        ]);
    }

    public function store(StoreChamberRequestRequest $request)
    {
        $doctor = $request->user()->doctor;

        try {
            $this->service->submit(
                $doctor,
                $request->validated(),
                $request->file('pad_image')
            );
        } catch (Throwable $e) {
            return back()->withInput()->withErrors(['balance' => $e->getMessage()]);
        }

        return redirect()
            ->route('doctor.chambers.index')
            ->with('success', 'চেম্বার রিকোয়েস্ট পাঠানো হয়েছে। সুপার এডমিন রিভিউ করার পর চেম্বার চালু হবে।');
    }
}