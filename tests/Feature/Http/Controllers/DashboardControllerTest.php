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

    public function test_authenticated_administrator_can_view_empty_analytics(): void
    {
        $this->seed(SurveyQuestionSeeder::class);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Conversation Room Feedback Survey')
            ->assertSee('Your analytics are ready');
    }

    public function test_dashboard_aggregates_normalized_answers_for_the_selected_period(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $question = SurveyQuestion::query()->with('options')->where('key', 'activity_usefulness')->sole();
        $option = $question->options->firstWhere('label', 'Very useful');
        $response = ConversationRoomResponse::factory()->create(['created_at' => now()]);
        $response->answers()->create([
            'survey_question_id' => $question->id,
            'survey_question_option_id' => $option->id,
            'answer_value' => $option->label,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard', ['period' => 30]))
            ->assertOk()
            ->assertViewHas('periodResponses', 1)
            ->assertViewHas('positiveUsefulnessPercentage', 100);
    }
}
