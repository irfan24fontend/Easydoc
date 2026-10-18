<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Ekta jaygay Gemini + Grok (xAI): primary fail korle auto fallback.
 *
 *   $data = app(AiGateway::class)->json($prompt, $system);   // array
 *   $text = app(AiGateway::class)->text($prompt, $system);   // string
 *
 * Shob provider fail korle AiUnavailableException (RuntimeException) chhode.
 */
class AiGateway
{
    /** Shesh call-er report (debug / ai:test-er jonno) */
    protected array $meta = ['provider' => null, 'model' => null, 'attempts' => []];

    public function json(string $prompt, ?string $system = null, ?array $only = null): array
    {
        return $this->run($prompt, $system, true, $only)['data'];
    }

    public function text(string $prompt, ?string $system = null, ?array $only = null): string
    {
        return $this->run($prompt, $system, false, $only)['text'];
    }

    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * Ekta provider-ke alada kore ping kore (ai:test command-er jonno)
     */
    public function probe(string $provider): array
    {
        try {
            $this->run('Reply with exactly this JSON: {"ok": true}', 'Respond with valid JSON only.', true, [$provider], true);
            $ok = true;
        } catch (Throwable $e) {
            $ok = false;
        }

        return ['ok' => $ok] + $this->meta;
    }

    /* ------------------------------------------------------------------ */

    protected function run(string $prompt, ?string $system, bool $expectJson, ?array $only = null, bool $ignoreCooldown = false): array
    {
        @set_time_limit(90);

        $system = trim(($system ?? '') . ($expectJson ? "\nRespond with valid JSON only. No markdown, no commentary." : ''));
        $this->meta = ['provider' => null, 'model' => null, 'attempts' => []];

        foreach ($this->orderedProviders($only, $ignoreCooldown) as $name) {
            $cfg = config("ai.providers.{$name}");
            $allFailed = true;

            foreach ((array) ($cfg['models'] ?? []) as $model) {
                $t0 = microtime(true);

                try {
                    $text = $cfg['driver'] === 'gemini'
                        ? $this->callGemini($cfg, $model, $system, $prompt, $expectJson)
                        : $this->callXai($cfg, $model, $system, $prompt, $expectJson);

                    $data = null;
                    if ($expectJson) {
                        $data = $this->decodeJson($text);
                        if ($data === null) {
                            throw new RuntimeException('invalid JSON in response', 422);
                        }
                    }

                    $this->meta['attempts'][] = ['provider' => $name, 'model' => $model, 'ok' => true, 'ms' => $this->ms($t0), 'error' => null];
                    $this->meta['provider'] = $name;
                    $this->meta['model'] = $model;

                    if (count($this->meta['attempts']) > 1) {
                        Log::info('AI fallback used', ['provider' => $name, 'model' => $model, 'attempts' => $this->meta['attempts']]);
                    }

                    return ['text' => $text, 'data' => $data ?? [], 'provider' => $name, 'model' => $model];
                } catch (Throwable $e) {
                    $this->meta['attempts'][] = ['provider' => $name, 'model' => $model, 'ok' => false, 'ms' => $this->ms($t0), 'error' => Str::limit($e->getMessage(), 300)];
                    Log::warning("AI provider failed [{$name}/{$model}]: " . $e->getMessage());

                    // Key/permission bhul hole ei provider-er onno model try kore lav nei
                    if (in_array((int) $e->getCode(), [401, 403], true)) {
                        break;
                    }
                }
            }

            if ($allFailed) {
                // Ei provider-e shob model fail: kichukkhon skip korbo, jate porer request-gulo fast hoy
                Cache::put("ai:cooldown:{$name}", true, now()->addSeconds((int) config('ai.cooldown', 60)));
            }
        }

        throw new AiUnavailableException(attempts: $this->meta['attempts']);
    }

    /**
     * Key ache emon provider-gulo, config order-e. Cooldown-e thaka gulo shesh-e (tobuo try hoy, last resort).
     */
    protected function orderedProviders(?array $only, bool $ignoreCooldown): array
    {
        $names = array_values(array_filter(
            (array) config('ai.order', ['gemini', 'grok']),
            fn ($n) => ($only === null || in_array($n, $only, true))
                && filled(config("ai.providers.{$n}.key"))
                && !empty(config("ai.providers.{$n}.models"))
        ));

        if ($ignoreCooldown) {
            return $names;
        }

        $fresh = array_filter($names, fn ($n) => !Cache::has("ai:cooldown:{$n}"));
        $cooled = array_filter($names, fn ($n) => Cache::has("ai:cooldown:{$n}"));

        return array_merge(array_values($fresh), array_values($cooled));
    }

    /* ------------------------- Providers ------------------------------ */

    protected function callGemini(array $cfg, string $model, string $system, string $prompt, bool $json): string
    {
        $payload = [
            'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => (float) config('ai.temperature', 0.2)],
        ];
        if ($system !== '') {
            $payload['systemInstruction'] = ['parts' => [['text' => $system]]];
        }
        if ($json) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
        }

        $url = rtrim($cfg['base_url'], '/') . "/models/{$model}:generateContent";
        $data = $this->post($url, $payload, ['x-goog-api-key' => $cfg['key']])->json();

        if (!empty($data['promptFeedback']['blockReason'])) {
            throw new RuntimeException('blocked: ' . $data['promptFeedback']['blockReason'], 422);
        }

        $cand = $data['candidates'][0] ?? null;
        $text = collect($cand['content']['parts'] ?? [])->pluck('text')->filter()->implode('');

        if (trim($text) === '') {
            throw new RuntimeException('empty response (finishReason: ' . ($cand['finishReason'] ?? 'unknown') . ')', 422);
        }

        return $text;
    }

    /** xAI Grok (OpenAI-compatible /chat/completions) */
    protected function callXai(array $cfg, string $model, string $system, string $prompt, bool $json): string
    {
        $messages = [];
        if ($system !== '') {
            $messages[] = ['role' => 'system', 'content' => $system];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => (float) config('ai.temperature', 0.2),
            'stream' => false,
        ];
        if ($json) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $url = rtrim($cfg['base_url'], '/') . '/chat/completions';
        $data = $this->post($url, $payload, ['Authorization' => 'Bearer ' . $cfg['key']])->json();

        $text = (string) data_get($data, 'choices.0.message.content', '');

        if (trim($text) === '') {
            throw new RuntimeException('empty response (finish_reason: ' . data_get($data, 'choices.0.finish_reason', 'unknown') . ')', 422);
        }

        return $text;
    }

    /* ----------------------------- HTTP ------------------------------- */

    protected function post(string $url, array $payload, array $headers): Response
    {
        $retries = max(0, (int) config('ai.retries', 1));

        for ($i = 0; $i <= $retries; $i++) {
            try {
                $res = Http::withHeaders($headers)
                    ->acceptJson()
                    ->asJson()
                    ->connectTimeout((int) config('ai.connect_timeout', 5))
                    ->timeout((int) config('ai.timeout', 15))
                    ->post($url, $payload);
            } catch (ConnectionException $e) {
                if ($i < $retries) {
                    usleep(300000);
                    continue;
                }
                throw new RuntimeException('connection/timeout: ' . $e->getMessage(), 0);
            }

            if ($res->successful()) {
                return $res;
            }

            // 429 (quota) retry kore lav nei, sorasori fallback-e jai. Shudhu server error-e retry.
            if (in_array($res->status(), [500, 502, 503, 504], true) && $i < $retries) {
                usleep(400000);
                continue;
            }

            throw new RuntimeException('HTTP ' . $res->status() . ': ' . Str::limit($res->body(), 300), $res->status());
        }

        throw new RuntimeException('AI request failed');
    }

    /* ---------------------------- Helpers ----------------------------- */

    protected function decodeJson(string $text): ?array
    {
        $t = trim($text);
        $t = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $t);

        $d = json_decode($t, true);
        if (is_array($d)) {
            return $d;
        }

        foreach (['/\{.*\}/s', '/\[.*\]/s'] as $re) {
            if (preg_match($re, $t, $m)) {
                $d = json_decode($m[0], true);
                if (is_array($d)) {
                    return $d;
                }
            }
        }

        return null;
    }

    protected function ms(float $t0): int
    {
        return (int) round((microtime(true) - $t0) * 1000);
    }
}