<?php

namespace App\Services;

use App\Models\ConversationRoomResponse;
use App\Models\FeedbackTheme;
use App\Models\FeedbackThemeAssignment;
use App\Models\SurveyAlert;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use App\Models\SurveyVersion;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SurveyAlertService
{
    /** @var array<string, array{label: string, title: string, explanation: string}> */
    private const STRUCTURED_RULES = [
        'teaching_group_experience' => [
            'label' => "The group atmosphere or teaching style doesn't suit me.",
            'title' => 'Teaching/group experience feedback needs review',
            'explanation' => 'Respondents selected the structured teaching/group experience concern. This is a feedback signal requiring administrator review, not a performance verdict.',
        ],
        'technical_access' => [
            'label' => 'I have experienced technical problems.',
            'title' => 'Technical-access feedback needs review',
            'explanation' => 'Respondents reported technical problems when explaining lower attendance.',
        ],
        'scheduling' => [
            'label' => 'The available session times are inconvenient.',
            'title' => 'Scheduling feedback needs review',
            'explanation' => 'Respondents reported that the available session times are inconvenient.',
        ],
    ];

    public function evaluate(): void
    {
        Cache::lock('survey-alert-evaluation', 30)->get(function (): void {
            $version = SurveyVersion::query()->active()->first();

            if (! $version) {
                return;
            }

            [$windowStart, $windowEnd] = $this->monitoringWindow();

            foreach (self::STRUCTURED_RULES as $ruleKey => $rule) {
                $this->evaluateStructuredRule($version, $ruleKey, $rule, $windowStart, $windowEnd);
            }

            $this->evaluateWrittenTeachingRule($version, $windowStart, $windowEnd);
        });
    }

    /** @return array{alerts: array<int, array<string, mixed>>, unread: int, monitoringLabel: string, disabledRules: array<int, array{ruleKey: string, reason: string}>} */
    public function toolbarData(): array
    {
        [$windowStart, $windowEnd] = $this->monitoringWindow();
        $timezone = (string) config('survey.reporting_timezone', config('app.timezone'));
        $alerts = SurveyAlert::query()
            ->whereNull('resolved_at')
            ->latest('last_evaluated_at')
            ->get();
        $disabledRules = DB::table('survey_alert_rule_mappings')
            ->whereNotNull('disabled_reason')
            ->orderBy('rule_key')
            ->get(['rule_key', 'disabled_reason'])
            ->map(fn ($mapping): array => [
                'ruleKey' => str($mapping->rule_key)->replace('_', ' ')->title()->toString(),
                'reason' => $mapping->disabled_reason,
            ])
            ->all();

        return [
            'alerts' => $alerts->map(fn (SurveyAlert $alert): array => $this->serialize($alert))->all(),
            'unread' => $alerts->whereNull('read_at')->count(),
            'monitoringLabel' => $windowStart->setTimezone($timezone)->format('M j, Y').' - '.$windowEnd->setTimezone($timezone)->format('M j, Y').' ('.$timezone.')',
            'disabledRules' => $disabledRules,
        ];
    }

    /** @return array<string, mixed> */
    public function serialize(SurveyAlert $alert): array
    {
        $timezone = (string) config('survey.reporting_timezone', config('app.timezone'));

        return [
            'id' => $alert->id,
            'ruleKey' => $alert->rule_key,
            'title' => $alert->title,
            'severity' => $alert->severity,
            'explanation' => $alert->explanation,
            'matchingCount' => $alert->matching_count,
            'denominator' => $alert->denominator,
            'threshold' => $alert->threshold,
            'period' => $alert->window_start->setTimezone($timezone)->format('M j').' - '.$alert->window_end->setTimezone($timezone)->format('M j, Y'),
            'timestamp' => $alert->last_evaluated_at->setTimezone($timezone)->format('M j, Y g:i A'),
            'isRead' => $alert->read_at !== null,
            'evidenceUrl' => route('admin.common-answers', [
                'preset' => 'custom',
                'start' => $alert->window_start->setTimezone($timezone)->toDateString(),
                'end' => $alert->window_end->setTimezone($timezone)->toDateString(),
                'version' => $alert->survey_version,
                'tab' => str_starts_with($alert->rule_key, 'written_') ? 'feedback' : 'selected',
                'question' => str_starts_with($alert->rule_key, 'written_') ? null : 'attendance_reasons',
                'alert' => $alert->id,
            ]),
            'readUrl' => route('admin.alerts.read', $alert),
            'resolveUrl' => route('admin.alerts.resolve', $alert),
        ];
    }

    /**
     * @param  array{label: string, title: string, explanation: string}  $rule
     */
    private function evaluateStructuredRule(
        SurveyVersion $version,
        string $ruleKey,
        array $rule,
        CarbonImmutable $windowStart,
        CarbonImmutable $windowEnd,
    ): void {
        $option = $this->mappedOption($version, $ruleKey, $rule['label']);

        if (! $option) {
            return;
        }

        $responseScope = $this->monitoringResponseScope($version, $windowStart, $windowEnd);
        $denominator = SurveyAnswer::query()
            ->where('survey_question_id', $option->survey_question_id)
            ->whereIn('conversation_room_response_id', (clone $responseScope)->select('conversation_room_responses.id'))
            ->distinct('conversation_room_response_id')
            ->count('conversation_room_response_id');
        $responseIds = SurveyAnswer::query()
            ->where('survey_question_option_id', $option->id)
            ->whereIn('conversation_room_response_id', (clone $responseScope)->select('conversation_room_responses.id'))
            ->distinct()
            ->orderBy('conversation_room_response_id')
            ->pluck('conversation_room_response_id');

        $this->persistEvaluation(
            version: $version,
            ruleKey: $ruleKey,
            title: $rule['title'],
            explanation: $rule['explanation'],
            responseIds: $responseIds,
            denominator: $denominator,
            windowStart: $windowStart,
            windowEnd: $windowEnd,
            sourceOptionStableId: $option->stable_id,
        );
    }

    private function evaluateWrittenTeachingRule(
        SurveyVersion $version,
        CarbonImmutable $windowStart,
        CarbonImmutable $windowEnd,
    ): void {
        $theme = FeedbackTheme::query()->where('key', 'teaching_group_experience')->first();

        if (! $theme) {
            return;
        }

        $responseScope = $this->monitoringResponseScope($version, $windowStart, $windowEnd);
        $questionIds = SurveyQuestion::withTrashed()
            ->where('survey_version_id', $version->id)
            ->where('key', 'one_change')
            ->pluck('id');
        $denominator = SurveyAnswer::query()
            ->whereIn('survey_question_id', $questionIds)
            ->whereIn('conversation_room_response_id', (clone $responseScope)->select('conversation_room_responses.id'))
            ->whereNotNull('answer_value')
            ->where('answer_value', '!=', '')
            ->distinct('conversation_room_response_id')
            ->count('conversation_room_response_id');
        $responseIds = FeedbackThemeAssignment::query()
            ->join('survey_answers', 'survey_answers.id', '=', 'feedback_theme_assignments.survey_answer_id')
            ->where('feedback_theme_assignments.feedback_theme_id', $theme->id)
            ->whereIn('survey_answers.survey_question_id', $questionIds)
            ->whereIn('survey_answers.conversation_room_response_id', (clone $responseScope)->select('conversation_room_responses.id'))
            ->distinct()
            ->orderBy('survey_answers.conversation_room_response_id')
            ->pluck('survey_answers.conversation_room_response_id');

        $this->persistEvaluation(
            version: $version,
            ruleKey: 'written_teaching_concern',
            title: 'Reviewed teaching/group feedback needs attention',
            explanation: 'Administrator-reviewed written feedback was tagged as a teaching/group experience concern. This is a feedback signal requiring administrator review.',
            responseIds: $responseIds,
            denominator: $denominator,
            windowStart: $windowStart,
            windowEnd: $windowEnd,
        );
    }

    /** @param Collection<int, int> $responseIds */
    private function persistEvaluation(
        SurveyVersion $version,
        string $ruleKey,
        string $title,
        string $explanation,
        Collection $responseIds,
        int $denominator,
        CarbonImmutable $windowStart,
        CarbonImmutable $windowEnd,
        ?string $sourceOptionStableId = null,
    ): void {
        $threshold = (int) config('survey.alerts.threshold', 11);
        $matchingCount = $responseIds->count();
        $activeKey = $ruleKey.':'.$version->number;
        $activeAlert = SurveyAlert::query()->where('active_key', $activeKey)->first();

        if ($matchingCount < $threshold) {
            if ($activeAlert) {
                $activeAlert->update([
                    'active_key' => null,
                    'matching_count' => $matchingCount,
                    'denominator' => $denominator,
                    'window_start' => $windowStart,
                    'window_end' => $windowEnd,
                    'last_evaluated_at' => now(),
                    'resolved_at' => now(),
                    'resolution_reason' => 'below_threshold',
                ]);
            }

            return;
        }

        $attributes = [
            'title' => $title,
            'severity' => 'warning',
            'explanation' => $explanation,
            'matching_count' => $matchingCount,
            'denominator' => $denominator,
            'threshold' => $threshold,
            'window_start' => $windowStart,
            'window_end' => $windowEnd,
            'evidence_response_ids' => $responseIds->values()->all(),
            'source_option_stable_id' => $sourceOptionStableId,
            'last_evaluated_at' => now(),
        ];

        if ($activeAlert) {
            $activeAlert->update($attributes);

            return;
        }

        $latestResolved = SurveyAlert::query()
            ->where('rule_key', $ruleKey)
            ->where('survey_version', $version->number)
            ->whereNotNull('resolved_at')
            ->latest('resolved_at')
            ->first();
        $materialIncrease = (int) config('survey.alerts.material_increase', 10);

        if ($latestResolved
            && $latestResolved->resolution_reason !== 'below_threshold'
            && $matchingCount < $latestResolved->matching_count + $materialIncrease) {
            return;
        }

        SurveyAlert::create($attributes + [
            'active_key' => $activeKey,
            'rule_key' => $ruleKey,
            'survey_version' => $version->number,
            'first_triggered_at' => now(),
        ]);
    }

    private function mappedOption(SurveyVersion $version, string $ruleKey, string $label): ?SurveyQuestionOption
    {
        $mapping = DB::table('survey_alert_rule_mappings')
            ->where('rule_key', $ruleKey)
            ->where('survey_version_id', $version->id)
            ->first();

        if ($mapping?->survey_question_option_id) {
            return SurveyQuestionOption::withTrashed()->find($mapping->survey_question_option_id);
        }

        $matches = SurveyQuestionOption::withTrashed()
            ->where('label', $label)
            ->whereHas('question', fn ($query) => $query
                ->withTrashed()
                ->where('survey_version_id', $version->id)
                ->where('key', 'attendance_reasons'))
            ->get();
        $option = $matches->count() === 1 ? $matches->first() : null;
        $values = [
            'survey_question_option_id' => $option?->id,
            'disabled_reason' => $option
                ? null
                : ($matches->isEmpty() ? 'Required survey option is missing.' : 'Required survey option mapping is ambiguous.'),
            'updated_at' => now(),
        ];

        DB::table('survey_alert_rule_mappings')->updateOrInsert(
            ['rule_key' => $ruleKey, 'survey_version_id' => $version->id],
            $values + ['created_at' => now()],
        );

        return $option;
    }

    /** @return Builder<ConversationRoomResponse> */
    private function monitoringResponseScope(
        SurveyVersion $version,
        CarbonImmutable $windowStart,
        CarbonImmutable $windowEnd,
    ): Builder {
        return ConversationRoomResponse::query()
            ->where('survey_version', $version->number)
            ->where('is_demo', false)
            ->where('created_at', '>=', $windowStart)
            ->where('created_at', '<', $windowEnd)
            ->whereHas('answers');
    }

    /** @return array{CarbonImmutable, CarbonImmutable} */
    private function monitoringWindow(): array
    {
        $timezone = (string) config('survey.reporting_timezone', config('app.timezone'));
        $databaseTimezone = (string) config('app.timezone', 'UTC');
        $now = CarbonImmutable::now($timezone);
        $days = (int) config('survey.alerts.monitoring_days', 30);

        return [
            $now->subDays($days - 1)->startOfDay()->setTimezone($databaseTimezone),
            $now->addSecond()->setTimezone($databaseTimezone),
        ];
    }
}
