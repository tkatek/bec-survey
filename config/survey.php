<?php

return [
    'reporting_timezone' => env('SURVEY_REPORTING_TIMEZONE', 'Africa/Casablanca'),
    'public_session_driver' => env('SURVEY_PUBLIC_SESSION_DRIVER', 'cookie'),
    'public_session_cookie' => env('SURVEY_PUBLIC_SESSION_COOKIE', 'bec-survey-anonymous'),
    'public_cache_seconds' => (int) env('SURVEY_PUBLIC_CACHE_SECONDS', 3600),

    'analytics' => [
        'maximum_custom_range_days' => 366,
        'snapshot_minutes' => 15,
        'cache_fresh_seconds' => (int) env('SURVEY_ANALYTICS_CACHE_FRESH_SECONDS', 15),
        'cache_stale_seconds' => (int) env('SURVEY_ANALYTICS_CACHE_STALE_SECONDS', 60),
    ],

    'alerts' => [
        'threshold' => 11,
        'monitoring_days' => 30,
        'material_increase' => 10,
    ],

    'exports' => [
        'per_minute' => 5,
        'lock_seconds' => 45,
    ],
];
