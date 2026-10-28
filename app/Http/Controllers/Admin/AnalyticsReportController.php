<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnalyticsFilterRequest;
use App\Services\AnalyticsSnapshotService;
use App\Services\PdfReportService;
use App\Services\SurveyAlertService;
use App\Services\SurveyAnalyticsService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class AnalyticsReportController extends Controller
{
    public function __invoke(
        AnalyticsFilterRequest $request,
        SurveyAnalyticsService $analytics,
        AnalyticsSnapshotService $snapshots,
        SurveyAlertService $alerts,
        PdfReportService $pdf,
    ): Response {
        $filter = $request->filter();
        $snapshotToken = $request->string('snapshot')->toString();
        $payload = $snapshotToken !== ''
            ? $snapshots->retrieve($request->session(), $snapshotToken)
            : null;
        $snapshotMatches = is_array($payload)
            && ($payload['meta']['query'] ?? null) === $filter->query();

        if (! $snapshotMatches) {
            $payload = $analytics->build($filter);
            $payload['meta']['snapshotRefreshed'] = true;
        }

        $lockKey = 'survey-pdf:'.$request->user()->id.':'.sha1(json_encode($filter->query(), JSON_THROW_ON_ERROR));
        $contents = Cache::lock($lockKey, (int) config('survey.exports.lock_seconds', 45))
            ->block(3, fn (): string => $pdf->render($payload, $alerts->toolbarData()));
        $filename = sprintf(
            'BEC_Conversation_Room_Report_%s_to_%s.pdf',
            $payload['meta']['filter']['start'],
            $payload['meta']['filter']['end'],
        );

        return response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
