<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Services\SerialService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $doctor = auth()->user()->doctor;

        $stats = [
            'wallet_balance'  => optional($doctor?->wallet)->balance ?? 0,
            'total_chambers'  => $doctor?->chambers?->count() ?? 0,
            'active_chamber'  => $doctor?->activeChamber(),
        ];

        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

        $patientCounts = $doctor
            ? Prescription::where('doctor_id', $doctor->id)
                ->whereDate('created_at', '>=', now()->subDays(13)->toDateString())
                ->selectRaw('DATE(created_at) as day, COUNT(DISTINCT patient_id) as patient_count')
                ->groupBy('day')
                ->pluck('patient_count', 'day')
            : collect();

        $patientChart = [
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('d M'))->values(),
            'data' => $days->map(fn ($d) => (int) ($patientCounts[$d] ?? 0))->values(),
        ];

        // আজকের কিউ: চলছে আগে, তারপর অপেক্ষায় (সিরিয়াল নাম্বার অনুযায়ী) — Start সরাসরি ড্যাশবোর্ড থেকে
        $queueCounts = ['total' => 0, 'waiting' => 0, 'in_progress' => 0, 'done' => 0, 'skipped' => 0];
        $queueSerials = collect();

        if ($doctor) {
            $svc = app(SerialService::class);
            $all = $svc->todaysQueue($doctor);
            $queueCounts = $svc->counts($all);
            $queueSerials = $all
                ->whereIn('status', ['in_progress', 'waiting'])
                ->sortBy(fn ($s) => [$s->status === 'in_progress' ? 0 : 1, $s->serial_number])
                ->take(6)
                ->values();
        }

        return view('doctor.dashboard', compact('stats', 'patientChart', 'queueCounts', 'queueSerials'));
    }
}