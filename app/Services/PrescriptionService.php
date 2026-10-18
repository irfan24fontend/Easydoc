<?php

namespace App\Services;
use App\Models\Serial;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionMedicine;
use App\Models\Medicine;
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
        $chamber = $doctor->activeChamber();

        if (!$chamber) {
            throw new Exception('আপনার কোনো Active চেম্বার নেই। প্রেসক্রিপশন তৈরির আগে সুপার এডমিনের সাথে যোগাযোগ করে চেম্বার active করান।');
        }

        $isInvestigation = ($data['type'] ?? 'full') === 'investigation_only';

        $wallet = $doctor->wallet;
        $charge = $isInvestigation
            ? (float) config('easydoc.investigation_advice_charge', 2)
            : $doctor->getPrescriptionCharge();

        if (!$wallet || $wallet->balance < $charge) {
            throw new Exception('আপনার ব্যালেন্স অপর্যাপ্ত। এটা তৈরি করতে ন্যূনতম ৳' . number_format($charge, 2) . ' ব্যালেন্স প্রয়োজন। সুপার এডমিনের সাথে যোগাযোগ করে রিচার্জ করুন।');
        }

        // Report দেখে লেখা চূড়ান্ত প্রেসক্রিপশন হলে আগের Investigation Advice এর সাথে লিংক হবে
        $parentId = null;
        if (!$isInvestigation && !empty($data['parent_prescription_id'])) {
            $parentId = Prescription::where('doctor_id', $doctor->id)
                ->where('type', 'investigation_only')
                ->whereKey($data['parent_prescription_id'])
                ->value('id');
        }

        return DB::transaction(function () use ($doctor, $data, $chamber, $wallet, $charge, $isInvestigation, $parentId) {

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
            ]);

            $medicines = $isInvestigation ? [] : ($data['medicines'] ?? []);

            foreach ($medicines as $i => $row) {
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
            $this->walletService->debit($wallet, $charge, "{$label} #{$prescription->id} charge");

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
        $wallet = $doctor->wallet;
        $charge = (float) config('easydoc.reprint_charge', 5);

        if (!$wallet || $wallet->balance < $charge) {
            throw new Exception('Reprint করতে ন্যূনতম ৳' . number_format($charge, 2) . ' ব্যালেন্স প্রয়োজন। ব্যালেন্স অপর্যাপ্ত।');
        }

        $this->walletService->debit($wallet, $charge, "Reprint charge for Prescription #{$prescription->id}");
    }
}