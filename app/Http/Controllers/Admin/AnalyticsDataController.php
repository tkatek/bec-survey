<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnalyticsFilterRequest;
use App\Services\AnalyticsSnapshotService;
use App\Services\SurveyAnalyticsService;
use Illuminate\Http\JsonResponse;

class AnalyticsDataController extends Controller
{
    public function __invoke(
        AnalyticsFilterRequest $request,
        SurveyAnalyticsService $analytics,
        AnalyticsSnapshotService $snapshots,
    ): JsonResponse {
        $payload = $analytics->cachedBuild($request->filter());

        return response()->json([
            'analytics' => $payload,
            'snapshotToken' => $snapshots->store($request->session(), $payload),
        ]);
    }
}
