<?php

namespace App\Services;

use App\Models\Medicine;

class MedicineService
{
    public function create(array $data): Medicine
    {
        $data['status'] = $data['status'] ?? false;
        $data['is_sponsored'] = $data['is_sponsored'] ?? false;

        return Medicine::create($data);
    }

    public function update(Medicine $medicine, array $data): Medicine
    {
        $data['status'] = $data['status'] ?? false;
        $data['is_sponsored'] = $data['is_sponsored'] ?? false;

        $medicine->update($data);

        return $medicine;
    }
}