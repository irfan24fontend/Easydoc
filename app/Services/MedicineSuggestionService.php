<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\DoctorMedicine;

class MedicineSuggestionService
{
    /**
     * Ranking priority:
     * 1) shortcut exact match -> সরাসরি সেই মেডিসিন
     * 2) matched brand/generic এর generic_name এর মধ্যে sponsored ব্র্যান্ড -> সবার উপরে ⭐
     * 3) doctor এর personal usage_count/favorite অনুযায়ী বাকি ম্যাচ
     */
    public function suggest(int $doctorId, string $term, int $limit = 10)
    {
        $term = trim($term);
        if ($term === '') {
            return collect();
        }

        $shortcutMatch = Medicine::where('shortcut', $term)->where('status', true)->first();
        if ($shortcutMatch) {
            return collect([$this->format($shortcutMatch, false)]);
        }

        $matches = Medicine::where('status', true)
            ->where(function ($q) use ($term) {
                $q->where('brand_name', 'like', "{$term}%")
                  ->orWhere('generic_name', 'like', "{$term}%");
            })
            ->limit(40)
            ->get();

        if ($matches->isEmpty()) {
            return collect();
        }

        $genericNames = $matches->pluck('generic_name')->unique()->values();

        $sponsored = Medicine::where('status', true)
            ->where('is_sponsored', true)
            ->whereIn('generic_name', $genericNames)
            ->orderByDesc('sponsor_priority')
            ->get();

        $sponsoredIds = $sponsored->pluck('id');

        $usage = DoctorMedicine::where('doctor_id', $doctorId)
            ->whereIn('medicine_id', $matches->pluck('id'))
            ->get()
            ->keyBy('medicine_id');

        $rest = $matches->whereNotIn('id', $sponsoredIds)
            ->sortByDesc(function ($m) use ($usage) {
                $u = $usage->get($m->id);
                return (($u->is_favorite ?? false) ? 100000 : 0) + ($u->usage_count ?? 0);
            })
            ->values();

        return $sponsored->concat($rest)
            ->unique('id')
            ->take($limit)
            ->map(fn ($m) => $this->format($m, $sponsoredIds->contains($m->id)))
            ->values();
    }

    protected function format(Medicine $medicine, bool $isSponsored): array
    {
        return [
            'id' => $medicine->id,
            'brand_name' => $medicine->brand_name,
            'generic_name' => $medicine->generic_name,
            'strength' => $medicine->strength,
            'dosage_form' => $medicine->dosage_form,
            'is_sponsored' => $isSponsored,
            'label' => $medicine->brand_name . ($medicine->strength ? ' (' . $medicine->strength . ')' : ''),
        ];
    }

    public function recordUsage(int $doctorId, int $medicineId): void
    {
        $record = DoctorMedicine::firstOrCreate(
            ['doctor_id' => $doctorId, 'medicine_id' => $medicineId]
        );
        $record->increment('usage_count');
    }
}