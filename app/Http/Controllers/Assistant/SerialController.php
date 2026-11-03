<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSerialRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Serial;
use App\Models\Chamber;
use App\Services\SerialService;
use Exception;
use Illuminate\Http\Request;
class SerialController extends Controller
{
    public function __construct(protected SerialService $service) {}

    protected function doctor(): Doctor
    {
        $doctor = auth()->user()->assistantProfile?->doctor;
        abort_unless($doctor, 403, 'আপনি কোনো ডাক্তারের সাথে যুক্ত নন।');

        return $doctor;
    }

    public function index(Request $request)
    {
        $doctor = $this->doctor();

        $today = SerialService::today();
        $date = $this->service->normalizeDate($request->query('date'));
        $isToday = $date === $today;

        $chambers = auth()->user()->assistantProfile->chambers()
            ->where('request_status', Chamber::REQUEST_APPROVED)->where('status', true)->get();
        $operationalChambers = $chambers->filter(fn (Chamber $chamber) => $chamber->isOperational())->values();
        $selectedChamber = $operationalChambers->firstWhere('id', (int) $request->query('chamber_id')) ?? $operationalChambers->first();
        $serials = $selectedChamber ? $this->service->queueFor($doctor, $date, $selectedChamber->id) : collect();
        $counts = $this->service->counts($serials);
        $activeChamber = $selectedChamber;

        $maxDate = SerialService::maxDate();
        $upcoming = $selectedChamber ? $this->service->upcomingDates($doctor, 7, $selectedChamber->id) : collect();

        return view('assistant.serials.index', compact(
            'doctor', 'serials', 'counts', 'activeChamber',
            'date', 'today', 'isToday', 'maxDate', 'upcoming', 'operationalChambers', 'selectedChamber'
        ));
    }

    public function store(StoreSerialRequest $request)
    {
        $doctor = $this->doctor();

        try {
            $serial = $this->service->create($doctor, auth()->user(), $request->validated());
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $dateStr = $serial->serial_date->toDateString();

        return redirect()
            ->route('assistant.serials.index', ['date' => $dateStr, 'chamber_id' => $serial->chamber_id])
            ->with('success', "সিরিয়াল #{$serial->serial_number} দেওয়া হয়েছে ({$serial->serial_date->format('d M Y')})।");
    }

    public function status(Request $request, Serial $serial)
    {
        $doctor = $this->doctor();
        abort_unless($serial->doctor_id === $doctor->id && auth()->user()->assistantProfile->chambers()->whereKey($serial->chamber_id)->exists(), 403);

        $data = $request->validate(['status' => 'required|in:waiting,skipped,cancelled']);

        try {
            $this->service->changeStatus($serial, $data['status']);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "সিরিয়াল #{$serial->serial_number} আপডেট হয়েছে।");
    }

    public function lookupPatient(Request $request)
    {
        $doctor = $this->doctor();
        $mobile = trim($request->get('mobile', ''));
        $date = $this->service->normalizeDate($request->get('date'));
        $chamberId = $request->integer('chamber_id');
        abort_unless(auth()->user()->assistantProfile->chambers()->whereKey($chamberId)->exists(), 403);

        $patient = Patient::where('doctor_id', $doctor->id)->where('mobile', $mobile)->first();

        if (!$patient) {
            return response()->json(['found' => false]);
        }

        $open = Serial::where('doctor_id', $doctor->id)
            ->where('serial_date', $date)
            ->where('patient_id', $patient->id)
            ->where('chamber_id', $chamberId)
            ->whereIn('status', ['waiting', 'in_progress'])
            ->first();

        return response()->json([
            'found' => true,
            'name' => $patient->name,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'weight' => $patient->weight,
            'open_serial' => $open?->serial_number,
        ]);
    }
}
