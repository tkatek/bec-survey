<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use App\Models\User;
use Database\Seeders\FeedbackThemeSeeder;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CommonAnswersControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SurveyQuestionSeeder::class, FeedbackThemeSeeder::class]);
    }

    public function test_common_answers_is_protected_by_administrator_authorization(): void
    {
        $query = $this->filterQuery();

        $this->get(route('admin.common-answers', $query))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())
            ->get(route('admin.common-answers', $query))
            ->assertForbidden();
    }

    public function test_empty_state_keeps_filters_and_tabs_available(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('admin.common-answers', $this->filterQuery()))
            ->assertOk()
            ->assertSee('Common Answers')
            ->assertSee('Selected Answers')
            ->assertSee('Written Feedback Themes')
            ->assertDontSee('name="version"', false)
            ->assertSee('No survey responses yet')
            ->assertSee('Open Student Survey')
            ->assertDontSee('aria-label="Common answer summary"', false);
    }

    public function test_administrator_can_view_data_backed_analytics_and_question_detail(): void
    {
        $this->createAnalyticsResponse();
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('admin.common-answers', $this->filterQuery() + [
                'tab' => 'selected',
                'question' => 'attendance_frequency',
            ]))
            ->assertOk()
            ->assertSee('Attendance Frequency')
            ->assertSee('Attendance Barriers')
            ->assertSee('Preferred Session Format')
            ->assertSee('Preferred Time to Attend')
            ->assertSee('Common Written Feedback Themes')
            ->assertSee('Key Insights')
            ->assertSee('A combination of formats')
            ->assertSee('id="question-breakdown"', false)
            ->assertDontSee('<details class="admin-card group', false)
            ->assertViewHas('analytics', fn (array $analytics): bool => $analytics['overview']['responses'] === 1
                && (float) $analytics['overview']['positiveActivity']['percentage'] === 100.0
                && $analytics['overview']['writtenFeedback']['count'] === 1
                && $analytics['dashboard']['barriers']['answered'] === 1);
    }

    public function test_administrator_can_filter_and_review_written_feedback(): void
    {
        $this->createAnalyticsResponse();
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('admin.common-answers', $this->filterQuery() + ['tab' => 'feedback']))
            ->assertOk()
            ->assertSee('Feedback Themes')
            ->assertSee('Counts include administrator-reviewed comments only.')
            ->assertSee('Please offer more evening sessions.');
    }

    /** @return array<string, string> */
    private function filterQuery(): array
    {
        return [
            'preset' => 'month',
        ];
    }

    private function createAnalyticsResponse(): ConversationRoomResponse
    {
        $questions = SurveyQuestion::query()
            ->with('options')
            ->whereIn('key', [
                'attendance_frequency',
                'attendance_reasons',
                'activity_usefulness',
                'session_format',
                'preferred_session_time',
                'one_change',
            ])
            ->get()
            ->keyBy('key');
        $response = ConversationRoomResponse::factory()->create([
            'survey_version' => SurveyVersion::query()->active()->value('number'),
            'created_at' => now(),
        ]);

        $selections = [
            ['attendance_frequency', 'About once a week', null],
            ['attendance_reasons', 'My schedule has changed.', null],
            ['attendance_reasons', 'The available session times are inconvenient.', null],
            ['activity_usefulness', 'Very useful', null],
            ['session_format', 'A combination of formats', null],
            ['preferred_session_time', 'Evening (5–9 PM)', 'Preferred time'],
        ];

        foreach ($selections as [$questionKey, $optionLabel, $groupName]) {
            $question = $questions->get($questionKey);
            $option = $question->options->firstWhere('label', $optionLabel);
            $response->answers()->create([
                'survey_question_id' => $question->id,
                'survey_question_option_id' => $option->id,
                'answer_value' => $option->label,
                'group_name' => $groupName,
            ]);
        }

        $response->answers()->create([
            'survey_question_id' => $questions->get('one_change')->id,
            'answer_value' => 'Please offer more evening sessions.',
        ]);

        return $response;
    }
}
