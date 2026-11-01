<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use App\Models\User;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
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
            ->assertDontSee('id="survey-version"', false)
            ->assertViewHas('analytics', fn (array $analytics): bool => $analytics['overview']['responses'] === 1
                && (float) $analytics['overview']['positiveActivity']['percentage'] === 100.0);
    }

    public function test_dashboard_defaults_to_the_last_seven_days_across_month_boundaries(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $this->travelTo(Carbon::parse('2026-11-01 12:00:00'));
        $question = SurveyQuestion::query()->with('options')->where('key', 'activity_usefulness')->sole();
        $option = $question->options->first();
        $this->answeredResponse($question, $option, now()->subDay());
        $this->answeredResponse($question, $option, now()->subDays(9));

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('analytics', fn (array $analytics): bool => $analytics['meta']['filter']['preset'] === 'last7'
                && $analytics['overview']['responses'] === 1);
    }

    public function test_month_preset_only_counts_responses_from_the_current_calendar_month(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $this->travelTo(Carbon::parse('2026-11-01 12:00:00'));
        $question = SurveyQuestion::query()->with('options')->where('key', 'activity_usefulness')->sole();
        $option = $question->options->first();
        $this->answeredResponse($question, $option, now()->subDay());
        $this->answeredResponse($question, $option, now()->subHour());

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('dashboard', ['preset' => 'month']))
            ->assertOk()
            ->assertViewHas('analytics', fn (array $analytics): bool => $analytics['meta']['filter']['preset'] === 'month'
                && $analytics['overview']['responses'] === 1);
    }

    private function answeredResponse(
        SurveyQuestion $question,
        SurveyQuestionOption $option,
        Carbon $createdAt,
        bool $isDemo = false,
    ): ConversationRoomResponse {
        $response = ConversationRoomResponse::factory()->create([
            'survey_version' => $question->version()->value('number'),
            'is_demo' => $isDemo,
            'created_at' => $createdAt,
        ]);
        $response->answers()->create([
            'survey_question_id' => $question->id,
            'survey_question_option_id' => $option->id,
            'answer_value' => $option->label,
        ]);

        return $response;
    }
}
