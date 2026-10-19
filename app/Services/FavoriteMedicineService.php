<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorFavoriteCompany;
use App\Models\DoctorMedicine;
use App\Models\Medicine;

class FavoriteMedicineService
{
    /**
     * Medicines that are added automatically to every new prescription,
     * already shaped for the prescription form JavaScript.
     */
    public function defaultsFor(Doctor $doctor): array
    {
        return DoctorMedicine::where('doctor_id', $doctor->id)
            ->where('is_default', true)
            ->with('medicine')
            ->orderBy('default_sort')
            ->orderBy('id')
            ->get()
            ->filter(fn (DoctorMedicine $dm) => $dm->medicine && $dm->medicine->status)
            ->map(fn (DoctorMedicine $dm) => [
                'id'          => $dm->medicine->id,
                'name'        => $this->label($dm->medicine),
                'type'        => $dm->medicine->dosage_form ?: 'Tablet',
                'generic'     => mb_strtolower((string) $dm->medicine->generic_name),
                'dose'        => (string) $dm->default_dose,
                'doseText'    => (string) $dm->default_dose_text,
                'duration'    => (string) $dm->default_duration,
                'instruction' => (string) $dm->default_instruction,
            ])
            ->values()
            ->all();
    }

    /** Create or update the favourite / default settings for one medicine. */
    public function save(Doctor $doctor, int $medicineId, array $data): DoctorMedicine
    {
        $record = DoctorMedicine::firstOrNew([
            'doctor_id'   => $doctor->id,
            'medicine_id' => $medicineId,
        ]);

        $isDefault = (bool) ($data['is_default'] ?? false);

        $record->is_favorite = (bool) ($data['is_favorite'] ?? false);
        $record->is_default  = $isDefault;

        if ($isDefault) {
            $record->default_dose        = $data['default_dose'] ?? null;
            $record->default_dose_text   = $data['default_dose_text'] ?? null;
            $record->default_duration    = $data['default_duration'] ?? null;
            $record->default_instruction = $data['default_instruction'] ?? null;

            if (! $record->exists || ! $record->default_sort) {
                $record->default_sort = 1 + (int) DoctorMedicine::where('doctor_id', $doctor->id)->max('default_sort');
            }
        } else {
            $record->default_dose = $record->default_dose_text = $record->default_duration = $record->default_instruction = null;
        }

        $record->save();

        return $record;
    }

    /** Distinct company names that exist in the medicine list. */
    public function searchCompanies(string $term, int $limit = 12): array
    {
        return Medicine::whereNotNull('company')
            ->where('company', '!=', '')
            ->where('company', 'like', '%' . trim($term) . '%')
            ->distinct()
            ->orderBy('company')
            ->limit($limit)
            ->pluck('company')
            ->all();
    }

    public function addCompany(Doctor $doctor, string $company): DoctorFavoriteCompany
    {
        $next = 1 + (int) DoctorFavoriteCompany::where('doctor_id', $doctor->id)->max('priority');

        return DoctorFavoriteCompany::firstOrCreate(
            ['doctor_id' => $doctor->id, 'company' => $company],
            ['priority' => $next]
        );
    }

    public function label(Medicine $medicine): string
    {
        return $medicine->brand_name . ($medicine->strength ? ' (' . $medicine->strength . ')' : '');
    }
}
