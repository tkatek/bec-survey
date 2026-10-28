<?php

namespace App\Services;

use Illuminate\Session\Store;
use Illuminate\Support\Str;

class AnalyticsSnapshotService
{
    /** @param array<string, mixed> $payload */
    public function store(Store $session, array $payload): string
    {
        $token = (string) Str::uuid();
        $session->cache()->put(
            $this->key($token),
            $payload,
            now()->addMinutes((int) config('survey.analytics.snapshot_minutes', 15)),
        );

        return $token;
    }

    /** @return array<string, mixed>|null */
    public function retrieve(Store $session, string $token): ?array
    {
        $payload = $session->cache()->get($this->key($token));

        return is_array($payload) ? $payload : null;
    }

    private function key(string $token): string
    {
        return "survey-report-snapshot:{$token}";
    }
}
