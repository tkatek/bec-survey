<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ConversationRoomResponse;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ConversationRoomSurveyControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_home_page_displays_the_public_student_survey(): void
    {
        $response = $this->get(route('conversation-room.create'));

        $response
            ->assertOk()
            ->assertViewIs('conversation-room.create')
            ->assertSee('Conversation Room Feedback Survey')
            ->assertSee('How often have you attended the Conversation Room in the past month?');
    }

    public function test_conversation_room_path_redirects_to_the_student_survey(): void
    {
        $response = $this->get('/conversation-room');

        $response->assertRedirect(route('conversation-room.create'));
    }

    public function test_student_survey_contains_the_final_feedback_question(): void
    {
        $response = $this->get(route('conversation-room.create'));

        $response
            ->assertOk()
            ->assertSee('If you could change one thing about the Conversation Room, what would it be?');
    }

    public function test_valid_anonymous_submission_creates_a_response_and_selected_options(): void
    {
        $payload = $this->validSurveyPayload();
        $payload['survey_version'] = 999;

        $response = $this->post(route('conversation-room.store'), $payload);

        $response->assertRedirect(route('conversation-room.thank-you'));

        $surveyResponse = ConversationRoomResponse::query()
            ->with('choices')
            ->sole();

        $this->assertSame(1, $surveyResponse->survey_version);
        $this->assertSame('About once a week', $surveyResponse->attendance_frequency);
        $this->assertSame('More evening sessions and smaller groups.', $surveyResponse->one_change);
        $this->assertCount(6, $surveyResponse->choices);
        $this->assertSame(
            ['Monday', 'Saturday'],
            $surveyResponse->choices
                ->where('question_key', 'preferred_days')
                ->pluck('option_value')
                ->sort()
                ->values()
                ->all(),
        );
    }

    public function test_empty_submission_is_rejected_without_creating_a_response(): void
    {
        $response = $this->from(route('conversation-room.create'))
            ->post(route('conversation-room.store'));

        $response
            ->assertRedirect(route('conversation-room.create'))
            ->assertSessionHasErrors([
                'attendance_frequency',
                'attendance_change',
                'updated_room_use',
                'ease_of_use',
                'activity_usefulness',
                'speaking_comfort',
                'session_format',
                'encouragement_changes',
                'preferred_session_time',
                'time_zone',
                'preferred_days',
                'session_length',
                'attendance_likelihood',
            ]);

        $this->assertDatabaseEmpty('conversation_room_responses');
        $this->assertDatabaseEmpty('conversation_room_response_choices');
    }

    public function test_more_than_three_encouragement_changes_is_rejected(): void
    {
        $payload = $this->validSurveyPayload();
        $payload['encouragement_changes'] = [
            'More real-life conversation topics',
            'Smaller groups and more speaking time',
            'More personalised feedback from teachers',
            'More flexible session times',
        ];

        $response = $this->post(route('conversation-room.store'), $payload);

        $response->assertSessionHasErrors([
            'encouragement_changes' => 'Choose up to three changes that would encourage attendance.',
        ]);
        $this->assertDatabaseEmpty('conversation_room_responses');
    }

    public function test_unknown_answer_option_is_rejected(): void
    {
        $payload = $this->validSurveyPayload();
        $payload['attendance_frequency'] = "' OR 1=1 --";

        $response = $this->post(route('conversation-room.store'), $payload);

        $response->assertSessionHasErrors(['attendance_frequency']);
        $this->assertDatabaseEmpty('conversation_room_responses');
    }

    /**
     * @return array<string, mixed>
     */
    private function validSurveyPayload(): array
    {
        return [
            'attendance_frequency' => 'About once a week',
            'attendance_change' => 'I attend more often now.',
            'attendance_reasons' => [
                'My schedule has changed.',
                "I'm nervous about speaking or making mistakes.",
            ],
            'updated_room_use' => 'Yes, several times.',
            'ease_of_use' => 'Very easy',
            'activity_usefulness' => 'Very useful',
            'speaking_comfort' => 'Somewhat comfortable',
            'session_format' => 'Small-group conversations',
            'encouragement_changes' => [
                'More real-life conversation topics',
                'Smaller groups and more speaking time',
            ],
            'preferred_session_time' => 'Evening (5–9 PM)',
            'time_zone' => 'Eastern',
            'preferred_days' => ['Monday', 'Saturday'],
            'session_length' => '45 minutes',
            'attendance_likelihood' => 'Very likely',
            'one_change' => 'More evening sessions and smaller groups.',
        ];
    }
}
