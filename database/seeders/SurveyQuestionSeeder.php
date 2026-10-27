<?php

namespace Database\Seeders;

use App\Models\SurveyQuestion;
use App\Models\SurveySection;
use App\Models\SurveyVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SurveyQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $version = SurveyVersion::query()->active()->firstOrCreate(
            [],
            [
                'number' => 2,
                'status' => SurveyVersion::STATUS_ACTIVE,
                'published_at' => now(),
            ],
        );
        $sections = collect($this->sections())
            ->mapWithKeys(function (array $sectionData, int $index) use ($version): array {
                $section = SurveySection::query()->firstOrCreate(
                    [
                        'survey_version_id' => $version->id,
                        'title' => $sectionData['title'],
                    ],
                    [
                        'stable_id' => (string) Str::uuid(),
                        'description' => $sectionData['description'],
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ],
                );

                return [$sectionData['legacy_title'] => $section];
            });
        $sectionQuestionPositions = [];

        foreach ($this->questions() as $questionData) {
            $options = $questionData['options'];
            unset($questionData['options']);
            $legacySection = $questionData['section'];
            $section = $sections->get($legacySection);
            $sectionQuestionPositions[$legacySection] = ($sectionQuestionPositions[$legacySection] ?? 0) + 1;
            $questionData['survey_version_id'] = $version->id;
            $questionData['survey_section_id'] = $section->id;
            $questionData['stable_id'] = (string) Str::uuid();
            $questionData['section'] = $section->title;
            $questionData['sort_order'] = $sectionQuestionPositions[$legacySection];

            $question = SurveyQuestion::query()
                ->withTrashed()
                ->firstOrCreate([
                    'survey_version_id' => $version->id,
                    'key' => $questionData['key'],
                ], $questionData);

            if ($question->options()->withTrashed()->exists()) {
                continue;
            }

            $question->options()->createMany(
                collect($options)
                    ->map(fn (array $option, int $index): array => [
                        'stable_id' => (string) Str::uuid(),
                        'label' => $option[0],
                        'icon' => $option[1],
                        'tone' => $option[2],
                        'group_name' => $option[3] ?? null,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ])
                    ->all(),
            );
        }
    }

    /**
     * @return array<int, array{legacy_title: string, title: string, description: string}>
     */
    private function sections(): array
    {
        return [
            [
                'legacy_title' => 'Attendance and participation',
                'title' => 'Attendance & Participation',
                'description' => 'Questions about attendance and participation in the Conversation Room.',
            ],
            [
                'legacy_title' => 'The updated Conversation Room',
                'title' => 'The Updated Conversation Room',
                'description' => 'Questions about the updated Conversation Room experience.',
            ],
            [
                'legacy_title' => 'Your preferences',
                'title' => 'Your Preferences',
                'description' => 'Questions about preferred times, days and improvements.',
            ],
            [
                'legacy_title' => 'Your feedback',
                'title' => 'Your Feedback',
                'description' => 'Final questions about future attendance and open feedback.',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function questions(): array
    {
        return [
            $this->question('attendance_frequency', 'Attendance and participation', 'How often have you attended the Conversation Room in the past month?', 'Choose the answer that best describes the past four weeks.', SurveyQuestion::TYPE_SINGLE_CHOICE, 1, [
                ['Never', 'ban', 'red'],
                ['Once or twice', 'calendar', 'blue'],
                ['About once a week', 'calendar-days', 'green'],
                ['Several times a week', 'calendar', 'orange'],
                ['Almost every day', 'calendar-star', 'purple'],
            ]),
            $this->question('attendance_change', 'Attendance and participation', 'Compared with before the Conversation Room update, how has your attendance changed?', 'Choose one answer.', SurveyQuestion::TYPE_SINGLE_CHOICE, 2, [
                ['I attend more often now.', 'trending-up', 'green'],
                ['My attendance is about the same.', 'minus', 'blue'],
                ['I attend less often now.', 'trending-down', 'orange'],
                ['I have stopped attending.', 'stop-circle', 'red'],
                ["I joined after the update / I didn't attend before.", 'user-plus', 'purple'],
            ]),
            $this->question('attendance_reasons', 'Attendance and participation', "What are the main reasons you attend less often or don't attend?", 'Select all that apply, or continue if this question does not apply to you.', SurveyQuestion::TYPE_MULTIPLE_CHOICE, 3, [
                ['My schedule has changed.', 'calendar', 'red'],
                ['The available session times are inconvenient.', 'clock', 'blue'],
                ["I didn't know when sessions were available.", 'info', 'orange'],
                ["I haven't been motivated to join.", 'lightbulb', 'purple'],
                ["I'm nervous about speaking or making mistakes.", 'message-circle', 'green'],
                ["The topics or activities don't interest me.", 'file-text', 'blue'],
                ['The sessions are too easy or too difficult.', 'bar-chart', 'green'],
                ["I don't get enough opportunities to speak.", 'users', 'purple'],
                ["The group atmosphere or teaching style doesn't suit me.", 'home', 'red'],
                ['I have experienced technical problems.', 'wifi', 'blue'],
                ['I prefer other ways of practising English.', 'book-open', 'orange'],
                ['Other personal reasons.', 'ellipsis', 'neutral'],
            ], false, 12),
            $this->question('updated_room_use', 'The updated Conversation Room', 'Have you tried the updated Conversation Room?', 'Choose one answer.', SurveyQuestion::TYPE_SINGLE_CHOICE, 4, [
                ['Yes, several times.', 'check-circle', 'green'],
                ['Yes, once.', 'check', 'blue'],
                ['No, not yet.', 'clock', 'orange'],
                ["I wasn't aware it had been updated.", 'info', 'purple'],
            ]),
            $this->question('ease_of_use', 'The updated Conversation Room', 'How easy is it to use the updated Conversation Room?', 'Choose one answer.', SurveyQuestion::TYPE_SINGLE_CHOICE, 5, [
                ['Very easy', 'sparkles', 'green'],
                ['Somewhat easy', 'smile', 'blue'],
                ['Somewhat difficult', 'gauge', 'orange'],
                ['Very difficult', 'alert-circle', 'red'],
                ['Not sure', 'help-circle', 'neutral'],
            ]),
            $this->question('activity_usefulness', 'The updated Conversation Room', 'How useful are the activities for improving your spoken English?', 'Choose one answer.', SurveyQuestion::TYPE_SINGLE_CHOICE, 6, [
                ['Very useful', 'star', 'green'],
                ['Somewhat useful', 'thumbs-up', 'blue'],
                ['Not very useful', 'minus', 'orange'],
                ['Not useful at all', 'thumbs-down', 'red'],
                ['Not sure yet', 'help-circle', 'neutral'],
            ]),
            $this->question('speaking_comfort', 'The updated Conversation Room', 'How comfortable do you feel speaking during a Conversation Room session?', 'Choose one answer.', SurveyQuestion::TYPE_SINGLE_CHOICE, 7, [
                ['Very comfortable', 'microphone', 'green'],
                ['Somewhat comfortable', 'message-circle', 'blue'],
                ['Somewhat uncomfortable', 'message-circle', 'orange'],
                ['Very uncomfortable', 'microphone-off', 'red'],
                ['It depends on the group or activity.', 'users', 'purple'],
            ]),
            $this->question('session_format', 'The updated Conversation Room', 'Which session format would you prefer?', 'Choose one answer.', SurveyQuestion::TYPE_SINGLE_CHOICE, 8, [
                ['Small-group conversations', 'users', 'blue'],
                ['Pair speaking activities', 'user-round', 'green'],
                ['Teacher-led discussion with feedback', 'message-circle', 'purple'],
                ['Interactive speaking games and challenges', 'gamepad', 'orange'],
                ['A combination of formats', 'layout-grid', 'blue'],
            ]),
            $this->question('encouragement_changes', 'Your preferences', 'What changes would encourage you to attend more often?', 'Select up to three options.', SurveyQuestion::TYPE_MULTIPLE_CHOICE, 9, [
                ['More real-life conversation topics', 'message-circle', 'blue'],
                ['Smaller groups and more speaking time', 'users', 'green'],
                ['Games, role-plays and interactive challenges', 'gamepad', 'orange'],
                ['More personalised feedback from teachers', 'microphone', 'purple'],
                ['Topics organised by English level', 'layers', 'blue'],
                ['Easier navigation and fewer technical problems', 'compass', 'green'],
                ['More flexible session times', 'clock', 'orange'],
                ['Clear learning goals and progress tracking', 'target', 'purple'],
                ['More opportunities to choose topics', 'hand-pointer', 'blue'],
                ['Friendly sessions for shy learners', 'shield', 'green'],
            ], true, 3),
            $this->question('preferred_session_time', 'Your preferences', 'When would you prefer to attend live speaking sessions?', 'Choose a time of day and your time zone.', SurveyQuestion::TYPE_GROUPED_CHOICE, 10, [
                ['Morning (6 AM–12 PM)', 'sun', 'orange', 'Preferred time'],
                ['Afternoon (12–5 PM)', 'sunset', 'blue', 'Preferred time'],
                ['Evening (5–9 PM)', 'moon', 'purple', 'Preferred time'],
                ['Late evening (after 9 PM)', 'star', 'purple', 'Preferred time'],
                ['My availability changes.', 'refresh', 'green', 'Preferred time'],
                ['Eastern', 'globe', 'blue', 'Time zone'],
                ['Central', 'globe', 'green', 'Time zone'],
                ['Mountain', 'globe', 'orange', 'Time zone'],
                ['Pacific', 'globe', 'purple', 'Time zone'],
                ['Other', 'globe', 'neutral', 'Time zone'],
            ]),
            $this->question('preferred_days', 'Your preferences', 'Which days usually work best for you?', 'Select all that apply.', SurveyQuestion::TYPE_MULTIPLE_CHOICE, 11, [
                ['Monday', 'calendar-days', 'blue'],
                ['Tuesday', 'calendar-days', 'green'],
                ['Wednesday', 'calendar-days', 'orange'],
                ['Thursday', 'calendar-days', 'purple'],
                ['Friday', 'calendar-days', 'blue'],
                ['Saturday', 'calendar-days', 'green'],
                ['Sunday', 'calendar-days', 'orange'],
            ], true, 7),
            $this->question('session_length', 'Your preferences', 'What is your ideal session length?', 'Choose one answer.', SurveyQuestion::TYPE_SINGLE_CHOICE, 12, [
                ['15 minutes', 'timer', 'green'],
                ['30 minutes', 'timer', 'blue'],
                ['45 minutes', 'timer', 'orange'],
                ['60 minutes', 'timer', 'purple'],
            ]),
            $this->question('attendance_likelihood', 'Your feedback', 'How likely are you to attend the Conversation Room in the next two weeks?', 'Choose one answer.', SurveyQuestion::TYPE_SINGLE_CHOICE, 13, [
                ['Very likely', 'star', 'green'],
                ['Somewhat likely', 'thumbs-up', 'blue'],
                ['Not sure', 'help-circle', 'neutral'],
                ['Somewhat unlikely', 'trending-down', 'orange'],
                ['Very unlikely', 'thumbs-down', 'red'],
            ]),
            $this->question('one_change', 'Your feedback', 'If you could change one thing about the Conversation Room, what would it be?', 'This final answer is optional. Share as much or as little as you like.', SurveyQuestion::TYPE_LONG_TEXT, 14, [], false),
        ];
    }

    /**
     * @param  array<int, array<int, string>>  $options
     * @return array<string, mixed>
     */
    private function question(
        string $key,
        string $section,
        string $prompt,
        string $helpText,
        string $type,
        int $sortOrder,
        array $options,
        bool $isRequired = true,
        ?int $maxSelections = null,
    ): array {
        return [
            'key' => $key,
            'section' => $section,
            'prompt' => $prompt,
            'help_text' => $helpText,
            'type' => $type,
            'is_required' => $isRequired,
            'max_selections' => $maxSelections,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'options' => $options,
        ];
    }
}
