<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AnalyticsSnapshotService
{
    /**
     * Snapshots hold the full analytics payload and regularly exceed both the session
     * payload limits and the database max_allowed_packet, so they are cached in the
     * file store rather than the session-embedded cache.
     *
     * @param  array<string, mixed>  $payload
     */
    public function store(array $payload): string
    {
        $token = (string) Str::uuid();

        Cache::store('file')->put(
            $this->key($token),
            $payload,
            now()->addMinutes((int) config('survey.analytics.snapshot_minutes', 15)),
        );

        return $token;
    }

    /** @return array<string, mixed>|null */
    public function retrieve(string $token): ?array
    {
        $payload = Cache::store('file')->get($this->key($token));

        return is_array($payload) ? $payload : null;
    }

    private function key(string $token): string
    {
        return "survey-report-snapshot:{$token}";
    }
}
