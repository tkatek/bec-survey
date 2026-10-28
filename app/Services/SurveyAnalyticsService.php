<?php

namespace App\Services;

use App\Models\ConversationRoomResponse;
use App\Models\FeedbackThemeAssignment;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use App\Models\SurveyVersion;
use App\Support\AnalyticsFilter;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurveyAnalyticsService
{
    private const CACHE_REVISION_KEY = 'survey:analytics:revision';

    /** @return array<string, mixed> */
    public function build(AnalyticsFilter $filter): array
    {
        $version = SurveyVersion::query()->active()->sole();

        return $this->buildForVersion($filter, $version);
    }

    /** @return array<string, mixed> */
    public function cachedBuild(AnalyticsFilter $filter): array
    {
        $version = SurveyVersion::query()->active()->sole();
        $freshSeconds = max(1, (int) config('survey.analytics.cache_fresh_seconds', 15));
        $staleSeconds = max($freshSeconds + 1, (int) config('survey.analytics.cache_stale_seconds', 60));
        $revision = (string) Cache::get(self::CACHE_REVISION_KEY, 'initial');
        $cacheKey = $this->cacheKey($filter, $version);
        $cached = Cache::flexible(
            $cacheKey,
            [$freshSeconds, $staleSeconds],
            fn (): array => $this->cacheEnvelope($revision, $this->buildForVersion($filter, $version)),
        );

        if (! is_array($cached) || ($cached['revision'] ?? null) !== $revision || ! is_array($cached['payload'] ?? null)) {
            $cached = $this->cacheEnvelope($revision, $this->buildForVersion($filter, $version));
            Cache::put($cacheKey, $cached, $staleSeconds);
        }

        return $cached['payload'];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{revision: string, payload: array<string, mixed>}
     */
    private function cacheEnvelope(string $revision, array $payload): array
    {
        return [
            'revision' => $revision,
            'payload' => $payload,
        ];
    }

    public function invalidateCache(): void
    {
        Cache::forever(self::CACHE_REVISION_KEY, (string) Str::uuid());
    }

    private function cacheKey(AnalyticsFilter $filter, SurveyVersion $version): string
    {
        $identity = [
            'version' => $version->id,
            'query' => $filter->query(),
            'periodStart' => $filter->localStart->toDateString(),
            'timezone' => $filter->timezone,
        ];

        return 'survey:analytics:'.sha1(json_encode($identity, JSON_THROW_ON_ERROR));
    }

    /** @return array<string, mixed> */
    private function buildForVersion(AnalyticsFilter $filter, SurveyVersion $version): array
    {
        $responses = $this->responseScope($filter);
        $responseCount = (clone $responses)->count();
        $questions = SurveyQuestion::withTrashed()
            ->where('survey_version_id', $version->id)
            ->with([
                'surveySection' => fn ($query) => $query->withTrashed(),
                'options' => fn ($query) => $query->withTrashed()->orderBy('sort_order')->orderBy('id'),
            ])
            ->ordered()
            ->get();
        $questionStableIds = $questions->pluck('stable_id')->filter()->values()->all();
        $answerCounts = $questionStableIds === []
            ? collect()
            : SurveyAnswer::query()
                ->join('survey_questions as answered_questions', 'answered_questions.id', '=', 'survey_answers.survey_question_id')
                ->join('survey_question_options as answered_options', 'answered_options.id', '=', 'survey_answers.survey_question_option_id')
                ->select([
                    'answered_questions.stable_id as question_stable_id',
                    'answered_options.stable_id as option_stable_id',
                    DB::raw('COUNT(DISTINCT survey_answers.conversation_room_response_id) as aggregate'),
                ])
                ->whereIn('answered_questions.stable_id', $questionStableIds)
                ->whereIn('survey_answers.conversation_room_response_id', (clone $responses)->select('conversation_room_responses.id'))
                ->groupBy('answered_questions.stable_id', 'answered_options.stable_id')
                ->get();
        $answeredCounts = $questionStableIds === []
            ? collect()
            : SurveyAnswer::query()
                ->join('survey_questions as answered_questions', 'answered_questions.id', '=', 'survey_answers.survey_question_id')
                ->select([
                    'answered_questions.stable_id as question_stable_id',
                    DB::raw('COUNT(DISTINCT survey_answers.conversation_room_response_id) as aggregate'),
                ])
                ->whereIn('answered_questions.stable_id', $questionStableIds)
                ->whereIn('survey_answers.conversation_room_response_id', (clone $responses)->select('conversation_room_responses.id'))
                ->groupBy('answered_questions.stable_id')
                ->get();
        $questionsPayload = $questions
            ->map(fn (SurveyQuestion $question): array => $this->questionPayload(
                $question,
                $responseCount,
                $answerCounts,
                $answeredCounts,
            ))
            ->values();
        $questionsByKey = $questionsPayload->keyBy('key');
        $series = $this->responseSeries($filter, $responses);
        $writtenQuestionIds = $this->matchingQuestionIds(
            $questions->where('type', SurveyQuestion::TYPE_LONG_TEXT),
        );

        return [
            'meta' => [
                'generatedAt' => now($filter->timezone)->toIso8601String(),
                'dataAsOf' => $filter->cutoff->toIso8601String(),
                'timezone' => $filter->timezone,
                'scope' => 'All completed student submissions',
                'filter' => $filter->summary(),
                'query' => $filter->query(),
                'exclusions' => 'Demo records and records without normalized answers are excluded. All completed student submissions in the selected period are included.',
            ],
            'overview' => [
                'responses' => $responseCount,
                'positiveActivity' => $this->positiveActivityKpi($questionsByKey->get('activity_usefulness')),
                'mainBarrier' => $this->leaderKpi($questionsByKey->get('attendance_reasons')),
                'writtenFeedback' => $this->writtenFeedbackKpi($filter, $writtenQuestionIds),
                'preferredTime' => $this->leaderKpi(
                    $questionsByKey->get('preferred_session_time'),
                    'Preferred time',
                ),
            ],
            'series' => $series,
            'questions' => $questionsPayload->all(),
            'dashboard' => [
                'attendance' => $questionsByKey->get('attendance_frequency'),
                'barriers' => $questionsByKey->get('attendance_reasons'),
                'format' => $questionsByKey->get('session_format'),
                'preferredTime' => $questionsByKey->get('preferred_session_time'),
                'likelihood' => $questionsByKey->get('attendance_likelihood'),
            ],
            'feedback' => $this->recentFeedback($filter, $writtenQuestionIds),
            'reviewedFeedback' => $this->reviewedFeedback($filter, $writtenQuestionIds),
            'feedbackThemes' => $this->feedbackThemeSummary($filter, $writtenQuestionIds),
            'insights' => $this->insights($questionsByKey, $responseCount),
        ];
    }

    /** @return Builder<ConversationRoomResponse> */
    public function responseScope(AnalyticsFilter $filter): Builder
    {
        return ConversationRoomResponse::query()
            ->where('is_demo', false)
            ->where('created_at', '>=', $filter->databaseStart)
            ->where('created_at', '<', $filter->databaseEnd)
            ->whereHas('answers');
    }

    /**
     * @param  Collection<int, object>  $answerCounts
     * @param  Collection<int, object>  $answeredCounts
     * @return array<string, mixed>
     */
    private function questionPayload(
        SurveyQuestion $question,
        int $responseCount,
        Collection $answerCounts,
        Collection $answeredCounts,
    ): array {
        $questionCounts = $answerCounts->where('question_stable_id', $question->stable_id);
        $answered = (int) ($answeredCounts->firstWhere('question_stable_id', $question->stable_id)?->aggregate ?? 0);

        $groupNames = $question->options->pluck('group_name')->filter()->unique()->values();
        $distributions = $question->type === SurveyQuestion::TYPE_LONG_TEXT
            ? collect()
            : ($groupNames->isEmpty()
                ? collect([$this->distribution($question->options, $questionCounts, $answered, null)])
                : $groupNames->map(function (string $groupName) use ($question, $questionCounts): array {
                    $options = $question->options->where('group_name', $groupName);
                    $groupCounts = $questionCounts->whereIn('option_stable_id', $options->pluck('stable_id'));

                    return $this->distribution(
                        $options,
                        $groupCounts,
                        (int) $groupCounts->sum('aggregate'),
                        $groupName,
                    );
                }));

        if ($question->type === SurveyQuestion::TYPE_GROUPED_CHOICE) {
            $answered = (int) $distributions->max('answered');
        }

        return [
            'id' => $question->id,
            'stableId' => $question->stable_id,
            'key' => $question->key,
            'prompt' => $question->prompt,
            'helpText' => $question->help_text,
            'type' => $question->type,
            'section' => $question->surveySection?->title ?? $question->section,
            'sectionOrder' => $question->surveySection?->sort_order ?? 0,
            'questionOrder' => $question->sort_order,
            'isActive' => $question->is_active && $question->deleted_at === null,
            'isMultiSelect' => $question->type === SurveyQuestion::TYPE_MULTIPLE_CHOICE,
            'maxSelections' => $question->max_selections,
            'totalResponses' => $responseCount,
            'answered' => $answered,
            'skipped' => max(0, $responseCount - $answered),
            'ineligible' => 0,
            'denominator' => $this->denominatorDescription($question, $answered),
            'distributions' => $distributions->values()->all(),
        ];
    }

    /**
     * @param  Collection<int, SurveyQuestionOption>  $options
     * @param  Collection<int, object>  $counts
     * @return array<string, mixed>
     */
    private function distribution(Collection $options, Collection $counts, int $answered, ?string $groupName): array
    {
        $countByOption = $counts->keyBy('option_stable_id');

        return [
            'group' => $groupName,
            'answered' => $answered,
            'options' => $options
                ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
                ->map(function (SurveyQuestionOption $option) use ($countByOption, $answered): array {
                    $count = (int) ($countByOption->get($option->stable_id)?->aggregate ?? 0);

                    return [
                        'id' => $option->id,
                        'stableId' => $option->stable_id,
                        'label' => $option->label,
                        'icon' => $option->icon,
                        'tone' => $option->tone,
                        'count' => $count,
                        'percentage' => $answered > 0 ? round(($count / $answered) * 100, 1) : null,
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function positiveActivityKpi(?array $question): array
    {
        $distribution = $this->distributionFromQuestion($question);
        $answered = (int) ($distribution['answered'] ?? 0);
        $positiveCount = collect($distribution['options'] ?? [])
            ->whereIn('label', ['Very useful', 'Somewhat useful'])
            ->sum('count');

        return [
            'label' => 'Positive activity feedback',
            'count' => (int) $positiveCount,
            'denominator' => $answered,
            'percentage' => $answered > 0 ? round(($positiveCount / $answered) * 100, 1) : null,
            'definition' => 'Very useful + Somewhat useful, divided by all valid activity-usefulness answers.',
        ];
    }

    /**
     * @param  Collection<int, int>  $questionIds
     * @return array{count: int, questionCount: int}
     */
    private function writtenFeedbackKpi(AnalyticsFilter $filter, Collection $questionIds): array
    {
        if ($questionIds->isEmpty()) {
            return ['count' => 0, 'questionCount' => 0];
        }

        $count = SurveyAnswer::query()
            ->whereIn('survey_question_id', $questionIds)
            ->whereNotNull('answer_value')
            ->where('answer_value', '!=', '')
            ->whereIn('conversation_room_response_id', $this->responseScope($filter)->select('conversation_room_responses.id'))
            ->distinct('conversation_room_response_id')
            ->count('conversation_room_response_id');

        return [
            'count' => $count,
            'questionCount' => $questionIds->count(),
        ];
    }

    /** @return array<string, mixed> */
    private function leaderKpi(?array $question, ?string $groupName = null): array
    {
        $distribution = $this->distributionFromQuestion($question, $groupName);
        $options = collect($distribution['options'] ?? []);
        $maximum = (int) $options->max('count');
        $leaders = $maximum > 0 ? $options->where('count', $maximum)->values() : collect();
        $answered = (int) ($distribution['answered'] ?? 0);

        return [
            'label' => $leaders->count() > 1
                ? 'Tie: '.$leaders->pluck('label')->join(' / ')
                : ($leaders->first()['label'] ?? 'N/A'),
            'count' => $maximum,
            'denominator' => $answered,
            'percentage' => $answered > 0 && $maximum > 0 ? round(($maximum / $answered) * 100, 1) : null,
            'isTie' => $leaders->count() > 1,
            'leaders' => $leaders->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function distributionFromQuestion(?array $question, ?string $groupName = null): array
    {
        if (! $question) {
            return ['answered' => 0, 'options' => []];
        }

        $distributions = collect($question['distributions']);

        return $groupName === null
            ? ($distributions->first() ?? ['answered' => 0, 'options' => []])
            : ($distributions->firstWhere('group', $groupName) ?? ['answered' => 0, 'options' => []]);
    }

    /**
     * @param  Collection<string, array<string, mixed>>  $questionsByKey
     * @return array<int, string>
     */
    private function insights(Collection $questionsByKey, int $responseCount): array
    {
        if ($responseCount === 0) {
            return [];
        }

        $attendance = $this->leaderKpi($questionsByKey->get('attendance_frequency'));
        $barrier = $this->leaderKpi($questionsByKey->get('attendance_reasons'));
        $format = $this->leaderKpi($questionsByKey->get('session_format'));
        $preferredTime = $this->leaderKpi(
            $questionsByKey->get('preferred_session_time'),
            'Preferred time',
        );
        $positiveActivity = $this->positiveActivityKpi($questionsByKey->get('activity_usefulness'));

        return collect([
            $this->leaderInsight(
                $attendance,
                fn (string $label, string $percentage, int $count): string => "{$percentage} of respondents answering attendance frequency selected {$label} ({$count} responses).",
                fn (int $leaders, string $percentage, int $count): string => "{$leaders} attendance-frequency answers are tied at {$percentage} ({$count} responses each).",
            ),
            $this->leaderInsight(
                $barrier,
                fn (string $label, string $percentage, int $count): string => "{$label} is the leading attendance barrier ({$percentage}, {$count} responses).",
                fn (int $leaders, string $percentage, int $count): string => "{$leaders} attendance barriers are tied for the lead at {$percentage} ({$count} responses each).",
            ),
            $this->leaderInsight(
                $format,
                fn (string $label, string $percentage, int $count): string => "{$label} is the most preferred session format ({$percentage}, {$count} responses).",
                fn (int $leaders, string $percentage, int $count): string => "{$leaders} session formats are tied for first at {$percentage} ({$count} responses each).",
            ),
            $this->leaderInsight(
                $preferredTime,
                fn (string $label, string $percentage, int $count): string => "{$label} is the most selected time to attend ({$percentage}, {$count} responses).",
                fn (int $leaders, string $percentage, int $count): string => "{$leaders} session times are tied for first at {$percentage} ({$count} responses each).",
            ),
            $positiveActivity['denominator'] > 0
                ? $this->percentageLabel($positiveActivity['percentage'])." of respondents answering activity usefulness found the activities useful ({$positiveActivity['count']} of {$positiveActivity['denominator']})."
                : null,
        ])->filter()->take(5)->values()->all();
    }

    /**
     * @param  array<string, mixed>  $leader
     * @param  callable(string, string, int): string  $message
     * @param  callable(int, string, int): string  $tieMessage
     */
    private function leaderInsight(array $leader, callable $message, callable $tieMessage): ?string
    {
        if ($leader['denominator'] === 0 || $leader['percentage'] === null || $leader['count'] === 0) {
            return null;
        }

        if ($leader['isTie']) {
            return $tieMessage(
                count($leader['leaders']),
                $this->percentageLabel($leader['percentage']),
                (int) $leader['count'],
            );
        }

        return $message(
            (string) $leader['label'],
            $this->percentageLabel($leader['percentage']),
            (int) $leader['count'],
        );
    }

    private function percentageLabel(float|int|null $percentage): string
    {
        if ($percentage === null) {
            return 'N/A';
        }

        return rtrim(rtrim(number_format($percentage, 1), '0'), '.').'%';
    }

    /**
     * @param  Builder<ConversationRoomResponse>  $responses
     * @return array<string, mixed>
     */
    private function responseSeries(AnalyticsFilter $filter, Builder $responses): array
    {
        $timestamps = (clone $responses)->orderBy('created_at')->pluck('created_at');
        $granularity = $filter->dayCount() === 1
            ? 'hour'
            : ($filter->dayCount() <= 62 ? 'day' : ($filter->dayCount() <= 366 ? 'week' : 'month'));
        $counts = $timestamps
            ->map(fn ($timestamp): CarbonImmutable => CarbonImmutable::parse($timestamp)->setTimezone($filter->timezone))
            ->countBy(fn (CarbonImmutable $timestamp): string => $this->bucketKey($timestamp, $granularity));
        $cursor = $this->bucketStart($filter->localStart, $granularity);
        $end = $filter->localEnd;
        $points = collect();

        while ($cursor->lessThan($end)) {
            $next = $this->nextBucket($cursor, $granularity);
            $bucketEnd = $next->lessThan($end) ? $next : $end;
            $key = $this->bucketKey($cursor, $granularity);
            $points->push([
                'key' => $key,
                'label' => $this->bucketLabel($cursor, $granularity),
                'fullLabel' => $this->bucketFullLabel($cursor, $bucketEnd, $filter->timezone),
                'value' => (int) ($counts[$key] ?? 0),
            ]);
            $cursor = $next;
        }

        return [
            'granularity' => $granularity,
            'label' => ucfirst($granularity).' buckets',
            'timezone' => $filter->timezone,
            'total' => (int) $points->sum('value'),
            'points' => $points->all(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function recentFeedback(AnalyticsFilter $filter, Collection $questionIds): array
    {
        if ($questionIds->isEmpty()) {
            return [];
        }

        return SurveyAnswer::query()
            ->with(['response:id,created_at'])
            ->whereIn('survey_question_id', $questionIds)
            ->whereNotNull('answer_value')
            ->where('answer_value', '!=', '')
            ->whereIn('conversation_room_response_id', $this->responseScope($filter)->select('conversation_room_responses.id'))
            ->orderByDesc(
                ConversationRoomResponse::query()
                    ->select('created_at')
                    ->whereColumn('conversation_room_responses.id', 'survey_answers.conversation_room_response_id')
                    ->limit(1),
            )
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn (SurveyAnswer $answer): array => [
                'id' => $answer->id,
                'responseId' => $answer->conversation_room_response_id,
                'text' => $answer->answer_value,
                'date' => $answer->response?->created_at?->setTimezone($filter->timezone)->format('M j, Y'),
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function reviewedFeedback(AnalyticsFilter $filter, Collection $questionIds): array
    {
        return SurveyAnswer::query()
            ->with(['response:id,created_at', 'themeAssignments.theme'])
            ->whereIn('survey_question_id', $questionIds)
            ->whereIn('conversation_room_response_id', $this->responseScope($filter)->select('conversation_room_responses.id'))
            ->whereHas('themeAssignments')
            ->whereNotNull('answer_value')
            ->where('answer_value', '!=', '')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (SurveyAnswer $answer): array => [
                'id' => $answer->id,
                'text' => $answer->answer_value,
                'themes' => $answer->themeAssignments->pluck('theme.label')->filter()->values()->all(),
                'date' => $answer->response?->created_at?->setTimezone($filter->timezone)->format('M j, Y'),
            ])
            ->all();
    }

    /** @return array<int, array{key: string, label: string, count: int}> */
    private function feedbackThemeSummary(AnalyticsFilter $filter, Collection $questionIds): array
    {
        return FeedbackThemeAssignment::query()
            ->join('feedback_themes', 'feedback_themes.id', '=', 'feedback_theme_assignments.feedback_theme_id')
            ->join('survey_answers', 'survey_answers.id', '=', 'feedback_theme_assignments.survey_answer_id')
            ->whereIn('survey_answers.survey_question_id', $questionIds)
            ->whereIn('survey_answers.conversation_room_response_id', $this->responseScope($filter)->select('conversation_room_responses.id'))
            ->select([
                'feedback_themes.key',
                'feedback_themes.label',
                DB::raw('COUNT(DISTINCT survey_answers.conversation_room_response_id) as aggregate'),
            ])
            ->groupBy('feedback_themes.key', 'feedback_themes.label')
            ->orderByDesc('aggregate')
            ->orderBy('feedback_themes.label')
            ->get()
            ->map(fn ($row): array => [
                'key' => $row->key,
                'label' => $row->label,
                'count' => (int) $row->aggregate,
            ])
            ->all();
    }

    /**
     * @param  Collection<int, SurveyQuestion>  $questions
     * @return Collection<int, int>
     */
    private function matchingQuestionIds(Collection $questions): Collection
    {
        $stableIds = $questions->pluck('stable_id')->filter()->values();

        if ($stableIds->isEmpty()) {
            return collect();
        }

        return SurveyQuestion::withTrashed()
            ->whereIn('stable_id', $stableIds)
            ->pluck('id');
    }

    private function denominatorDescription(SurveyQuestion $question, int $answered): string
    {
        $description = "Of {$answered} responses answering this question";

        if ($question->type === SurveyQuestion::TYPE_MULTIPLE_CHOICE) {
            return $description.'. Multiple selections allowed; percentages may exceed 100%.';
        }

        return $description;
    }

    private function bucketStart(CarbonImmutable $date, string $granularity): CarbonImmutable
    {
        return match ($granularity) {
            'hour' => $date->startOfHour(),
            'day' => $date->startOfDay(),
            'week' => $date->startOfWeek(),
            default => $date->startOfMonth(),
        };
    }

    private function nextBucket(CarbonImmutable $date, string $granularity): CarbonImmutable
    {
        return match ($granularity) {
            'hour' => $date->addHour(),
            'day' => $date->addDay(),
            'week' => $date->addWeek(),
            default => $date->addMonth(),
        };
    }

    private function bucketKey(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'hour' => $date->format('Y-m-d-H'),
            'day' => $date->format('Y-m-d'),
            'week' => $date->startOfWeek()->format('Y-m-d'),
            default => $date->format('Y-m'),
        };
    }

    private function bucketLabel(CarbonImmutable $date, string $granularity): string
    {
        return match ($granularity) {
            'hour' => $date->format('g A'),
            'day' => $date->format('M j'),
            'week' => 'Week of '.$date->format('M j'),
            default => $date->format('M Y'),
        };
    }

    private function bucketFullLabel(CarbonImmutable $start, CarbonImmutable $end, string $timezone): string
    {
        return $start->format('M j, Y g:i A').' - '.$end->format('M j, Y g:i A').' ('.$timezone.')';
    }
}
