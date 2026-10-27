<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AnalyticsSnapshotService
{
    /** @param array<string, mixed> $payload */
    public function store(int $userId, array $payload): string
    {
        $token = (string) Str::uuid();
        Cache::put(
            $this->key($userId, $token),
            $payload,
            now()->addMinutes((int) config('survey.analytics.snapshot_minutes', 15)),
        );

        return $token;
    }

    /** @return array<string, mixed>|null */
    public function retrieve(int $userId, string $token): ?array
    {
        $payload = Cache::get($this->key($userId, $token));

        return is_array($payload) ? $payload : null;
    }

    private function key(int $userId, string $token): string
    {
        return "survey-report-snapshot:{$userId}:{$token}";
    }
}
