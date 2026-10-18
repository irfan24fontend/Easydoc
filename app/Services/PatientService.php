<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Patient;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PatientService
{
    /**
     * Prescription toiri-r somoy patient ঠিক করা।
     *
     * 1) patient_id thakle (form theke ba investigation follow-up theke): sei patient-i.
     *    PrescriptionService-e kono bodol na korleo kaj kore, karon patient_id
     *    controller-e request-e merge kora hoy, ekhane request() theke pawa jay.
     * 2) patient_id na thakle: ekoi doctor-er, EKOI MOBILE + EKOI NAAM-er patient thakle
     *    sei patient (mane ekoi manush), na hole NOTUN patient.
     *    Ekoi mobile-e onno naam hole ager patient-er sathe mishiye ফেলা হয় না.
     */
    public function findOrCreate(int $doctorId, array $data): Patient
    {
        $patientId = $data['patient_id'] ?? request()->input('patient_id');

        if (!empty($patientId)) {
            $patient = Patient::where('doctor_id', $doctorId)->find($patientId);

            if (!$patient) {
                throw new Exception('নির্বাচিত রোগী পাওয়া যায়নি। আবার রোগী নির্বাচন করুন।');
            }

            return $this->fill($patient, $data);
        }

        $same = Patient::where('doctor_id', $doctorId)
            ->where('mobile', $data['mobile'])
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim((string) $data['name']))])
            ->orderBy('patient_no')
            ->first();

        if ($same) {
            return $this->fill($same, $data);
        }

        return $this->create($doctorId, $data);
    }

    private function fill(Patient $patient, array $data): Patient
    {
        $patient->update([
            'name'        => $data['name'] ?? $patient->name,
            'mobile'      => $data['mobile'] ?? $patient->mobile,
            'age'         => $data['age'] ?? $patient->age,
            'weight'      => $data['weight'] ?? $patient->weight,
            'gender'      => $data['gender'] ?? $patient->gender,
            'blood_group' => $data['blood_group'] ?? $patient->blood_group,
            'address'     => $data['address'] ?? $patient->address,
        ]);

        return $patient;
    }

    public function create(int $doctorId, array $data): Patient
    {
        return DB::transaction(function () use ($doctorId, $data) {
            // Doctor-er row lock kore rakhi, jate ek-i somoy-e duto save hole ek-i Patient No na pay
            Doctor::whereKey($doctorId)->lockForUpdate()->first();

            $next = ((int) Patient::where('doctor_id', $doctorId)->max('patient_no')) + 1;

            return Patient::create([
                'doctor_id'   => $doctorId,
                'patient_no'  => $next,
                'name'        => $data['name'],
                'mobile'      => $data['mobile'],
                'age'         => $data['age'] ?? null,
                'weight'      => $data['weight'] ?? null,
                'gender'      => $data['gender'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'address'     => $data['address'] ?? null,
            ]);
        });
    }

    /**
     * Mobile ba Patient ID diye khuja.
     * "PT-0007" / "pt7"  => Patient ID
     * onno kichu          => mobile number (ekoi number-e ekadhik patient ashte pare)
     *
     * @return array{0: Collection<int, Patient>, 1: string}  [patients, 'code'|'mobile']
     */
    public function search(int $doctorId, string $query): array
    {
        $query = trim($query);

        if (preg_match('/^PT-?\s*(\d+)$/i', $query, $m)) {
            $patients = Patient::where('doctor_id', $doctorId)
                ->where('patient_no', (int) $m[1])
                ->get();

            return [$patients, 'code'];
        }

        $patients = Patient::where('doctor_id', $doctorId)
            ->where('mobile', $query)
            ->orderBy('patient_no')
            ->get();

        return [$patients, 'mobile'];
    }
}