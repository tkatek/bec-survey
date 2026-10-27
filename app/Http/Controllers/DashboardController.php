<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AnalyticsFilterRequest;
use App\Models\SurveyVersion;
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
        $payload = $analytics->build($filter);
        $snapshotToken = $snapshots->store($request->user()->id, $payload);

        return view('dashboard', [
            'analytics' => $payload,
            'snapshotToken' => $snapshotToken,
            'versions' => SurveyVersion::query()
                ->where('status', '!=', SurveyVersion::STATUS_DRAFT)
                ->orderByDesc('number')
                ->get(['number', 'status']),
            'alertData' => $alerts->toolbarData(),
        ]);
    }
}
