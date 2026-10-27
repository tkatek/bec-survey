<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConversationRoomResponseRequest;
use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConversationRoomSurveyController extends Controller
{
    /**
     * Display the Conversation Room survey.
     */
    public function create(): View
    {
        $questions = SurveyQuestion::query()
            ->active()
            ->with(['options' => fn ($query) => $query->where('is_active', true)])
            ->ordered()
            ->get();
        $sections = $questions->pluck('section')->unique()->values();
        $surveyConfiguration = [
            'totalSteps' => $questions->count(),
            'questions' => $questions->values()->map(fn (SurveyQuestion $question): array => [
                'id' => $question->id,
                'required' => $question->is_required,
                'type' => $question->type,
                'maxSelections' => $question->max_selections,
                'groups' => $question->options->pluck('group_name')->filter()->unique()->values()->all(),
            ])->all(),
            'sections' => $sections->map(function (string $section) use ($questions): array {
                $indexes = $questions->values()
                    ->map(fn (SurveyQuestion $question, int $index): ?int => $question->section === $section ? $index : null)
                    ->filter(fn (?int $index): bool => $index !== null)
                    ->values();

                return [
                    'name' => $section,
                    'start' => $indexes->first(),
                    'end' => $indexes->last(),
                ];
            })->all(),
        ];

        return view('conversation-room.create', compact('questions', 'sections', 'surveyConfiguration'));
    }

    /**
     * Store an anonymous Conversation Room survey response.
     */
    public function store(StoreConversationRoomResponseRequest $request): RedirectResponse
    {
        $answers = $request->validated('answers');
        $questions = SurveyQuestion::query()
            ->active()
            ->with(['options' => fn ($query) => $query->where('is_active', true)])
            ->ordered()
            ->get();

        DB::transaction(function () use ($answers, $questions): void {
            $response = ConversationRoomResponse::create([
                'survey_version' => 2,
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
