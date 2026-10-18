<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavePatientRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Services\PatientService;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function __construct(protected PatientService $service) {}

    protected function doctor(): Doctor
    {
        return auth()->user()->doctor;
    }

    protected function own(Patient $patient): Patient
    {
        abort_unless($patient->doctor_id === $this->doctor()->id, 403);

        return $patient;
    }

    /** Report-er opekkhay: Investigation Advice dewa hoyeche, kintu final prescription hoyni */
    protected function pendingConstraint(): \Closure
    {
        return fn ($q) => $q->reorder()->where('type', 'investigation_only')->whereDoesntHave('children');
    }

    /** Search + filter (index ar CSV export duitoi ei query use kore) */
    protected function filtered(Request $request, Doctor $doctor)
    {
        $pending = $this->pendingConstraint();

        return Patient::where('doctor_id', $doctor->id)
            ->search($request->query('q'))
            ->when(
                in_array($request->query('gender'), ['Male', 'Female', 'Other'], true),
                fn ($q) => $q->where('gender', $request->query('gender'))
            )
            ->when($request->filled('blood_group'), fn ($q) => $q->where('blood_group', $request->query('blood_group')))
            ->when($request->filled('age_group'), function ($q) use ($request) {
                return match ($request->query('age_group')) {
                    'child'  => $q->where('age', '<=', 12),
                    'teen'   => $q->whereBetween('age', [13, 19]),
                    'adult'  => $q->whereBetween('age', [20, 59]),
                    'senior' => $q->where('age', '>=', 60),
                    default  => $q,
                };
            })
            ->when($request->boolean('pending'), fn ($q) => $q->whereHas('prescriptions', $pending))
            ->when($request->filled('visit_from') || $request->filled('visit_to'), function ($q) use ($request) {
                $q->whereHas('prescriptions', function ($p) use ($request) {
                    $p->reorder();
                    if ($request->filled('visit_from')) {
                        $p->whereDate('created_at', '>=', $request->query('visit_from'));
                    }
                    if ($request->filled('visit_to')) {
                        $p->whereDate('created_at', '<=', $request->query('visit_to'));
                    }
                });
            })
            ->withCount([
                'prescriptions as visits_count' => fn ($q) => $q->reorder(),
                'prescriptions as pending_reports_count' => $pending,
            ])
            ->withMax(['prescriptions as last_visit_at' => fn ($q) => $q->reorder()], 'created_at');
    }

    protected function sorted($query, ?string $sort)
    {
        return match ($sort) {
            'name'   => $query->orderBy('name'),
            'newest' => $query->orderByDesc('created_at'),
            'oldest' => $query->orderBy('patient_no'),
            'visits' => $query->orderByDesc('visits_count')->orderBy('name'),
            default  => $query->orderByDesc('last_visit_at')->orderByDesc('created_at'),
        };
    }

    public function index(Request $request)
    {
        $doctor = $this->doctor();

        $patients = $this->sorted($this->filtered($request, $doctor), $request->query('sort'))
            ->paginate(15)
            ->withQueryString();

        $base = Patient::where('doctor_id', $doctor->id);

        $stats = [
            'total' => (clone $base)->count(),
            'new_month' => (clone $base)->where('created_at', '>=', now()->startOfMonth())->count(),
            'today' => Prescription::where('doctor_id', $doctor->id)
                ->whereDate('created_at', now()->toDateString())
                ->distinct()->count('patient_id'),
            'pending' => (clone $base)->whereHas('prescriptions', $this->pendingConstraint())->count(),
        ];

        $bloodGroups = Patient::where('doctor_id', $doctor->id)
            ->whereNotNull('blood_group')->where('blood_group', '!=', '')
            ->distinct()->orderBy('blood_group')->pluck('blood_group');

        return view('doctor.patients.index', compact('patients', 'stats', 'bloodGroups'));
    }

    public function show(Patient $patient)
    {
        $this->own($patient);
        $today = now()->toDateString();

        $prescriptions = $patient->prescriptions()
            ->with(['medicines', 'chamber', 'children'])
            ->paginate(8)
            ->withQueryString();

        $pending = Prescription::where('doctor_id', $patient->doctor_id)
            ->where('patient_id', $patient->id)
            ->where('type', 'investigation_only')
            ->whereDoesntHave('children')
            ->latest()
            ->get();

        $stats = [
            'visits' => $patient->prescriptions()->reorder()->count(),
            'full' => $patient->prescriptions()->reorder()->where('type', 'full')->count(),
            'investigation' => $patient->prescriptions()->reorder()->where('type', 'investigation_only')->count(),
            'first' => $patient->prescriptions()->reorder()->min('created_at'),
            'last' => $patient->prescriptions()->reorder()->max('created_at'),
        ];

        $nextFollowup = $patient->prescriptions()->reorder()
            ->whereDate('followup_date', '>=', $today)
            ->orderBy('followup_date')
            ->first();

        $lastVitals = $patient->prescriptions()
            ->where(fn ($q) => $q->whereNotNull('bp')->orWhereNotNull('pulse')->orWhereNotNull('temperature'))
            ->first();

        // Vitals trend (shesh 12 ta visit, purono theke notun)
        $vitalRows = $patient->prescriptions()->reorder('created_at')
            ->where(fn ($q) => $q->whereNotNull('bp')->orWhereNotNull('pulse')->orWhereNotNull('temperature'))
            ->get(['id', 'created_at', 'bp', 'pulse', 'temperature'])
            ->take(-12)
            ->values();

        $num = fn ($v) => (is_numeric(trim((string) $v))) ? (float) $v : null;

        $vitalsChart = [
            'labels' => $vitalRows->map(fn ($r) => $r->created_at->format('d M'))->all(),
            'sys' => $vitalRows->map(fn ($r) => preg_match('/(\d{2,3})\s*\/\s*(\d{2,3})/', (string) $r->bp, $m) ? (int) $m[1] : null)->all(),
            'dia' => $vitalRows->map(fn ($r) => preg_match('/(\d{2,3})\s*\/\s*(\d{2,3})/', (string) $r->bp, $m) ? (int) $m[2] : null)->all(),
            'pulse' => $vitalRows->map(fn ($r) => $num($r->pulse))->all(),
            'temp' => $vitalRows->map(fn ($r) => $num($r->temperature))->all(),
        ];

        $serials = $patient->serials()
            ->orderByDesc('serial_date')->orderByDesc('serial_number')
            ->limit(8)->get();

        return view('doctor.patients.show', compact(
            'patient', 'prescriptions', 'pending', 'stats', 'nextFollowup', 'lastVitals', 'vitalsChart', 'serials'
        ));
    }

    public function create()
    {
        return view('doctor.patients.create');
    }

    public function store(SavePatientRequest $request)
    {
        $doctor = $this->doctor();
        $data = $request->validated();

        $same = $this->findDuplicate($doctor->id, $data['name'], $data['mobile']);
        if ($same) {
            return redirect()
                ->route('doctor.patients.show', $same)
                ->with('error', "এই নাম ও মোবাইলের রোগী আগে থেকেই আছে ({$same->patient_code})।");
        }

        $patient = $this->service->create($doctor->id, $data);

        return redirect()
            ->route('doctor.patients.show', $patient)
            ->with('success', "রোগী যোগ হয়েছে ({$patient->patient_code})।");
    }

    public function edit(Patient $patient)
    {
        $this->own($patient);

        return view('doctor.patients.edit', compact('patient'));
    }

    public function update(SavePatientRequest $request, Patient $patient)
    {
        $this->own($patient);
        $data = $request->validated();

        $same = $this->findDuplicate($patient->doctor_id, $data['name'], $data['mobile'], $patient->id);
        if ($same) {
            return back()->withInput()->withErrors([
                'name' => "এই নাম ও মোবাইলের আরেকজন রোগী আছে ({$same->patient_code})। একই মানুষ হলে আলাদা রোগী না বানিয়ে ঐ প্রোফাইল ব্যবহার করুন।",
            ]);
        }

        $patient->update($data);

        return redirect()
            ->route('doctor.patients.show', $patient)
            ->with('success', 'রোগীর তথ্য আপডেট হয়েছে।');
    }

    public function export(Request $request)
    {
        $doctor = $this->doctor();
        $rows = $this->sorted($this->filtered($request, $doctor), $request->query('sort'))->get();

        $filename = 'patients-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // Excel-e Bangla thik dekhanor jonno BOM

            fputcsv($out, ['Patient ID', 'Name', 'Mobile', 'Age', 'Gender', 'Weight (kg)', 'Blood Group', 'Address', 'Visits', 'Last Visit', 'Registered']);

            foreach ($rows as $p) {
                fputcsv($out, [
                    $p->patient_code,
                    $p->name,
                    $p->mobile,
                    $p->age,
                    $p->gender,
                    $p->weight,
                    $p->blood_group,
                    $p->address,
                    $p->visits_count,
                    $p->last_visit_at ? \Carbon\Carbon::parse($p->last_visit_at)->format('Y-m-d') : '',
                    $p->created_at?->format('Y-m-d'),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Ekoi doctor-er ekoi mobile + ekoi naam = ekoi manush (PatientService-er niyom-er sathe mil) */
    protected function findDuplicate(int $doctorId, string $name, string $mobile, ?int $exceptId = null): ?Patient
    {
        return Patient::where('doctor_id', $doctorId)
            ->where('mobile', trim($mobile))
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->orderBy('patient_no')
            ->first();
    }
}