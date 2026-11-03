<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class ApprovalStatusController extends Controller
{
    public function show()
    {
        $doctor = auth()->user()->doctor;

        if ($doctor && $doctor->isApproved()) {
            return redirect()->route('doctor.dashboard');
        }

        return view('doctor.approval-status', compact('doctor'));
    }
}
