<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSerialRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Serial;
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

        $serials = $this->service->queueFor($doctor, $date);
        $counts = $this->service->counts($serials);
        $activeChamber = $doctor->activeChamber();

        $maxDate = SerialService::maxDate();
        $upcoming = $this->service->upcomingDates($doctor);

        return view('assistant.serials.index', compact(
            'doctor', 'serials', 'counts', 'activeChamber',
            'date', 'today', 'isToday', 'maxDate', 'upcoming'
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
            ->route('assistant.serials.index', ['date' => $dateStr])
            ->with('success', "সিরিয়াল #{$serial->serial_number} দেওয়া হয়েছে ({$serial->serial_date->format('d M Y')})।");
    }

    public function status(Request $request, Serial $serial)
    {
        $doctor = $this->doctor();
        abort_unless($serial->doctor_id === $doctor->id, 403);

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

        $patient = Patient::where('doctor_id', $doctor->id)->where('mobile', $mobile)->first();

        if (!$patient) {
            return response()->json(['found' => false]);
        }

        $open = Serial::where('doctor_id', $doctor->id)
            ->where('serial_date', $date)
            ->where('patient_id', $patient->id)
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