<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnalyticsFilterRequest;
use App\Models\ConversationRoomResponse;
use App\Models\FeedbackTheme;
use App\Models\SurveyAlert;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use App\Services\AnalyticsSnapshotService;
use App\Services\SurveyAlertService;
use App\Services\SurveyAnalyticsService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommonAnswersController extends Controller
{
    public function __invoke(
        AnalyticsFilterRequest $request,
        SurveyAnalyticsService $analytics,
        AnalyticsSnapshotService $snapshots,
        SurveyAlertService $alerts,
    ): View {
        $filter = $request->filter();
        $payload = $analytics->build($filter);
        $search = $request->string('search')->trim()->toString();
        $section = $request->string('section')->toString();
        $questionKey = $request->string('question')->toString();
        $sort = $request->string('sort', 'original')->toString();
        $themeKey = $request->string('theme')->toString();
        $alert = $request->filled('alert')
            ? SurveyAlert::query()->find($request->integer('alert'))
            : null;
        $selectedQuestions = collect($payload['questions'])
            ->reject(fn (array $question): bool => $question['type'] === SurveyQuestion::TYPE_LONG_TEXT)
            ->when($section !== '', fn (Collection $questions) => $questions->where('section', $section))
            ->when($questionKey !== '', fn (Collection $questions) => $questions->where('key', $questionKey))
            ->when($search !== '', function (Collection $questions) use ($search): Collection {
                $needle = mb_strtolower($search);

                return $questions->filter(function (array $question) use ($needle): bool {
                    if (str_contains(mb_strtolower($question['prompt']), $needle)) {
                        return true;
                    }

                    return collect($question['distributions'])
                        ->flatMap(fn (array $distribution): array => $distribution['options'])
                        ->contains(fn (array $option): bool => str_contains(mb_strtolower($option['label']), $needle));
                });
            })
            ->map(function (array $question) use ($sort): array {
                if ($sort === 'count') {
                    $question['distributions'] = collect($question['distributions'])
                        ->map(function (array $distribution): array {
                            $distribution['options'] = collect($distribution['options'])
                                ->sortByDesc('count')
                                ->values()
                                ->all();

                            return $distribution;
                        })
                        ->all();
                }

                $leaders = collect($question['distributions'])
                    ->flatMap(fn (array $distribution): array => $distribution['options']);
                $maximum = (int) $leaders->max('count');
                $question['leaders'] = $maximum > 0
                    ? $leaders->where('count', $maximum)->values()->all()
                    : [];

                return $question;
            })
            ->values();
        $feedbackQuestionIds = SurveyQuestion::withTrashed()
            ->whereHas('version', fn ($query) => $query->where('number', $filter->version))
            ->where('key', 'one_change')
            ->pluck('id');
        $feedbackQuery = SurveyAnswer::query()
            ->with(['response:id,created_at', 'themeAssignments.theme'])
            ->whereIn('survey_question_id', $feedbackQuestionIds)
            ->whereNotNull('answer_value')
            ->where('answer_value', '!=', '')
            ->whereIn('conversation_room_response_id', $analytics->responseScope($filter)->select('conversation_room_responses.id'))
            ->when($themeKey !== '', fn ($query) => $query->whereHas(
                'themeAssignments.theme',
                fn ($themeQuery) => $themeQuery->where('key', $themeKey),
            ))
            ->when(
                $alert && str_starts_with($alert->rule_key, 'written_'),
                fn ($query) => $query->whereIn('conversation_room_response_id', $alert->evidence_response_ids ?? []),
            )
            ->when($search !== '', fn ($query) => $query->where('answer_value', 'like', '%'.$search.'%'))
            ->orderByDesc(
                ConversationRoomResponse::query()
                    ->select('created_at')
                    ->whereColumn('conversation_room_responses.id', 'survey_answers.conversation_room_response_id')
                    ->limit(1),
            )
            ->orderByDesc('id');
        $feedback = $feedbackQuery->paginate(8)->withQueryString();
        $themes = FeedbackTheme::query()->orderBy('id')->get();
        $themeCounts = DB::table('feedback_theme_assignments')
            ->join('feedback_themes', 'feedback_themes.id', '=', 'feedback_theme_assignments.feedback_theme_id')
            ->join('survey_answers', 'survey_answers.id', '=', 'feedback_theme_assignments.survey_answer_id')
            ->whereIn('survey_answers.survey_question_id', $feedbackQuestionIds)
            ->whereIn('survey_answers.conversation_room_response_id', $analytics->responseScope($filter)->select('conversation_room_responses.id'))
            ->select('feedback_themes.key', DB::raw('COUNT(DISTINCT survey_answers.conversation_room_response_id) as aggregate'))
            ->groupBy('feedback_themes.key')
            ->pluck('aggregate', 'feedback_themes.key');
        $uncategorizedCount = SurveyAnswer::query()
            ->whereIn('survey_question_id', $feedbackQuestionIds)
            ->whereNotNull('answer_value')
            ->where('answer_value', '!=', '')
            ->whereIn('conversation_room_response_id', $analytics->responseScope($filter)->select('conversation_room_responses.id'))
            ->whereDoesntHave('themeAssignments')
            ->distinct('conversation_room_response_id')
            ->count('conversation_room_response_id');

        return view('admin.common-answers', [
            'analytics' => $payload,
            'selectedQuestions' => $selectedQuestions,
            'sections' => collect($payload['questions'])->pluck('section')->unique()->values(),
            'feedback' => $feedback,
            'themes' => $themes,
            'themeCounts' => $themeCounts,
            'uncategorizedCount' => $uncategorizedCount,
            'selectedTab' => $request->string('tab', 'selected')->toString(),
            'selectedSection' => $section,
            'selectedQuestion' => $questionKey,
            'search' => $search,
            'sort' => $sort,
            'selectedTheme' => $themeKey,
            'selectedAlert' => $alert ? $alerts->serialize($alert) + [
                'evidenceResponseIds' => $alert->evidence_response_ids ?? [],
            ] : null,
            'snapshotToken' => $snapshots->store($request->user()->id, $payload),
            'versions' => SurveyVersion::query()
                ->where('status', '!=', SurveyVersion::STATUS_DRAFT)
                ->orderByDesc('number')
                ->get(['number', 'status']),
            'alertData' => $alerts->toolbarData(),
        ]);
    }
}
