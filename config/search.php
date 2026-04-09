<?php

return [
    'cache' => [
        'enabled' => env('SEARCH_CACHE_ENABLED', true),

        // null = use `config/cache.php` default store (CACHE_STORE / CACHE_DRIVER)
        'store' => env('SEARCH_CACHE_STORE'),

        'ttl_seconds' => (int) env('SEARCH_CACHE_TTL_SECONDS', 60),
        'ttl_jitter_seconds' => (int) env('SEARCH_CACHE_TTL_JITTER_SECONDS', 10),

        'lock_seconds' => (int) env('SEARCH_CACHE_LOCK_SECONDS', 10),
        'lock_wait_seconds' => (int) env('SEARCH_CACHE_LOCK_WAIT_SECONDS', 2),

        'min_query_length' => (int) env('SEARCH_CACHE_MIN_QUERY_LENGTH', 2),
    ],
];

