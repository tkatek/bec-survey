<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AnalyticsFilterRequest;
use App\Services\AnalyticsSnapshotService;
use App\Services\SurveyAlertService;
use App\Services\SurveyAnalyticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        AnalyticsFilterRequest $request,
        SurveyAnalyticsService $analytics,
        AnalyticsSnapshotService $snapshots,
        SurveyAlertService $alerts,
    ): View {
        $filter = $request->filter();
        $payload = $analytics->cachedBuild($filter);
        $snapshotToken = $snapshots->store($request->session(), $payload);

        return view('dashboard', [
            'analytics' => $payload,
            'snapshotToken' => $snapshotToken,
            'alertData' => $alerts->toolbarData(),
        ]);
    }
}
