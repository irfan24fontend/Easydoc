<?php

/*
|--------------------------------------------------------------------------
| AI providers (Gemini primary -> Grok fallback)
|--------------------------------------------------------------------------
| Proti request-e age primary provider try hoy. Fail korle (quota/timeout/5xx/
| invalid JSON/blocked) sathe sathe porer provider try hoy, doctor kichu tero pay na.
*/

$list = fn (?string $v) => array_values(array_filter(array_map('trim', explode(',', (string) $v))));

return [

    // Kon provider age try hobe (comma separated)
    'order' => $list(env('AI_PROVIDER_ORDER', 'gemini,grok')),

    // Proti HTTP call-er timeout (second). Duita provider-er mot somoy PHP max_execution_time-er cheye kom rakhun
    'timeout'         => (int) env('AI_TIMEOUT', 15),
    'connect_timeout' => (int) env('AI_CONNECT_TIMEOUT', 5),

    // Connection error / 5xx hole kotobar abar try korbe (ei provider-er bhetore)
    'retries' => (int) env('AI_RETRIES', 1),

    // Kono provider fail korle koto second tare "skip" kore sorasori porer provider-e jabe
    'cooldown' => (int) env('AI_COOLDOWN', 60),

    'temperature' => (float) env('AI_TEMPERATURE', 0.2),

    // Same prompt-er result koto second cache thakbe (0 = cache nei)
    'cache_ttl' => (int) env('AI_CACHE_TTL', 600),

    'providers' => [

        'gemini' => [
            'driver'   => 'gemini',
            'key'      => env('GEMINI_API_KEY'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
            // Ekadhik model dile ekta fail korle porer ta try hobe: GEMINI_MODELS=model-a,model-b
            'models'   => $list(env('GEMINI_MODELS', env('GEMINI_MODEL', 'gemini-2.5-flash'))),
        ],

        'grok' => [
            'driver'   => 'xai',
            'key'      => env('GROK_API_KEY', env('XAI_API_KEY')),
            'base_url' => env('GROK_BASE_URL', 'https://api.x.ai/v1'),
            'models'   => $list(env('GROK_MODELS', env('GROK_MODEL', 'grok-4.7'))),
        ],
    ],
];