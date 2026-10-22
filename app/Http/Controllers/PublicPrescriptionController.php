<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PublicPrescriptionController extends Controller
{
    /**
     * Public (login charai) prescription page.
     * Doctor\PrescriptionController::print() er logic hubohu - shudhu login/ownership check nai.
     */
    public function show(string $token)
    {
        $prescription = Prescription::where('share_token', $token)->firstOrFail();
        $doctor = Doctor::with('user')->findOrFail($prescription->doctor_id);

        $prescription->load(['patient', 'chamber.template', 'medicines']);

        $chamber  = $prescription->chamber;
        $template = $chamber?->template;

        $headers = [
            'X-Robots-Tag'    => 'noindex, nofollow, noarchive',
            'Referrer-Policy' => 'no-referrer',
        ];

        if (! $template) {
            return response()->view('doctor.prescriptions.print-fallback', [
                'prescription' => $prescription,
                'isPublic'     => true,
            ], 200, $headers);
        }

        if ($prescription->medicines->isEmpty()) {
            $medicineTable = '<div class="no-medicine-note" style="padding:14px 6px;font-size:12.5px;color:#64748b;">No medicine prescribed.</div>';
        } else {
            $rows = '';
            foreach ($prescription->medicines as $i => $pm) {
                $rows .= '<tr>'
                    . '<td>' . ($i + 1) . '</td>'
                    . '<td>' . e($pm->displayName()) . '</td>'
                    . '<td>' . e($pm->doseLabel()) . '</td>'
                    . '<td>' . e($pm->duration) . '</td>'
                    . '<td>' . e($pm->instruction) . '</td>'
                    . '</tr>';
            }
            $medicineTable = '<table class="medicine-table"><thead><tr><th>SL</th><th>Medicine</th><th>Dose</th><th>Duration</th><th>Instruction</th></tr></thead><tbody>' . $rows . '</tbody></table>';
        }

        $findings = trim((string) $prescription->investigation_findings);
        $findingsBlock = $findings === ''
            ? ''
            : '<div class="rx-section"><div class="rx-label">Investigation Findings:</div><div class="rx-text">' . nl2br(e($findings)) . '</div></div>';

        $logo = $doctor->photo
            ? '<img src="' . asset('storage/' . $doctor->photo) . '" style="max-width:70px;">'
            : '<div style="width:55px;height:55px;border-radius:50%;border:3px solid #4f46e5;display:flex;align-items:center;justify-content:center;font-weight:700;">+</div>';

        $p = $prescription->patient;

        $fuRaw = $prescription->follow_up_date ?? $prescription->followup_date
            ?? $prescription->next_visit_date ?? $prescription->next_visit ?? null;
        $fuText = '';
        if (! empty($fuRaw)) {
            try { $fuText = \Illuminate\Support\Carbon::parse($fuRaw)->format('d M Y'); }
            catch (\Throwable $e) { $fuText = (string) $fuRaw; }
        }
        $followupBlock = $fuText === ''
            ? ''
            : '<div class="rx-followup">Follow-up: ' . e($fuText) . ' <small>(abar dekha korun)</small></div>';

        $replacements = [
            'doctor_name'      => $doctor->user->name ?? '',
            'doctor_degree'    => $doctor->qualification ?? '',
            'doctor_bmdc'      => $doctor->bmdc_number ?? '',
            'chamber_name'     => $chamber->chamber_name ?? '',
            'hospital_name'    => $chamber->hospital_name ?? '',
            'hospital_address' => $chamber->address ?? '',
            'hospital_phone'   => $chamber->phone ?? '',
            'date'             => $prescription->created_at->format('d M Y'),
            'patient_name'     => $p->name ?? '',
            'patient_id'       => $p->patient_code ?? '',
            'patient_code'     => $p->patient_code ?? '',
            'patient_age'      => $p->age ?? '',
            'patient_gender'   => $p->gender ?? '',
            'patient_weight'   => ($p && $p->weight) ? $p->weight . ' kg' : '',
            'blood_group'      => $p->blood_group ?? '',
            'patient_address'  => $p->address ?? '',
            'bp'               => $prescription->bp ?? '',
            'pulse'            => $prescription->pulse ?? '',
            'temperature'      => $prescription->temperature ?? '',
            'diagnosis'            => nl2br(e($prescription->diagnosis)),
            'clinical_complaints'  => nl2br(e($prescription->clinical_complaints)),
            'risk_factors'         => nl2br(e($prescription->risk_factors)),
            'investigations'       => nl2br(e($prescription->investigations)),
            'investigation_findings'       => nl2br(e($prescription->investigation_findings)),
            'investigation_findings_block' => $findingsBlock,
            'advice'           => nl2br(e($prescription->advice)),
            'medicine_table'   => $medicineTable,
            'logo'             => $logo,
            'followup_block'   => $followupBlock,
            'followup_date'    => $fuText,
        ];

        $html = $template->html;
        foreach ($replacements as $key => $value) {
            $html = str_replace('{{' . $key . '}}', $value, $html);
        }

        return response()->view('doctor.prescriptions.print', [
            'prescription' => $prescription,
            'html'         => $html,
            'css'          => $template->css,
            'paperSize'    => $template->paper_size,
            'isPublic'     => true,
        ], 200, $headers);
    }

    /** Remote image (jemon logo/watermark) same-origin e dey - download er somoy CORS error ray na */
    public function image(Request $request)
    {
        $url = (string) $request->query('u', '');
        $parts = parse_url($url);
        abort_unless($parts && ($parts['scheme'] ?? '') === 'https' && ! empty($parts['host']), 404);

        $ip = gethostbyname($parts['host']);
        abort_unless(
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE),
            404
        );

        try {
            $res = \Illuminate\Support\Facades\Http::timeout(8)->withoutRedirecting()->get($url);
        } catch (\Throwable $e) {
            abort(404);
        }

        $type = (string) $res->header('Content-Type');
        abort_unless($res->successful() && str_starts_with($type, 'image/') && strlen($res->body()) < 6 * 1024 * 1024, 404);

        return response($res->body(), 200, [
            'Content-Type'  => $type,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /** Doctor "Share" click korle link toiri (na thakle) kore JSON e dey */
    public function share(Request $request, $id)
    {
        $p = $this->ownedPrescription($request, $id);

        if (! $p->share_token) {
            do {
                $t = \Illuminate\Support\Str::random(40);
            } while (Prescription::where('share_token', $t)->exists());
            $p->forceFill(['share_token' => $t, 'shared_at' => now()])->save();
        }

        return response()->json(['url' => route('prescription.public', $p->share_token)]);
    }

    public function revoke(Request $request, $id)
    {
        $p = $this->ownedPrescription($request, $id);
        $p->forceFill(['share_token' => null, 'shared_at' => null])->save();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Share link bondho kora hoyeche.');
    }

    private function ownedPrescription(Request $request, $id): Prescription
    {
        $p = Prescription::findOrFail($id);

        $user = $request->user();
        $allowed = [$user->id];
        if (method_exists($user, 'doctor') && $user->doctor) {
            $allowed[] = $user->doctor->id;
        }
        abort_unless(in_array($p->doctor_id ?? null, $allowed, true) || ($user->role ?? '') === 'admin', 403);

        return $p;
    }
}
