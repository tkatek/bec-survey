<?php

namespace Database\Seeders;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DemoSurveyResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(SurveyQuestionSeeder::class);

        $questions = SurveyQuestion::query()
            ->active()
            ->with(['options' => fn ($query) => $query->where('is_active', true)])
            ->ordered()
            ->get();

        DB::transaction(function () use ($questions): void {
            ConversationRoomResponse::query()->where('is_demo', true)->delete();

            foreach ($this->dayOffsets() as $responseIndex => $dayOffset) {
                $createdAt = now()
                    ->subDays($dayOffset)
                    ->setTime(9 + ($responseIndex % 8), ($responseIndex * 7) % 60);
                $answerRows = [];
                $legacyValues = [];

                foreach ($questions as $question) {
                    $selectedAnswers = $this->answersForQuestion($question, $responseIndex);

                    foreach ($selectedAnswers as $selectedAnswer) {
                        $answerRows[] = [
                            'survey_question_id' => $question->id,
                            'survey_question_option_id' => $selectedAnswer['option']?->id,
                            'answer_value' => $selectedAnswer['value'],
                            'group_name' => $selectedAnswer['group'],
                        ];
                    }

                    $this->addLegacyValue($legacyValues, $question->key, $selectedAnswers);
                }

                $response = ConversationRoomResponse::query()->create([
                    'survey_version' => 2,
                    'is_demo' => true,
                    ...$legacyValues,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $response->answers()->createMany($answerRows);
            }
        });
    }

    /**
     * @return array<int, int>
     */
    private function dayOffsets(): array
    {
        return [13, 13, 12, 11, 10, 10, 9, 8, 8, 8, 7, 6, 6, 5, 4, 4, 3, 2, 1, 0];
    }

    /**
     * @return array<int, array{option: SurveyQuestionOption|null, value: string, group: string|null}>
     */
    private function answersForQuestion(SurveyQuestion $question, int $responseIndex): array
    {
        if ($question->type === SurveyQuestion::TYPE_LONG_TEXT) {
            return [[
                'option' => null,
                'value' => $this->feedbackMessages()[$responseIndex],
                'group' => null,
            ]];
        }

        if ($question->type === SurveyQuestion::TYPE_GROUPED_CHOICE) {
            return $question->options
                ->groupBy('group_name')
                ->values()
                ->map(function (Collection $options, int $groupIndex) use ($responseIndex): array {
                    /** @var SurveyQuestionOption $option */
                    $option = $options->values()->get(($responseIndex + $groupIndex) % $options->count());

                    return $this->optionAnswer($option);
                })
                ->all();
        }

        if ($question->type === SurveyQuestion::TYPE_MULTIPLE_CHOICE) {
            $optionIndexes = $this->multipleChoicePatterns()[$question->key][$responseIndex];

            return collect($optionIndexes)
                ->map(function (int $optionIndex) use ($question): array {
                    /** @var SurveyQuestionOption $option */
                    $option = $question->options->values()->get($optionIndex % $question->options->count());

                    return $this->optionAnswer($option);
                })
                ->all();
        }

        $patterns = $this->singleChoicePatterns();
        $pattern = $patterns[$question->key] ?? range(0, max(0, $question->options->count() - 1));
        /** @var SurveyQuestionOption $option */
        $option = $question->options->values()->get($pattern[$responseIndex % count($pattern)]);

        return [$this->optionAnswer($option)];
    }

    /**
     * @return array{option: SurveyQuestionOption, value: string, group: string|null}
     */
    private function optionAnswer(SurveyQuestionOption $option): array
    {
        return [
            'option' => $option,
            'value' => $option->label,
            'group' => $option->group_name,
        ];
    }

    /**
     * @param  array<string, string>  $legacyValues
     * @param  array<int, array{option: SurveyQuestionOption|null, value: string, group: string|null}>  $answers
     */
    private function addLegacyValue(array &$legacyValues, string $questionKey, array $answers): void
    {
        $legacyKeys = [
            'attendance_frequency',
            'attendance_change',
            'updated_room_use',
            'ease_of_use',
            'activity_usefulness',
            'speaking_comfort',
            'session_format',
            'session_length',
            'attendance_likelihood',
            'one_change',
        ];

        if (in_array($questionKey, $legacyKeys, true)) {
            $legacyValues[$questionKey] = $answers[0]['value'] ?? null;
        }

        if ($questionKey === 'preferred_session_time') {
            foreach ($answers as $answer) {
                if ($answer['group'] === 'Preferred time') {
                    $legacyValues['preferred_session_time'] = $answer['value'];
                }

                if ($answer['group'] === 'Time zone') {
                    $legacyValues['time_zone'] = $answer['value'];
                }
            }
        }
    }

    /**
     * @return array<string, array<int, int>>
     */
    private function singleChoicePatterns(): array
    {
        return [
            'attendance_frequency' => [0, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 3, 4, 4, 4, 1, 2, 3, 4, 2],
            'attendance_change' => [0, 0, 1, 0, 1, 2, 0, 1, 0, 4, 1, 2, 0, 1, 3, 0, 1, 2, 4, 4],
            'updated_room_use' => [0, 0, 1, 0, 2, 0, 1, 0, 2, 3, 0, 1, 0, 2, 1, 0, 0, 2, 1, 3],
            'ease_of_use' => [0, 1, 1, 0, 1, 2, 0, 1, 1, 3, 0, 1, 0, 2, 1, 0, 1, 2, 4, 0],
            'activity_usefulness' => [0, 0, 1, 0, 1, 1, 0, 1, 0, 2, 1, 1, 0, 2, 1, 0, 1, 0, 3, 4],
            'speaking_comfort' => [0, 1, 1, 0, 1, 2, 1, 0, 2, 4, 1, 1, 0, 2, 1, 0, 2, 1, 3, 4],
            'session_format' => [0, 3, 4, 1, 3, 0, 4, 3, 2, 1, 3, 0, 4, 3, 1, 2, 0, 3, 4, 3],
            'session_length' => [1, 2, 1, 1, 2, 1, 2, 1, 3, 1, 2, 0, 1, 2, 1, 3, 2, 1, 0, 2],
            'attendance_likelihood' => [0, 0, 1, 0, 1, 1, 0, 1, 0, 2, 1, 1, 0, 2, 1, 0, 3, 1, 3, 4],
        ];
    }

    /**
     * @return array<string, array<int, array<int, int>>>
     */
    private function multipleChoicePatterns(): array
    {
        $attendanceReasons = [
            [0, 1], [1], [0, 2], [3], [0, 4], [1, 5], [2], [0, 6], [1, 8], [4],
            [0, 7], [1, 9], [2, 3], [0], [5], [1, 4], [0, 10], [7], [3, 11], [0, 1, 2],
        ];
        $encouragementChanges = [
            [0, 1, 6], [1, 3], [0, 2, 8], [3, 7], [1, 4, 6], [0, 5], [2, 3, 9], [1, 6], [0, 7], [3, 4],
            [0, 1, 2], [5, 6], [3, 7, 8], [0, 4], [1, 9], [2, 6, 7], [0, 3], [1, 4, 8], [5, 6], [0, 1, 7],
        ];
        $preferredDays = [
            [0, 2], [1, 3], [2, 4], [3, 5], [4, 6], [0, 5], [1, 6], [0, 2, 4], [1, 3, 5], [2, 4, 6],
            [0, 1], [2, 3], [4, 5], [5, 6], [0, 6], [1, 4], [2, 5], [3, 6], [0, 3, 5], [1, 4, 6],
        ];

        return [
            'attendance_reasons' => $attendanceReasons,
            'encouragement_changes' => $encouragementChanges,
            'preferred_days' => $preferredDays,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function feedbackMessages(): array
    {
        return [
            'Offer more evening sessions for students who work during the day.',
            'Keep the groups small so everyone has enough time to speak.',
            'Add more real-life role plays for work, travel and daily conversations.',
            'Give a short vocabulary list before each speaking session.',
            'Let students choose the topic before the session begins.',
            'More personal feedback from the teacher would help my confidence.',
            'Create a regular schedule that is easy to find each week.',
            'Add beginner-friendly rooms where making mistakes feels safe.',
            'Include more pronunciation practice inside the activities.',
            'A progress summary after several sessions would be motivating.',
            'Use more games and challenges to make speaking feel natural.',
            'Offer a weekend session for students with busy weekdays.',
            'Make the session links and joining instructions easier to find.',
            'Group students by level for some of the more difficult activities.',
            'Spend more time on open conversation instead of fixed exercises.',
            'Share the next session topic earlier so students can prepare.',
            'Add more pair activities before speaking to the whole group.',
            'Keep the friendly atmosphere and add more chances to practise.',
            'Provide a quick correction summary at the end of the session.',
            'More flexible times would make it easier for me to attend regularly.',
        ];
    }
}
