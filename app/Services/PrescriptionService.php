<?php

namespace App\Services;
use App\Models\Serial;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\Medicine;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Exception;

class PrescriptionService
{
    public function __construct(
        protected PatientService $patientService,
        protected WalletService $walletService,
        protected MedicineSuggestionService $suggestionService,
    ) {}

    public function create(Doctor $doctor, array $data): Prescription
    {
        $isInvestigation = ($data['type'] ?? 'full') === 'investigation_only';
        $parentId = null;
        if (!$isInvestigation && !empty($data['parent_prescription_id'])) {
            $parentId = Prescription::where('doctor_id', $doctor->id)
                ->where('type', 'investigation_only')
                ->whereKey($data['parent_prescription_id'])
                ->value('id');
        }

        return DB::transaction(function () use ($doctor, $data, $isInvestigation, $parentId) {
            // Serialize prescription submissions for this doctor before checking the request token.
            $wallet = Wallet::where('doctor_id', $doctor->id)->lockForUpdate()->first();
            if (!$wallet) {
                throw new Exception('Wallet not found. Please contact the administrator.');
            }

            if (!empty($data['offline_ref'])) {
                $existing = Prescription::where('doctor_id', $doctor->id)
                    ->where('offline_ref', $data['offline_ref'])
                    ->first();
                if ($existing) {
                    return $existing;
                }
            }

            $chamber = $doctor->activeChamber();
            if (!$chamber) {
                throw new Exception('No active chamber is available for this doctor.');
            }

            $charge = $doctor->getCharge($isInvestigation ? 'investigation_advice' : 'prescription');
            if ((float) $wallet->balance < $charge) {
                throw new Exception('Insufficient wallet balance. Required: ৳' . number_format($charge, 2) . '. Please recharge and try again.');
            }

            $patient = $this->patientService->findOrCreate($doctor->id, [
                'name' => $data['patient_name'],
                'mobile' => $data['patient_mobile'],
                'age' => $data['patient_age'] ?? null,
                'weight' => $data['patient_weight'] ?? null,
                'gender' => $data['patient_gender'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'address' => $data['patient_address'] ?? null,
            ]);

            $prescription = Prescription::create([
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'chamber_id' => $chamber->id,
                'type' => $isInvestigation ? 'investigation_only' : 'full',
                'parent_prescription_id' => $parentId,
                'bp' => $data['bp'] ?? null,
                'pulse' => $data['pulse'] ?? null,
                'temperature' => $data['temperature'] ?? null,
                'clinical_complaints' => $data['clinical_complaints'] ?? null,
                'diagnosis' => $data['diagnosis'] ?? null,
                'risk_factors' => $data['risk_factors'] ?? null,
                'investigations' => $data['investigations'] ?? null,
                'investigation_findings' => $isInvestigation ? null : ($data['investigation_findings'] ?? null),
                'advice' => $data['advice'] ?? null,
                'followup_date' => $data['followup_date'] ?? null,
                'charge_deducted' => $charge,
                'offline_ref' => $data['offline_ref'] ?? null,
            ]);

            foreach (($isInvestigation ? [] : ($data['medicines'] ?? [])) as $i => $row) {
                $medicine = !empty($row['medicine_id']) ? Medicine::find($row['medicine_id']) : null;
                PrescriptionMedicine::create([
                    'prescription_id' => $prescription->id,
                    'medicine_id' => $medicine?->id,
                    'medicine_name' => $row['medicine_name'],
                    'medicine_type' => $row['medicine_type'] ?? null,
                    'dose_morning' => $row['dose_morning'] ?? 0,
                    'dose_noon' => $row['dose_noon'] ?? 0,
                    'dose_night' => $row['dose_night'] ?? 0,
                    'dose_text' => $row['dose_text'] ?? null,
                    'duration' => $row['duration'] ?? null,
                    'instruction' => $row['instruction'] ?? null,
                    'sort_order' => $i,
                ]);
                if ($medicine) {
                    $this->suggestionService->recordUsage($doctor->id, $medicine->id);
                }
            }

            if (!empty($data['serial_id'])) {
                $serial = Serial::where('doctor_id', $doctor->id)->find($data['serial_id']);
                if ($serial && !in_array($serial->status, ['done', 'cancelled'])) {
                    $serial->update(['status' => 'done', 'prescription_id' => $prescription->id]);
                }
            }

            $label = $isInvestigation ? 'Investigation advice' : 'Prescription';
            $idempotencyKey = 'prescription:' . $doctor->id . ':' . ($data['offline_ref'] ?? $prescription->id);
            $this->walletService->debit($wallet, $charge, "{$label} #{$prescription->id} charge", $idempotencyKey);

            return $prescription;
        });
    }

    public function update(Prescription $prescription, array $data): Prescription
    {
        return DB::transaction(function () use ($prescription, $data) {

            $prescription->patient->update([
                'name' => $data['patient_name'],
                'age' => $data['patient_age'] ?? null,
                'weight' => $data['patient_weight'] ?? null,
                'gender' => $data['patient_gender'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'address' => $data['patient_address'] ?? null,
            ]);

            $prescription->update([
                'bp' => $data['bp'] ?? null,
                'pulse' => $data['pulse'] ?? null,
                'temperature' => $data['temperature'] ?? null,
                'clinical_complaints' => $data['clinical_complaints'] ?? null,
                'diagnosis' => $data['diagnosis'] ?? null,
                'risk_factors' => $data['risk_factors'] ?? null,
                'investigations' => $data['investigations'] ?? null,
                'investigation_findings' => $data['investigation_findings'] ?? $prescription->investigation_findings,
                'advice' => $data['advice'] ?? null,
                'followup_date' => $data['followup_date'] ?? null,
            ]);

            // A changed prescription invalidates every QR printed for its old contents.
            $prescription->forceFill(['verification_token' => null])->save();

            $prescription->medicines()->delete();

            foreach (($data['medicines'] ?? []) as $i => $row) {
                $medicine = !empty($row['medicine_id']) ? Medicine::find($row['medicine_id']) : null;

                PrescriptionMedicine::create([
                    'prescription_id' => $prescription->id,
                    'medicine_id' => $medicine?->id,
                    'medicine_name' => $row['medicine_name'],
                    'medicine_type' => $row['medicine_type'] ?? null,
                    'dose_morning' => $row['dose_morning'] ?? 0,
                    'dose_noon' => $row['dose_noon'] ?? 0,
                    'dose_night' => $row['dose_night'] ?? 0,
                    'dose_text' => $row['dose_text'] ?? null,
                    'duration' => $row['duration'] ?? null,
                    'instruction' => $row['instruction'] ?? null,
                    'sort_order' => $i,
                ]);
            }

            return $prescription->fresh(['patient', 'medicines']);
        });
    }

    public function reprint(Doctor $doctor, Prescription $prescription): void
    {
        // Reprinting an existing prescription is always free.
    }

}
