<?php

namespace App\Services;

use App\Services\Ai\AiGateway;
use Illuminate\Support\Facades\Cache;

/**
 * AI diagnosis + medicine suggestion.
 * Ekhon Gemini (primary) -> Grok (fallback) AiGateway diye cholche.
 * Controller-er contract same: suggestDiagnosis() ['diagnoses'=>[..]], suggestMedicines() ['medicines'=>[..],'advice'=>'..'].
 */
class AiSuggestionService
{
    protected const SYSTEM = <<<'TXT'
You are a clinical decision-support assistant helping a licensed doctor in Bangladesh.
You only give suggestions for the doctor to review; the doctor makes the final decision.
Prefer drugs commonly available in Bangladesh. Respect allergies, pregnancy, age, kidney/liver disease and other risk factors.
TXT;

    public function __construct(protected AiGateway $ai) {}

    /**
     * @return array{diagnoses: string[]}
     */
    public function suggestDiagnosis(
        $symptoms,
        $age = null,
        $gender = null,
        $bp = null,
        $pulse = null,
        $temperature = null,
        $riskFactors = null
    ): array {
        $prompt = "Patient details:\n"
            . $this->ctx([
                'Age' => $age, 'Gender' => $gender, 'BP' => $bp, 'Pulse' => $pulse,
                'Temperature (F)' => $temperature, 'Risk factors / history' => $riskFactors,
            ])
            . "Clinical complaints: " . trim((string) $symptoms) . "\n\n"
            . "Return JSON exactly like: {\"diagnoses\": [\"Diagnosis 1\", \"Diagnosis 2\"]}\n"
            . "Give 3 to 5 most likely provisional diagnoses (English medical terms, short), most likely first.";

        $data = $this->cached('dx', $prompt, fn () => $this->ai->json($prompt, self::SYSTEM));

        $list = collect($data['diagnoses'] ?? $data)
            ->filter(fn ($d) => is_string($d) && trim($d) !== '')
            ->map(fn ($d) => trim($d))
            ->unique()
            ->take(6)
            ->values()
            ->all();

        return ['diagnoses' => $list];
    }

    /**
     * @return array{medicines: array<int, array{generic_name:string,dose:string,duration:string,instruction:string}>, advice: string}
     */
    public function suggestMedicines(
        $diagnosis,
        $symptoms = '',
        $age = null,
        $gender = null,
        $riskFactors = null
    ): array {
        $prompt = "Patient details:\n"
            . $this->ctx(['Age' => $age, 'Gender' => $gender, 'Risk factors / history' => $riskFactors])
            . "Diagnosis: " . trim((string) $diagnosis) . "\n"
            . "Clinical complaints: " . trim((string) $symptoms) . "\n\n"
            . "Suggest a typical outpatient treatment. Return JSON exactly like:\n"
            . "{\"medicines\": [{\"generic_name\": \"Paracetamol\", \"dose\": \"1+1+1\", \"duration\": \"৫ দিন\", \"instruction\": \"খাবার পরে\"}], \"advice\": \"short advice in Bengali\"}\n"
            . "Rules: 3 to 6 medicines; generic_name must be the generic (not brand); dose in M+A+N format with ASCII digits (e.g. 1+0+1, 0.5+0+0.5); "
            . "duration and instruction in Bengali (e.g. ৭ দিন, খাবার পরে, প্রয়োজনে); advice 1-3 short Bengali sentences.";

        $data = $this->cached('med', $prompt, fn () => $this->ai->json($prompt, self::SYSTEM));

        $medicines = collect($data['medicines'] ?? [])
            ->filter(fn ($m) => is_array($m) && filled($m['generic_name'] ?? null))
            ->map(function ($m) {
                $dose = trim((string) ($m['dose'] ?? ''));
                if (!preg_match('/^\d+(\.\d+)?\+\d+(\.\d+)?\+\d+(\.\d+)?$/', $dose)) {
                    $dose = '1+0+1';
                }

                return [
                    'generic_name' => trim((string) $m['generic_name']),
                    'dose' => $dose,
                    'duration' => trim((string) ($m['duration'] ?? '')),
                    'instruction' => trim((string) ($m['instruction'] ?? '')),
                ];
            })
            ->unique(fn ($m) => strtolower($m['generic_name']))
            ->take(8)
            ->values()
            ->all();

        return [
            'medicines' => $medicines,
            'advice' => trim((string) ($data['advice'] ?? '')),
        ];
    }

    /* ------------------------------------------------------------------ */

    protected function ctx(array $pairs): string
    {
        $out = '';
        foreach ($pairs as $label => $value) {
            if (filled($value)) {
                $out .= "- {$label}: " . trim((string) $value) . "\n";
            }
        }

        return $out === '' ? "- (no extra details)\n" : $out;
    }

    /** Same prompt dubar ashle AI call chhara-i result (kom khoroch, kom interrupt) */
    protected function cached(string $ns, string $prompt, \Closure $fn): array
    {
        $ttl = (int) config('ai.cache_ttl', 600);
        if ($ttl <= 0) {
            return $fn();
        }

        return Cache::remember("ai:{$ns}:" . md5($prompt), $ttl, $fn);
    }
}