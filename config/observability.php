<?php

return [
    'enabled' => env('OBSERVABILITY_ENABLED', true),

    'storage' => 'database',

    'sampling' => [
        'rate' => (float) env('OBSERVABILITY_SAMPLE_RATE', 1.0),
        'always_sample_errors' => true,
    ],

    'slow_query_threshold' => (int) env('OBSERVABILITY_SLOW_QUERY_MS', 200),

    // Пути, которые не трейсятся. glob-паттерны, как в Route::is()
    'ignore_paths' => [
        'observability',
        'observability/*',
        'up',
        'telescope',
        'telescope/*',
        '_debugbar',
        '_debugbar/*',
    ],

    // Что вырезать из bindings, чтобы не писать в БД пароли/токены
    'redact_bindings' => true,

    'retention' => [
        'days' => (int) env('OBSERVABILITY_RETENTION_DAYS', 7),
    ],

    'ui' => [
        'enabled' => env('OBSERVABILITY_UI_ENABLED', true),
        'path' => env('OBSERVABILITY_UI_PATH', 'observability'),
        // Если сайт публичный — оставьте ['web', 'auth'].
        // Если локальный и не хотите логиниться — верните ['web'].
        'middleware' => ['web', 'auth'],
        'per_page' => 50,
    ],
];
