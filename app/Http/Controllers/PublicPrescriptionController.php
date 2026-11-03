<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PublicPrescriptionController extends Controller
{
    /** Public QR verification page: token lookup proves the prescription exists in the system. */
    public function verify(string $token)
    {
        $prescription = Prescription::where('verification_token', $token)
            ->with(['patient', 'doctor.user', 'chamber', 'medicines'])
            ->first();

        $headers = [
            'Cache-Control' => 'no-store, private',
            'Referrer-Policy' => 'no-referrer',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ];

        if ($prescription?->patient) {
            $prescription->patient->append('patient_code');
        }

        return response()->view('public.prescription-verification', [
            'prescription' => $prescription,
        ], $prescription ? 200 : 404, $headers);
    }

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

        $verificationUrl = route('prescription.verify', $prescription->ensureVerificationToken());

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
            ? '<img src="' . e(asset('storage/' . $doctor->photo)) . '" style="max-width:70px;">'
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
            'doctor_name'      => e($doctor->user->name ?? ''),
            'doctor_degree'    => e($doctor->qualification ?? ''),
            'doctor_bmdc'      => e($doctor->bmdc_number ?? ''),
            'chamber_name'     => e($chamber->chamber_name ?? ''),
            'hospital_name'    => e($chamber->hospital_name ?? ''),
            'hospital_address' => e($chamber->address ?? ''),
            'hospital_phone'   => e($chamber->phone ?? ''),
            'date'             => e($prescription->created_at->format('d M Y')),
            'patient_name'     => e($p->name ?? ''),
            'patient_id'       => e($p->patient_code ?? ''),
            'patient_code'     => e($p->patient_code ?? ''),
            'patient_age'      => e($p->age ?? ''),
            'patient_gender'   => e($p->gender ?? ''),
            'patient_weight'   => e(($p && $p->weight) ? $p->weight . ' kg' : ''),
            'blood_group'      => e($p->blood_group ?? ''),
            'patient_address'  => e($p->address ?? ''),
            'bp'               => e($prescription->bp ?? ''),
            'pulse'            => e($prescription->pulse ?? ''),
            'temperature'      => e($prescription->temperature ?? ''),
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
            'verificationUrl' => $verificationUrl,
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
        $p->forceFill([
            'share_token' => null,
            'shared_at' => null,
            'verification_token' => null,
        ])->save();

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
