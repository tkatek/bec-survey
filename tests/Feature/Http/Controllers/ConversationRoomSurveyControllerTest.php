<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationRoomSurveyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_the_survey(): void
    {
        $response = $this->get(route('conversation-room.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_the_survey(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('conversation-room.create'));

        $response
            ->assertOk()
            ->assertViewIs('conversation-room.create');
    }

    public function test_authenticated_user_can_view_the_thank_you_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('conversation-room.thank-you'));

        $response
            ->assertOk()
            ->assertViewIs('conversation-room.thank-you');
    }
}
