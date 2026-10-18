<?php

namespace App\Services;

use App\Models\Medicine;
use App\Services\Ai\AiGateway;
use Illuminate\Support\Facades\Cache;

/**
 * AI diye ouchudh-er moddhe conflict (interaction / duplicate) check.
 * Gemini (primary) -> Grok (fallback) AiGateway diye cholche.
 *
 * check() return: ['has_warnings' => bool, 'warnings' => [...], 'checked' => int]
 * warning: pair, type (interaction|duplicate), severity (contraindicated|major|moderate|minor), effect, recommendation
 */
class AiInteractionService
{
    protected const SEVERITY_RANK = ['contraindicated' => 0, 'major' => 1, 'moderate' => 2, 'minor' => 3];

    protected const SYSTEM = <<<'TXT'
You are a clinical pharmacist assisting a licensed doctor in Bangladesh.
Check a list of prescribed medicines for (1) drug-drug interactions, (2) therapeutic duplication (same generic or same class),
and (3) conflicts with the patient's age, gender, diagnosis or risk factors (allergy, pregnancy, kidney/liver disease, etc).
Only report clinically meaningful findings. If nothing significant is found, return an empty warnings list.
TXT;

    public function __construct(protected AiGateway $ai) {}

    public function check(array $medicineIds, array $context = []): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $medicineIds))));
        $meds = Medicine::whereIn('id', $ids)->get();

        if ($meds->count() < 2) {
            return ['has_warnings' => false, 'warnings' => [], 'checked' => $meds->count()];
        }

        $list = $meds->map(function ($m) {
            return '- ' . $m->brand_name . ($m->strength ? " ({$m->strength})" : '') . ' — generic: ' . $m->generic_name;
        })->implode("\n");

        $ctx = '';
        foreach (['age' => 'Age', 'gender' => 'Gender', 'diagnosis' => 'Diagnosis', 'risk_factors' => 'Risk factors / history'] as $key => $label) {
            if (filled($context[$key] ?? null)) {
                $ctx .= "- {$label}: " . trim((string) $context[$key]) . "\n";
            }
        }

        $prompt = "Patient details:\n" . ($ctx ?: "- (not provided)\n")
            . "\nPrescribed medicines:\n{$list}\n\n"
            . "Return JSON exactly like:\n"
            . "{\"warnings\": [{\"pair\": \"Drug A + Drug B\", \"type\": \"interaction\", \"severity\": \"major\", \"effect\": \"what can happen, in Bengali\", \"recommendation\": \"what the doctor can do, in Bengali\"}]}\n"
            . "type is \"interaction\" or \"duplicate\". severity is one of contraindicated, major, moderate, minor. "
            . "For a patient-condition conflict, put the drug name and the condition in pair (e.g. \"Ibuprofen + Kidney disease\"). "
            . "Keep effect and recommendation to 1-2 short Bengali sentences each.";

        $ttl = (int) config('ai.cache_ttl', 600);
        $key = 'ai:interaction:' . md5($prompt);

        $data = $ttl > 0
            ? Cache::remember($key, max($ttl, 3600), fn () => $this->ai->json($prompt, self::SYSTEM))
            : $this->ai->json($prompt, self::SYSTEM);

        $warnings = collect($data['warnings'] ?? [])
            ->filter(fn ($w) => is_array($w) && filled($w['pair'] ?? null) && filled($w['effect'] ?? null))
            ->map(function ($w) {
                $sev = strtolower(trim((string) ($w['severity'] ?? 'moderate')));
                $type = strtolower(trim((string) ($w['type'] ?? 'interaction')));

                return [
                    'pair' => trim((string) $w['pair']),
                    'type' => $type === 'duplicate' ? 'duplicate' : 'interaction',
                    'severity' => array_key_exists($sev, self::SEVERITY_RANK) ? $sev : 'moderate',
                    'effect' => trim((string) $w['effect']),
                    'recommendation' => trim((string) ($w['recommendation'] ?? '')),
                ];
            })
            ->sortBy(fn ($w) => self::SEVERITY_RANK[$w['severity']])
            ->values()
            ->all();

        return [
            'has_warnings' => count($warnings) > 0,
            'warnings' => $warnings,
            'checked' => $meds->count(),
        ];
    }
}