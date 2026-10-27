<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\User;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_the_administrator_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_non_administrator_cannot_view_the_dashboard(): void
    {
        $this->seed(SurveyQuestionSeeder::class);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_administrator_can_view_analytics_from_the_shared_payload(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $question = SurveyQuestion::query()->with(['options', 'version'])->where('key', 'activity_usefulness')->sole();
        $option = $question->options->firstWhere('label', 'Very useful');
        $response = ConversationRoomResponse::factory()->create([
            'survey_version' => $question->version->number,
            'created_at' => now(),
        ]);
        $response->answers()->create([
            'survey_question_id' => $question->id,
            'survey_question_option_id' => $option->id,
            'answer_value' => $option->label,
        ]);

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Conversation Room Feedback Survey')
            ->assertSee('Responses Over Time')
            ->assertViewHas('analytics', fn (array $analytics): bool => $analytics['overview']['responses'] === 1
                && (float) $analytics['overview']['positiveActivity']['percentage'] === 100.0);
    }
}
