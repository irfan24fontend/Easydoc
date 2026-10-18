<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Chamber;
use App\Models\PrescriptionTemplate;
use App\Models\Wallet;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_doctors'    => Doctor::count(),
            'active_chambers'  => Chamber::where('status', true)->count(),
            'total_templates'  => PrescriptionTemplate::count(),
            'wallet_balance'   => Wallet::sum('balance'),
        ];

        $recentDoctors = Doctor::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentDoctors'));
    }
}