<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;

class ConversationRoomSurveyControllerTest extends TestCase
{
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
}
