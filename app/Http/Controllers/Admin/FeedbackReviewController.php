<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateFeedbackReviewRequest;
use App\Models\FeedbackTheme;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Services\SurveyAlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FeedbackReviewController extends Controller
{
    public function __invoke(
        UpdateFeedbackReviewRequest $request,
        SurveyAnswer $answer,
        SurveyAlertService $alerts,
    ): RedirectResponse {
        abort_unless(
            $answer->question()->withTrashed()->where('type', SurveyQuestion::TYPE_LONG_TEXT)->exists(),
            Response::HTTP_NOT_FOUND,
        );
        $themeIds = FeedbackTheme::query()
            ->whereIn('key', $request->validated('themes'))
            ->pluck('id');

        DB::transaction(function () use ($answer, $themeIds, $request): void {
            $answer->themeAssignments()->delete();
            $answer->themeAssignments()->createMany(
                $themeIds->map(fn (int $themeId): array => [
                    'feedback_theme_id' => $themeId,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ])->all(),
            );
        });

        defer(fn () => $alerts->evaluate());

        return back()->with('status', 'Feedback themes updated.');
    }
}
