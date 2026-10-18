<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Serial;
use App\Services\SerialService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function __construct(protected SerialService $service) {}

    /** ?date=YYYY-MM-DD দিয়ে তারিখ অনুযায়ী কিউ (ফাঁকা হলে আজ) */
    public function index(Request $request)
    {
        $doctor = auth()->user()->doctor;

        $today = SerialService::today();
        $date = $this->service->normalizeDate($request->query('date'));
        $isToday = $date === $today;

        $serials = $this->service->queueFor($doctor, $date);
        $counts = $this->service->counts($serials);

        // "বর্তমানে চলছে / পরবর্তী রোগী" শুধু আজকের তারিখে
        $current = $isToday ? $serials->firstWhere('status', 'in_progress') : null;
        $next = $isToday ? $serials->firstWhere('status', 'waiting') : null;

        $upcoming = $this->service->upcomingDates($doctor);

        $d = Carbon::parse($date);
        $prevDate = $d->copy()->subDay()->toDateString();
        $nextDate = $d->copy()->addDay()->toDateString();
        $yesterday = Carbon::parse($today)->subDay()->toDateString();
        $tomorrow = Carbon::parse($today)->addDay()->toDateString();

        return view('doctor.queue.index', compact(
            'serials', 'counts', 'current', 'next',
            'date', 'today', 'isToday', 'upcoming',
            'prevDate', 'nextDate', 'yesterday', 'tomorrow'
        ));
    }

    public function status(Request $request, Serial $serial)
    {
        $doctor = auth()->user()->doctor;
        abort_unless($serial->doctor_id === $doctor->id, 403);

        $data = $request->validate(['status' => 'required|in:waiting,skipped,cancelled']);

        try {
            $this->service->changeStatus($serial, $data['status']);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "সিরিয়াল #{$serial->serial_number} আপডেট হয়েছে।");
    }

    /**
     * Prescription page-e Start chaple (AJAX) serial "চলছে" hoy.
     * Ager chalu thaka serial abar "অপেক্ষায়" fire jay.
     */
    public function start(Serial $serial)
    {
        $doctor = auth()->user()->doctor;
        abort_unless($serial->doctor_id === $doctor->id, 403);

        try {
            $this->service->start($serial);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true, 'status' => $serial->fresh()->status]);
    }
}