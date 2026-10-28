<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConversationRoomResponseRequest;
use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use App\Services\SurveyAlertService;
use App\Services\SurveyAnalyticsService;
use App\SurveyDraftManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;

class ConversationRoomSurveyController extends Controller
{
    /**
     * Display the Conversation Room survey.
     */
    public function create(Request $request, SurveyDraftManager $surveys): View
    {
        $viewData = $surveys->publicSurveyViewData();
        $errors = $request->session()->get('errors');
        $errorBag = $errors instanceof ViewErrorBag ? $errors->getBag('default') : null;
        $firstErrorKey = $errorBag?->keys()[0] ?? null;
        $initialStep = 0;

        if (is_string($firstErrorKey) && preg_match('/^answers\.(\d+)/', $firstErrorKey, $matches) === 1) {
            $questionIndex = $viewData['questions']->search(
                fn (SurveyQuestion $question): bool => $question->id === (int) $matches[1],
            );
            $initialStep = is_int($questionIndex) ? $questionIndex : 0;
        }

        $viewData['surveyConfiguration'] = array_replace(
            $viewData['surveyConfiguration'],
            [
                'initialAnswers' => $request->old('answers', []),
                'initialError' => $errorBag?->first() ?? '',
                'initialStep' => $initialStep,
            ],
        );

        return view('conversation-room.create', $viewData);
    }

    /**
     * Store an anonymous Conversation Room survey response.
     */
    public function store(
        StoreConversationRoomResponseRequest $request,
        SurveyDraftManager $surveys,
        SurveyAlertService $alerts,
        SurveyAnalyticsService $analytics,
    ): RedirectResponse {
        $answers = $request->validated('answers');
        $version = SurveyVersion::query()
            ->where('number', $request->integer('survey_version'))
            ->where('status', '!=', SurveyVersion::STATUS_DRAFT)
            ->sole();
        $questions = $surveys->surveyViewData($version)['questions'];

        DB::transaction(function () use ($answers, $questions, $version): void {
            $response = ConversationRoomResponse::create([
                'survey_version' => $version->number,
            ]);
            $labelsByQuestion = [];
            $groupedLabels = [];

            foreach ($questions as $question) {
                $submittedAnswer = $answers[$question->id] ?? null;

                if ($question->type === SurveyQuestion::TYPE_LONG_TEXT) {
                    if (filled($submittedAnswer)) {
                        $response->answers()->create([
                            'survey_question_id' => $question->id,
                            'answer_value' => $submittedAnswer,
                        ]);
                    }

                    $labelsByQuestion[$question->key] = $submittedAnswer;

                    continue;
                }

                $selectedOptionIds = collect(Arr::wrap($submittedAnswer))->map(fn ($id): int => (int) $id);
                $selectedOptions = $question->options->whereIn('id', $selectedOptionIds);

                foreach ($selectedOptions as $option) {
                    $response->answers()->create([
                        'survey_question_id' => $question->id,
                        'survey_question_option_id' => $option->id,
                        'answer_value' => $option->label,
                        'group_name' => $option->group_name,
                    ]);

                    if ($option->group_name) {
                        $groupedLabels[$option->group_name] = $option->label;
                    }
                }

                $labelsByQuestion[$question->key] = $selectedOptions->pluck('label')->values()->all();
            }

            $response->update([
                'attendance_frequency' => Arr::first($labelsByQuestion['attendance_frequency'] ?? []),
                'attendance_change' => Arr::first($labelsByQuestion['attendance_change'] ?? []),
                'updated_room_use' => Arr::first($labelsByQuestion['updated_room_use'] ?? []),
                'ease_of_use' => Arr::first($labelsByQuestion['ease_of_use'] ?? []),
                'activity_usefulness' => Arr::first($labelsByQuestion['activity_usefulness'] ?? []),
                'speaking_comfort' => Arr::first($labelsByQuestion['speaking_comfort'] ?? []),
                'session_format' => Arr::first($labelsByQuestion['session_format'] ?? []),
                'preferred_session_time' => $groupedLabels['Preferred time'] ?? null,
                'time_zone' => $groupedLabels['Time zone'] ?? null,
                'session_length' => Arr::first($labelsByQuestion['session_length'] ?? []),
                'attendance_likelihood' => Arr::first($labelsByQuestion['attendance_likelihood'] ?? []),
                'one_change' => $labelsByQuestion['one_change'] ?? null,
            ]);

            $multiSelectAnswers = collect([
                'attendance_reasons' => $labelsByQuestion['attendance_reasons'] ?? [],
                'encouragement_changes' => $labelsByQuestion['encouragement_changes'] ?? [],
                'preferred_days' => $labelsByQuestion['preferred_days'] ?? [],
            ])->flatMap(fn (array $answers, string $questionKey): array => collect($answers)
                ->map(fn (string $answer): array => [
                    'question_key' => $questionKey,
                    'option_value' => $answer,
                ])
                ->all())
                ->all();

            $response->choices()->createMany($multiSelectAnswers);
        });

        $analytics->invalidateCache();

        defer(function () use ($alerts): void {
            try {
                $alerts->evaluate();
            } catch (\Throwable $exception) {
                report($exception);
            }
        });

        return redirect()->route('conversation-room.thank-you');
    }

    /**
     * Display the survey completion page.
     */
    public function thankYou(): View
    {
        return view('conversation-room.thank-you');
    }
}
