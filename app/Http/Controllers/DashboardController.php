<?php

namespace App\Http\Controllers;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $period = (int) $request->integer('period', 30);

        if (! in_array($period, [7, 30, 90], true)) {
            $period = 30;
        }

        $periodStart = now()->subDays($period - 1)->startOfDay();
        $previousPeriodStart = $periodStart->copy()->subDays($period);
        $previousPeriodEnd = $periodStart->copy()->subSecond();
        $periodResponses = ConversationRoomResponse::query()->where('created_at', '>=', $periodStart)->count();
        $previousPeriodResponses = ConversationRoomResponse::query()
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->count();
        $questions = SurveyQuestion::query()
            ->with(['options' => fn ($query) => $query->where('is_active', true)])
            ->ordered()
            ->get()
            ->keyBy('key');
        $chartKeys = [
            'attendance_frequency',
            'attendance_change',
            'attendance_reasons',
            'activity_usefulness',
            'session_format',
            'preferred_session_time',
            'attendance_likelihood',
        ];
        $questionIds = $questions
            ->filter(fn (SurveyQuestion $question, string $key): bool => in_array($key, $chartKeys, true))
            ->pluck('id');
        $answerCounts = SurveyAnswer::query()
            ->select([
                'survey_question_id',
                'answer_value',
                'group_name',
                DB::raw('COUNT(DISTINCT conversation_room_response_id) as aggregate'),
            ])
            ->whereIn('survey_question_id', $questionIds)
            ->whereHas('response', fn ($query) => $query->where('created_at', '>=', $periodStart))
            ->groupBy('survey_question_id', 'answer_value', 'group_name')
            ->get()
            ->groupBy('survey_question_id');
        $buildChart = function (string $key, ?string $groupName = null, ?int $limit = null) use ($questions, $answerCounts, $periodResponses): array {
            $question = $questions->get($key);

            if (! $question) {
                return [];
            }

            $counts = $answerCounts->get($question->id, collect())
                ->when($groupName, fn (Collection $rows) => $rows->where('group_name', $groupName))
                ->pluck('aggregate', 'answer_value');
            $options = $question->options
                ->when($groupName, fn (Collection $options) => $options->where('group_name', $groupName))
                ->map(fn ($option): array => [
                    'label' => $option->label,
                    'tone' => $option->tone,
                    'count' => (int) ($counts[$option->label] ?? 0),
                    'percentage' => $periodResponses > 0
                        ? (int) round(((int) ($counts[$option->label] ?? 0) / $periodResponses) * 100)
                        : 0,
                ])
                ->sortByDesc('count')
                ->values();

            return ($limit ? $options->take($limit) : $options)->all();
        };
        $activityCounts = collect($buildChart('activity_usefulness'))->keyBy('label');
        $likelihoodCounts = collect($buildChart('attendance_likelihood'))->keyBy('label');
        $positiveUsefulness = (int) (($activityCounts['Very useful']['count'] ?? 0) + ($activityCounts['Somewhat useful']['count'] ?? 0));
        $likelyToAttend = (int) (($likelihoodCounts['Very likely']['count'] ?? 0) + ($likelihoodCounts['Somewhat likely']['count'] ?? 0));
        $seriesStart = now()->subDays(min($period, 14) - 1)->startOfDay();
        $seriesCounts = ConversationRoomResponse::query()
            ->selectRaw('DATE(created_at) as response_date, COUNT(*) as aggregate')
            ->where('created_at', '>=', $seriesStart)
            ->groupBy('response_date')
            ->pluck('aggregate', 'response_date');
        $responseSeries = collect(range(0, min($period, 14) - 1))
            ->map(function (int $offset) use ($seriesStart, $seriesCounts): array {
                $date = $seriesStart->copy()->addDays($offset);

                return [
                    'label' => $date->format('M j'),
                    'value' => (int) ($seriesCounts[$date->toDateString()] ?? 0),
                ];
            });
        $feedbackQuestion = $questions->get('one_change');
        $latestFeedback = $feedbackQuestion
            ? SurveyAnswer::query()
                ->where('survey_question_id', $feedbackQuestion->id)
                ->whereNotNull('answer_value')
                ->where('answer_value', '!=', '')
                ->whereHas('response', fn ($query) => $query->where('created_at', '>=', $periodStart))
                ->latest()
                ->limit(3)
                ->get()
            : collect();

        return view('dashboard', [
            'period' => $period,
            'periodResponses' => $periodResponses,
            'responseTrend' => $this->percentageChange($periodResponses, $previousPeriodResponses),
            'activeQuestions' => $questions->where('is_active', true)->count(),
            'positiveUsefulnessPercentage' => $periodResponses > 0 ? (int) round(($positiveUsefulness / $periodResponses) * 100) : 0,
            'likelyToAttendPercentage' => $periodResponses > 0 ? (int) round(($likelyToAttend / $periodResponses) * 100) : 0,
            'responseSeries' => $responseSeries,
            'maxSeriesValue' => max(1, (int) $responseSeries->max('value')),
            'charts' => [
                'attendance' => $buildChart('attendance_frequency'),
                'attendanceChange' => $buildChart('attendance_change'),
                'barriers' => $buildChart('attendance_reasons', null, 6),
                'format' => $buildChart('session_format'),
                'preferredTime' => $buildChart('preferred_session_time', 'Preferred time'),
            ],
            'latestFeedback' => $latestFeedback,
        ]);
    }

    private function percentageChange(int $current, int $previous): int
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }
}
