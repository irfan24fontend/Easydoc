<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctor\PrescriptionController as DoctorPrescriptionController;
use App\Models\Chamber;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function search(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'string', 'max:32'],
            'chamber_id' => ['required', 'integer'],
        ]);

        $profile = auth()->user()->assistantProfile;
        $doctor = $profile?->doctor;
        abort_unless($profile && $doctor, 403);

        $chamber = $profile->chambers()
            ->whereKey((int) $data['chamber_id'])
            ->where('chambers.doctor_id', $doctor->id)
            ->where('request_status', Chamber::REQUEST_APPROVED)
            ->where('status', true)
            ->where('doctor_active', true)
            ->first();
        abort_unless($chamber, 403);

        $patientNo = Patient::patientNoFromSearchTerm($data['patient_id']);
        $patient = $patientNo === null
            ? null
            : Patient::where('doctor_id', $doctor->id)->where('patient_no', $patientNo)->first();

        if (! $patient) {
            return response()->json(['found' => false]);
        }

        $prescriptions = Prescription::where('doctor_id', $doctor->id)
            ->where('patient_id', $patient->id)
            ->where('chamber_id', $chamber->id)
            ->latest()
            ->limit(50)
            ->get(['id', 'type', 'created_at'])
            ->map(fn (Prescription $prescription) => [
                'date' => $prescription->created_at
                    ->timezone(config('easydoc.timezone'))
                    ->format('d M Y, h:i A'),
                'type' => $prescription->type === 'investigation_only' ? 'Investigation advice' : 'Prescription',
                'print_url' => route('assistant.prescriptions.print', [
                    'prescription' => $prescription->id,
                    'chamber_id' => $chamber->id,
                    'autoprint' => 1,
                ]),
            ])
            ->values();

        return response()->json([
            'found' => true,
            'patient' => [
                'name' => $patient->name,
                'patient_id' => $patient->patient_code,
            ],
            'prescriptions' => $prescriptions,
        ]);
    }

    public function print(Request $request, Prescription $prescription)
    {
        $profile = auth()->user()->assistantProfile;
        $doctor = $profile?->doctor;
        abort_unless($profile && $doctor, 403);

        $chamberId = $request->integer('chamber_id') ?: (int) $prescription->chamber_id;
        $chamber = $profile->chambers()
            ->whereKey($chamberId)
            ->where('chambers.doctor_id', $doctor->id)
            ->where('request_status', Chamber::REQUEST_APPROVED)
            ->where('status', true)
            ->where('doctor_active', true)
            ->first();

        abort_unless($chamber && (int) $prescription->chamber_id === (int) $chamber->id, 403);
        abort_unless((int) $prescription->doctor_id === (int) $doctor->id, 403);
        abort_unless($prescription->patient()->where('doctor_id', $doctor->id)->exists(), 403);

        return app(DoctorPrescriptionController::class)
            ->printForAssistant($doctor, $prescription);
    }
}
