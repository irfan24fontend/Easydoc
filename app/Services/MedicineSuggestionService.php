<?php

namespace App\Services;

use App\Models\DoctorFavoriteCompany;
use App\Models\DoctorMedicine;
use App\Models\Medicine;

class MedicineSuggestionService
{
    /** Max favourite-brand results shown on top, so other matches still get room. */
    protected const FAVORITE_BRAND_SLOTS = 8;

    /**
     * Ranking:
     * 1) shortcut exact match -> that medicine only
     * 2) the doctor's FAVOURITE BRAND (company) medicines: brands matching the typed text, and
     *    that company's brand of the same generic (type "Seclo" -> favourite company's omeprazole first)
     * 3) sponsored brands of the matched generics
     * 4) everything else, ordered by the doctor's favourites / usage
     */
    public function suggest(int $doctorId, string $term, int $limit = 12)
    {
        $term = trim($term);
        if ($term === '') {
            return collect();
        }

        $shortcutMatch = Medicine::where('shortcut', $term)->where('status', true)->first();
        if ($shortcutMatch) {
            return collect([$this->format($shortcutMatch, false, false)]);
        }

        $favCompanies = $this->favoriteCompanies($doctorId);

        $termFilter = function ($q) use ($term) {
            $q->where('brand_name', 'like', "{$term}%")
              ->orWhere('generic_name', 'like', "{$term}%");
        };

        $matches = Medicine::where('status', true)
            ->where($termFilter)
            ->limit(40)
            ->get();

        // Favourite-brand medicines matching the typed text (looked up separately so
        // the 40-row limit above can never push them out)
        $favDirect = empty($favCompanies)
            ? collect()
            : Medicine::where('status', true)
                ->whereIn('company', $favCompanies)
                ->where($termFilter)
                ->limit(40)
                ->get();

        $all = $matches->concat($favDirect)->unique('id')->values();

        if ($all->isEmpty()) {
            return collect();
        }

        $genericNames = $all->pluck('generic_name')->unique()->values();

        // Favourite company's brand of the same generic
        $favSameGeneric = empty($favCompanies)
            ? collect()
            : Medicine::where('status', true)
                ->whereIn('company', $favCompanies)
                ->whereIn('generic_name', $genericNames)
                ->limit(60)
                ->get();

        $favAll = $favDirect->concat($favSameGeneric)->unique('id')->values();
        $favIds = $favAll->pluck('id');

        $sponsored = Medicine::where('status', true)
            ->where('is_sponsored', true)
            ->whereIn('generic_name', $genericNames)
            ->orderByDesc('sponsor_priority')
            ->get();

        $sponsoredIds = $sponsored->pluck('id');

        $usage = DoctorMedicine::where('doctor_id', $doctorId)
            ->whereIn('medicine_id', $all->pluck('id')->merge($favIds)->merge($sponsoredIds)->unique()->values())
            ->get()
            ->keyBy('medicine_id');

        $score = function (Medicine $m) use ($usage) {
            $u = $usage->get($m->id);

            return (($u->is_favorite ?? false) ? 100000 : 0) + ($u->usage_count ?? 0);
        };

        $companyRank = array_flip($favCompanies);

        $favSorted = $favAll
            ->sort(function (Medicine $a, Medicine $b) use ($companyRank, $sponsoredIds, $score) {
                return [
                    $companyRank[$a->company] ?? 999,
                    $sponsoredIds->contains($a->id) ? 0 : 1,
                    -$score($a),
                    $a->brand_name,
                ] <=> [
                    $companyRank[$b->company] ?? 999,
                    $sponsoredIds->contains($b->id) ? 0 : 1,
                    -$score($b),
                    $b->brand_name,
                ];
            })
            ->values()
            ->take(self::FAVORITE_BRAND_SLOTS);

        $shownFavIds = $favSorted->pluck('id');

        $sponsoredRest = $sponsored->whereNotIn('id', $shownFavIds)->values();

        $rest = $all
            ->whereNotIn('id', $shownFavIds->merge($sponsoredIds)->unique()->values())
            ->sortByDesc(fn (Medicine $m) => $score($m))
            ->values();

        return $favSorted
            ->concat($sponsoredRest)
            ->concat($rest)
            ->unique('id')
            ->take($limit)
            ->map(fn (Medicine $m) => $this->format(
                $m,
                $sponsoredIds->contains($m->id),
                $shownFavIds->contains($m->id)
            ))
            ->values();
    }

    /** Company names (exactly as in medicines.company), most important first. */
    public function favoriteCompanies(int $doctorId): array
    {
        return DoctorFavoriteCompany::where('doctor_id', $doctorId)
            ->orderBy('priority')
            ->orderBy('id')
            ->pluck('company')
            ->all();
    }

    protected function format(Medicine $medicine, bool $isSponsored, bool $isFavoriteCompany): array
    {
        return [
            'id' => $medicine->id,
            'brand_name' => $medicine->brand_name,
            'generic_name' => $medicine->generic_name,
            'strength' => $medicine->strength,
            'dosage_form' => $medicine->dosage_form,
            'company' => $medicine->company,
            'is_sponsored' => $isSponsored,
            'is_favorite_company' => $isFavoriteCompany,
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
