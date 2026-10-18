<?php

namespace App\Services;

use App\Models\Chamber;
use Illuminate\Support\Facades\DB;

class ChamberService
{
    /**
     * Super Admin chamber toiri kore.
     * Active kore toiri korle ebong doctor-er kono chalu chamber na thakle,
     * chamber-ti doctor-er jonno nijei chalu hoye jay. Noile doctor nijer switch diye chalu korbe.
     */
    public function create(array $data): Chamber
    {
        return DB::transaction(function () use ($data) {
            $data['doctor_active'] = !empty($data['status'])
                && !$this->hasOperationalChamber((int) $data['doctor_id']);

            return Chamber::create($data);
        });
    }

    public function update(Chamber $chamber, array $data): Chamber
    {
        return DB::transaction(function () use ($chamber, $data) {
            $wasEnabled    = (bool) $chamber->status;
            $doctorChanged = isset($data['doctor_id']) && (int) $data['doctor_id'] !== (int) $chamber->doctor_id;

            // Onno doctor-ke dile doctor-er switch abar bondho theke shuru
            if ($doctorChanged) {
                $data['doctor_active'] = false;
            }

            $chamber->update($data);

            // Super Admin notun kore Active korle (ba onno doctor-ke diye Active rakhle)
            // doctor-er kono chalu chamber na thakle ei-ta chalu hoy
            $justEnabled = !$wasEnabled && $chamber->status;
            if (($justEnabled || $doctorChanged) && $chamber->status && !$chamber->doctor_active
                && !$this->hasOperationalChamber($chamber->doctor_id, $chamber->id)) {
                $chamber->update(['doctor_active' => true]);
            }

            return $chamber;
        });
    }

    /**
     * Doctor nijer chamber chalu/bondho kore.
     * Chalu korle doctor-er baki shob chamber-er switch bondho hoy (ekshathe ekta-i chalu thake).
     */
    public function setDoctorActive(Chamber $chamber, bool $active): Chamber
    {
        return DB::transaction(function () use ($chamber, $active) {
            if ($active) {
                Chamber::where('doctor_id', $chamber->doctor_id)
                    ->where('id', '!=', $chamber->id)
                    ->update(['doctor_active' => false]);
            }

            $chamber->update(['doctor_active' => $active]);

            return $chamber;
        });
    }

    protected function hasOperationalChamber(int $doctorId, ?int $exceptChamberId = null): bool
    {
        return Chamber::where('doctor_id', $doctorId)
            ->when($exceptChamberId, fn ($q) => $q->where('id', '!=', $exceptChamberId))
            ->operational()
            ->exists();
    }
}