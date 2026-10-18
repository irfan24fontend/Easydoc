<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrescriptionRequest;
use App\Http\Requests\UpdatePrescriptionRequest;
use App\Models\Doctor;
use App\Models\DoctorMedicine;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Serial;
use App\Services\AiInteractionService;
use App\Services\AiSuggestionService;
use App\Services\MedicineSuggestionService;
use App\Services\PatientService;
use App\Services\PrescriptionService;
use App\Services\SerialService;
use App\Services\TreatmentPatternService;
use Exception;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function __construct(
        protected PrescriptionService $service,
        protected MedicineSuggestionService $suggestionService,
        protected PatientService $patientService,
        protected SerialService $serialService,
    ) {}

    /**
     * Logged-in user-er Doctor profile (editor warning charate ekhane ek jaygay rakha)
     */
    protected function currentDoctor(): Doctor
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return $user->doctor;
    }

    public function index(Request $request)
    {
        $doctor = $this->currentDoctor();

        $prescriptions = Prescription::where('doctor_id', $doctor->id)
            ->with('patient')
            ->withCount('children')
            ->when($request->search, function ($q) use ($request) {
                $term = trim($request->search);
                $q->whereHas('patient', function ($pq) use ($term) {
                    $pq->where(function ($w) use ($term) {
                        $w->where('mobile', 'like', "%{$term}%")
                          ->orWhere('name', 'like', "%{$term}%");

                        // Patient ID diye khuja: PT-0007
                        if (preg_match('/^PT-?\s*(\d+)$/i', $term, $m)) {
                            $w->orWhere('patient_no', (int) $m[1]);
                        }
                    });
                });
            })
            ->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('doctor.prescriptions.index', compact('prescriptions'));
    }

    /**
     * ?from={id}   thakle aager Investigation Advice theke tothyo tene form pre-fill hobe
     * ?serial={id} thakle oi serial-er rogir tothyo (naam, age, vitals...) auto load hobe
     */
    public function create(Request $request)
    {
        $doctor = $this->currentDoctor();
        $activeChamber = $doctor->activeChamber();

        $parent = null;
        if ($request->filled('from')) {
            $parent = Prescription::where('doctor_id', $doctor->id)
                ->where('type', 'investigation_only')
                ->with('patient')
                ->findOrFail($request->from);
        }

        // Start click korle ei serial-er rogi form-e auto load hoy
        $serial = null;
        if (!$parent && $request->filled('serial')) {
            $serial = Serial::with('patient')
                ->where('doctor_id', $doctor->id)
                ->findOrFail($request->serial);

            try {
                // Shudhu ajker khola serial-e Start hoy. Serial "চলছে" hoy, ager chalu thaka ta abar "অপেক্ষায়" fire jay.
                $this->serialService->start($serial);
                $serial->refresh();
            } catch (Exception $e) {
                return redirect()
                    ->route('doctor.queue.index', ['date' => $serial->serial_date->toDateString()])
                    ->with('error', $e->getMessage());
            }
        }

        // Ajker serial list (form-er bam pashe "আজকের সিরিয়াল" card-e dekhabe)
        $serials = Serial::with('patient')
            ->where('doctor_id', $doctor->id)
            ->where('serial_date', SerialService::today())
            ->orderBy('serial_number')
            ->get();

        return view('doctor.prescriptions.create', compact('doctor', 'activeChamber', 'parent', 'serial', 'serials'));
    }

    public function store(StorePrescriptionRequest $request)
    {
        $doctor = $this->currentDoctor();

        // Chamber chalu (Super Admin Active + doctor nije chalu) na thakle save hobe na
        if (! $doctor->activeChamber()) {
            return back()
                ->withInput()
                ->with('error', 'কোনো চেম্বার চালু নেই। আগে "আমার চেম্বার" থেকে একটি চেম্বার চালু করুন।');
        }

        $data = $request->validated();

        // Kon patient-er prescription: form-e bachai kora patient (patient_id).
        // Investigation report niye follow-up hole SOB SOMOY ager visit-er ei patient-i hobe,
        // form-e ja-i thakuk (ekoi mobile-er onno manush-er sathe mishe jabe na).
        $patientId = $request->input('patient_id');

        if ($request->filled('parent_prescription_id')) {
            $parent = Prescription::where('doctor_id', $doctor->id)
                ->where('type', 'investigation_only')
                ->find($request->input('parent_prescription_id'));

            if ($parent) {
                $patientId = $parent->patient_id;
            }
        }

        $data['patient_id'] = $patientId ?: null;

        // PatientService request() theke-o ei patient_id pay, tai PrescriptionService
        // je format-e-i patient-er data pathak, sothik patient-i bebohar hoy.
        $request->merge(['patient_id' => $data['patient_id']]);

        try {
            $prescription = $this->service->create($doctor, $data);
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // serial_id thakle PrescriptionService nijei serial-ta "সম্পন্ন" kore ar prescription_id boshay

        $prescription->loadMissing('patient');
        $code = $prescription->patient?->patient_code;

        return redirect()
            ->route('doctor.prescriptions.print', $prescription->id)
            ->with('success', 'Prescription created successfully' . ($code ? " (Patient ID: {$code})" : ''));
    }

    public function show(Prescription $prescription)
    {
        $doctor = $this->currentDoctor();
        abort_unless($prescription->doctor_id === $doctor->id, 403);

        $prescription->load(['patient', 'medicines', 'chamber']);

        return view('doctor.prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        $doctor = $this->currentDoctor();
        abort_unless($prescription->doctor_id === $doctor->id, 403);

        $prescription->load(['patient', 'medicines']);
        $activeChamber = $doctor->activeChamber();

        return view('doctor.prescriptions.edit', compact('prescription', 'doctor', 'activeChamber'));
    }

    public function update(UpdatePrescriptionRequest $request, Prescription $prescription)
    {
        $doctor = $this->currentDoctor();
        abort_unless($prescription->doctor_id === $doctor->id, 403);

        $this->service->update($prescription, $request->validated());

        return redirect()
            ->route('doctor.prescriptions.show', $prescription->id)
            ->with('success', 'Prescription updated successfully');
    }

    public function reprint(Prescription $prescription)
    {
        $doctor = $this->currentDoctor();
        abort_unless($prescription->doctor_id === $doctor->id, 403);

        try {
            $this->service->reprint($doctor, $prescription);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('doctor.prescriptions.print', $prescription->id);
    }

    public function suggestMedicine(Request $request)
    {
        $doctor = $this->currentDoctor();
        $term = $request->get('term', '');

        return response()->json($this->suggestionService->suggest($doctor->id, $term));
    }

    /**
     * Mobile ba Patient ID (PT-0007) diye rogi khuja.
     * Ekoi mobile-e ekadhik rogi thakle shobgulo-i ferot dey, proti rogir nijossho
     * "Report-er opekkhay thaka Investigation Advice" shoho.
     */
    public function lookupPatient(Request $request)
    {
        $doctor = $this->currentDoctor();
        $q = trim((string) $request->get('q', $request->get('mobile', '')));

        if ($q === '') {
            return response()->json(['found' => false, 'type' => null, 'patients' => []]);
        }

        [$patients, $type] = $this->patientService->search($doctor->id, $q);

        $items = $patients->map(function (Patient $p) use ($doctor) {
            $pending = Prescription::where('doctor_id', $doctor->id)
                ->where('patient_id', $p->id)
                ->where('type', 'investigation_only')
                ->whereDoesntHave('children')
                ->latest()
                ->get(['id', 'investigations', 'created_at'])
                ->map(fn ($x) => [
                    'id' => $x->id,
                    'date' => $x->created_at->format('d M Y'),
                    'investigations' => $x->investigations,
                    'url' => route('doctor.prescriptions.create', ['from' => $x->id]),
                ])
                ->values();

            $last = $p->prescriptions()->first();

            return [
                'id' => $p->id,
                'code' => $p->patient_code,
                'name' => $p->name,
                'mobile' => $p->mobile,
                'age' => $p->age,
                'weight' => $p->weight,
                'gender' => $p->gender,
                'blood_group' => $p->blood_group,
                'address' => $p->address,
                'visits' => $p->prescriptions()->count(),
                'last_visit' => $last?->created_at?->format('d M Y'),
                'pending' => $pending,
            ];
        })->values();

        return response()->json([
            'found' => $items->isNotEmpty(),
            'type' => $type,
            'patients' => $items,
        ]);
    }

    public function topMedicines()
    {
        $doctor = $this->currentDoctor();

        $items = DoctorMedicine::where('doctor_id', $doctor->id)
            ->with('medicine')
            ->orderByDesc('is_favorite')
            ->orderByDesc('usage_count')
            ->take(8)
            ->get()
            ->filter(fn ($dm) => $dm->medicine && $dm->medicine->status)
            ->map(fn ($dm) => [
                'id' => $dm->medicine->id,
                'label' => $dm->medicine->brand_name . ($dm->medicine->strength ? ' (' . $dm->medicine->strength . ')' : ''),
                'generic_name' => $dm->medicine->generic_name,
            ])
            ->values();

        return response()->json($items);
    }

    public function patternSuggest(Request $request, TreatmentPatternService $pattern)
    {
        $doctor = $this->currentDoctor();

        return response()->json(
            $pattern->suggestForDiagnosis($doctor->id, $request->get('diagnosis', ''))
        );
    }

    public function aiDiagnosis(Request $request, AiSuggestionService $ai)
    {
        $request->validate(['symptoms' => 'required|string']);

        try {
            $result = $ai->suggestDiagnosis(
                $request->symptoms,
                $request->age,
                $request->gender,
                $request->bp,
                $request->pulse,
                $request->temperature,
                $request->risk_factors
            );
        } catch (Exception $e) {
            return response()->json(['error' => 'AI suggestion ব্যর্থ হয়েছে, আবার চেষ্টা করুন।'], 500);
        }

        return response()->json($result);
    }

    public function aiMedicine(Request $request, AiSuggestionService $ai)
    {
        $request->validate([
            'diagnosis' => 'required|string',
            'symptoms' => 'nullable|string',
        ]);

        try {
            $result = $ai->suggestMedicines(
                $request->diagnosis,
                $request->symptoms ?? '',
                $request->age,
                $request->gender,
                $request->risk_factors
            );
        } catch (Exception $e) {
            return response()->json(['error' => 'AI suggestion ব্যর্থ হয়েছে, আবার চেষ্টা করুন।'], 500);
        }

        $resolved = [];
        foreach (($result['medicines'] ?? []) as $item) {
            $generic = $item['generic_name'] ?? null;
            if (!$generic) {
                continue;
            }

            $match = Medicine::where('status', true)
                ->where('generic_name', 'like', "%{$generic}%")
                ->orderByDesc('is_sponsored')
                ->orderByDesc('sponsor_priority')
                ->first();

            $resolved[] = [
                'medicine_id' => $match?->id,
                'label' => $match
                    ? ($match->brand_name . ($match->strength ? " ({$match->strength})" : ''))
                    : $generic . ' (আপনার DB তে নেই)',
                'generic_name' => $generic,
                'is_sponsored' => (bool) ($match?->is_sponsored),
                'found' => (bool) $match,
                'dose' => $item['dose'] ?? '1+0+1',
                'duration' => $item['duration'] ?? '',
                'instruction' => $item['instruction'] ?? '',
            ];
        }

        return response()->json([
            'medicines' => $resolved,
            'advice' => $result['advice'] ?? '',
        ]);
    }

    /**
     * AI diye nirbachito ouchudh gulor moddhe conflict (interaction) check
     */
    public function checkInteractions(Request $request, AiInteractionService $ai)
    {
        $data = $request->validate([
            'medicine_ids'   => 'required|array|min:1',
            'medicine_ids.*' => 'nullable|integer',
            'age'            => 'nullable',
            'gender'         => 'nullable|string|max:20',
            'risk_factors'   => 'nullable|string|max:1000',
            'diagnosis'      => 'nullable|string|max:1000',
        ]);

        try {
            $result = $ai->check(
                $data['medicine_ids'],
                array_intersect_key($data, array_flip(['age', 'gender', 'risk_factors', 'diagnosis']))
            );
        } catch (\RuntimeException $e) {
            // AiInteractionService-er nijer message (jemon API key nei / limit shesh), doctor-ke dekhano nirapod
            return response()->json(['error' => $e->getMessage()], 500);
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI conflict check failed: ' . $e->getMessage());

            return response()->json(['error' => 'AI conflict check ব্যর্থ হয়েছে, আবার চেষ্টা করুন।'], 500);
        }

        return response()->json($result);
    }

    public function print(Prescription $prescription)
    {
        $doctor = $this->currentDoctor();
        abort_unless($prescription->doctor_id === $doctor->id, 403);

        $prescription->load(['patient', 'chamber.template', 'medicines']);

        $chamber = $prescription->chamber;
        $template = $chamber?->template;

        if (!$template) {
            return view('doctor.prescriptions.print-fallback', compact('prescription'));
        }

        if ($prescription->medicines->isEmpty()) {
            $medicineTable = '<div class="no-medicine-note" style="padding:14px 6px;font-size:12.5px;color:#64748b;">'
                . 'এখনো কোনো ঔষধ দেওয়া হয়নি। উপরের পরীক্ষাগুলোর রিপোর্ট নিয়ে আবার দেখা করুন।'
                . '</div>';
        } else {
            $medicineRows = '';
            foreach ($prescription->medicines as $i => $pm) {
                $medicineRows .= '<tr>'
                    . '<td>' . ($i + 1) . '</td>'
                    . '<td>' . e($pm->displayName()) . '</td>'
                    . '<td>' . e($pm->doseLabel()) . '</td>'
                    . '<td>' . e($pm->duration) . '</td>'
                    . '<td>' . e($pm->instruction) . '</td>'
                    . '</tr>';
            }
            $medicineTable = '<table class="medicine-table"><thead><tr><th>SL</th><th>Medicine</th><th>Dose</th><th>Duration</th><th>Instruction</th></tr></thead><tbody>' . $medicineRows . '</tbody></table>';
        }

        $findings = trim((string) $prescription->investigation_findings);
        $findingsBlock = $findings === ''
            ? ''
            : '<div class="rx-section"><div class="rx-label">Investigation Findings:</div><div class="rx-text">' . nl2br(e($findings)) . '</div></div>';

        $logo = $doctor->photo
            ? '<img src="' . asset('storage/' . $doctor->photo) . '" style="max-width:70px;">'
            : '<div style="width:55px;height:55px;border-radius:50%;border:3px solid #4f46e5;display:flex;align-items:center;justify-content:center;font-weight:700;">+</div>';

        $replacements = [
            'doctor_name' => $doctor->user->name ?? '',
            'doctor_degree' => $doctor->qualification ?? '',
            'doctor_bmdc' => $doctor->bmdc_number ?? '',
            'chamber_name' => $chamber->chamber_name ?? '',
            'hospital_name' => $chamber->hospital_name ?? '',
            'hospital_address' => $chamber->address ?? '',
            'hospital_phone' => $chamber->phone ?? '',
            'date' => $prescription->created_at->format('d M Y'),
            'patient_name' => $prescription->patient->name ?? '',
            'patient_id' => $prescription->patient->patient_code ?? '',
            'patient_code' => $prescription->patient->patient_code ?? '',
            'patient_age' => $prescription->patient->age ?? '',
            'patient_gender' => $prescription->patient->gender ?? '',
            'patient_weight' => $prescription->patient->weight ? $prescription->patient->weight . ' kg' : '',
            'blood_group' => $prescription->patient->blood_group ?? '',
            'patient_address' => $prescription->patient->address ?? '',
            'bp' => $prescription->bp ?? '',
            'pulse' => $prescription->pulse ?? '',
            'temperature' => $prescription->temperature ?? '',
            'diagnosis' => nl2br(e($prescription->diagnosis)),
            'clinical_complaints' => nl2br(e($prescription->clinical_complaints)),
            'risk_factors' => nl2br(e($prescription->risk_factors)),
            'investigations' => nl2br(e($prescription->investigations)),
            'investigation_findings' => nl2br(e($prescription->investigation_findings)),
            'investigation_findings_block' => $findingsBlock,
            'advice' => nl2br(e($prescription->advice)),
            'medicine_table' => $medicineTable,
            'logo' => $logo,
        ];

        $html = $template->html;
        foreach ($replacements as $key => $value) {
            $html = str_replace('{{' . $key . '}}', $value, $html);
        }

        return view('doctor.prescriptions.print', [
            'prescription' => $prescription,
            'html'         => $html,
            'css'          => $template->css,
            'paperSize'    => $template->paper_size,
        ]);
    }
}