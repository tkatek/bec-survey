<?php

return [
    'reporting_timezone' => env('SURVEY_REPORTING_TIMEZONE', 'Africa/Casablanca'),

    'analytics' => [
        'maximum_custom_range_days' => 366,
        'snapshot_minutes' => 15,
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
