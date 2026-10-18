<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\Medicine;

class TreatmentPatternService
{
    public function suggestForDiagnosis(int $doctorId, string $diagnosisText, int $limit = 6): array
    {
        $diagnosisText = trim($diagnosisText);
        if (mb_strlen($diagnosisText) < 3) {
            return [];
        }

        $normalized = mb_strtolower($diagnosisText);

        $prescriptions = Prescription::where('doctor_id', $doctorId)
            ->where('type', 'full')
            ->whereNotNull('diagnosis')
            ->whereRaw('LOWER(diagnosis) LIKE ?', ["%{$normalized}%"])
            ->with('medicines')
            ->latest()
            ->limit(50)
            ->get();

        if ($prescriptions->count() < 2) {
            $words = collect(preg_split('/[\s,]+/', $normalized))
                ->filter(fn ($w) => mb_strlen($w) >= 4)
                ->unique();

            if ($words->isNotEmpty()) {
                $prescriptions = Prescription::where('doctor_id', $doctorId)
                    ->where('type', 'full')
                    ->whereNotNull('diagnosis')
                    ->where(function ($q) use ($words) {
                        foreach ($words as $w) {
                            $q->orWhereRaw('LOWER(diagnosis) LIKE ?', ["%{$w}%"]);
                        }
                    })
                    ->with('medicines')
                    ->latest()
                    ->limit(50)
                    ->get();
            }
        }

        if ($prescriptions->isEmpty()) {
            return [];
        }

        $grouped = [];
        foreach ($prescriptions as $p) {
            foreach ($p->medicines as $pm) {
                $key = $pm->medicine_id ? 'id:' . $pm->medicine_id : 'name:' . mb_strtolower($pm->medicine_name);

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'count' => 0,
                        'medicine_id' => $pm->medicine_id,
                        'medicine_name' => $pm->medicine_name,
                        'medicine_type' => $pm->medicine_type,
                        'dose_morning' => $pm->dose_morning,
                        'dose_noon' => $pm->dose_noon,
                        'dose_night' => $pm->dose_night,
                        'dose_text' => $pm->dose_text,
                        'duration' => $pm->duration,
                        'instruction' => $pm->instruction,
                    ];
                }

                $grouped[$key]['count']++;
            }
        }

        return collect($grouped)->sortByDesc('count')->take($limit)->values()->map(function ($g) {
            $medicine = $g['medicine_id'] ? Medicine::find($g['medicine_id']) : null;

            $prefixMap = ['Tablet' => 'Tab.', 'Capsule' => 'Cap.', 'Syrup' => 'Syr.', 'Injection' => 'Inj.', 'Drop' => 'Drop', 'Cream' => 'Cream', 'Inhaler' => 'Inhaler'];
            $prefix = $prefixMap[$g['medicine_type']] ?? '';
            $isSyrup = $g['medicine_type'] === 'Syrup';

            return [
                'medicine_id' => $g['medicine_id'],
                'medicine_name' => $g['medicine_name'],
                'medicine_type' => $g['medicine_type'] ?? 'Tablet',
                'label' => trim($prefix . ' ' . $g['medicine_name']),
                'generic_name' => $medicine->generic_name ?? '',
                'dose' => $isSyrup ? $g['dose_text'] : $this->formatDose($g['dose_morning'], $g['dose_noon'], $g['dose_night']),
                'duration' => $g['duration'],
                'instruction' => $g['instruction'],
                'used_count' => $g['count'],
            ];
        })->toArray();
    }

    protected function formatDose($m, $n, $e): string
    {
        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 1), '0'), '.') ?: '0';
        return $fmt($m) . '+' . $fmt($n) . '+' . $fmt($e);
    }
}