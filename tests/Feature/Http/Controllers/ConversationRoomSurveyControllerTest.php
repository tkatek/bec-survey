<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ConversationRoomSurveyControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_home_page_displays_the_database_backed_student_survey(): void
    {
        $this->seed(SurveyQuestionSeeder::class);

        $this->get(route('conversation-room.create'))
            ->assertOk()
            ->assertViewIs('conversation-room.create')
            ->assertSee('Conversation Room Feedback Survey')
            ->assertSee('How often have you attended the Conversation Room in the past month?');
    }

    public function test_conversation_room_path_redirects_to_the_student_survey(): void
    {
        $this->get('/conversation-room')->assertRedirect(route('conversation-room.create'));
    }

    public function test_valid_anonymous_submission_creates_a_response_and_normalized_answers(): void
    {
        $this->seed(SurveyQuestionSeeder::class);

        $response = $this->post(route('conversation-room.store'), $this->validSurveyPayload());

        $response->assertRedirect(route('conversation-room.thank-you'));

        $surveyResponse = ConversationRoomResponse::query()->with('answers')->sole();
        $attendanceQuestion = SurveyQuestion::query()->where('key', 'attendance_frequency')->sole();

        $this->assertSame(2, $surveyResponse->survey_version);
        $this->assertSame('Never', $surveyResponse->attendance_frequency);
        $this->assertSame('More evening sessions and smaller groups.', $surveyResponse->one_change);
        $this->assertTrue($surveyResponse->answers->contains(
            fn ($answer): bool => $answer->survey_question_id === $attendanceQuestion->id
                && $answer->answer_value === 'Never',
        ));
        $this->assertDatabaseCount('conversation_room_responses', 1);
        $this->assertGreaterThan(14, $surveyResponse->answers->count());
    }

    public function test_empty_submission_is_rejected_without_creating_a_response(): void
    {
        $this->seed(SurveyQuestionSeeder::class);

        $this->from(route('conversation-room.create'))
            ->post(route('conversation-room.store'), [
                'survey_version' => SurveyVersion::query()->active()->value('number'),
            ])
            ->assertRedirect(route('conversation-room.create'))
            ->assertSessionHasErrors(['answers']);

        $this->assertDatabaseEmpty('conversation_room_responses');
        $this->assertDatabaseEmpty('survey_answers');
    }

    public function test_more_than_the_allowed_number_of_choices_is_rejected(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $payload = $this->validSurveyPayload();
        $question = SurveyQuestion::query()->with('options')->where('key', 'encouragement_changes')->sole();
        $payload['answers'][$question->id] = $question->options->take(4)->pluck('id')->all();

        $this->post(route('conversation-room.store'), $payload)
            ->assertSessionHasErrors(['answers.'.$question->id]);

        $this->assertDatabaseEmpty('conversation_room_responses');
    }

    public function test_unknown_answer_option_is_rejected(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $payload = $this->validSurveyPayload();
        $question = SurveyQuestion::query()->where('key', 'attendance_frequency')->sole();
        $payload['answers'][$question->id] = 999999;

        $this->post(route('conversation-room.store'), $payload)
            ->assertSessionHasErrors(['answers.'.$question->id]);

        $this->assertDatabaseEmpty('conversation_room_responses');
    }

    public function test_grouped_question_requires_one_answer_from_each_group(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $payload = $this->validSurveyPayload();
        $question = SurveyQuestion::query()->with('options')->where('key', 'preferred_session_time')->sole();
        $payload['answers'][$question->id] = [$question->options->first()->id];

        $this->post(route('conversation-room.store'), $payload)
            ->assertSessionHasErrors(['answers.'.$question->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validSurveyPayload(): array
    {
        $questions = SurveyQuestion::query()->with('options')->active()->ordered()->get();
        $answers = [];

        foreach ($questions as $question) {
            $answers[$question->id] = match ($question->key) {
                'attendance_reasons' => [],
                'encouragement_changes', 'preferred_days' => $question->options->take(2)->pluck('id')->all(),
                'preferred_session_time' => $question->options
                    ->groupBy('group_name')
                    ->map(fn ($options) => $options->first()->id)
                    ->all(),
                'one_change' => 'More evening sessions and smaller groups.',
                default => $question->options->first()->id,
            };
        }

        return [
            'survey_version' => SurveyVersion::query()->active()->value('number'),
            'answers' => $answers,
        ];
    }
}
