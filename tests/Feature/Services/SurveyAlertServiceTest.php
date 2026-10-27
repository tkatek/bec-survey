<?php

namespace Tests\Feature\Services;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyAlert;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use App\Models\SurveyVersion;
use App\Models\User;
use App\Services\SurveyAlertService;
use Database\Seeders\FeedbackThemeSeeder;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SurveyAlertServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SurveyQuestionSeeder::class, FeedbackThemeSeeder::class]);
    }

    public function test_more_than_ten_distinct_responses_trigger_one_persistent_alert_episode(): void
    {
        $question = SurveyQuestion::query()->with('options')->where('key', 'attendance_reasons')->sole();
        $option = $question->options->firstWhere('label', 'The available session times are inconvenient.');
        $service = app(SurveyAlertService::class);

        foreach (range(1, 10) as $index) {
            $this->selectedAnswer($question, $option);
        }

        $service->evaluate();
        $this->assertDatabaseMissing('survey_alerts', ['rule_key' => 'scheduling']);

        $this->selectedAnswer($question, $option);
        $service->evaluate();
        $service->evaluate();

        $this->assertDatabaseCount('survey_alerts', 1);
        $alert = SurveyAlert::query()->sole();
        $this->assertSame(11, $alert->matching_count);
        $this->assertCount(11, $alert->evidence_response_ids);

        $admin = User::factory()->administrator()->create();
        $this->actingAs($admin)
            ->put(route('admin.alerts.read', $alert))
            ->assertRedirect();
        $this->assertNotNull($alert->fresh()->read_at);

        $this->actingAs($admin)
            ->put(route('admin.alerts.resolve', $alert))
            ->assertRedirect();
        $this->assertNotNull($alert->fresh()->resolved_at);
    }

    public function test_demo_responses_cannot_trigger_production_alerts(): void
    {
        $question = SurveyQuestion::query()->with('options')->where('key', 'attendance_reasons')->sole();
        $option = $question->options->firstWhere('label', 'I have experienced technical problems.');

        foreach (range(1, 11) as $index) {
            $this->selectedAnswer($question, $option, true);
        }

        app(SurveyAlertService::class)->evaluate();

        $this->assertDatabaseMissing('survey_alerts', ['rule_key' => 'technical_access']);
    }

    private function selectedAnswer(SurveyQuestion $question, SurveyQuestionOption $option, bool $isDemo = false): void
    {
        $response = ConversationRoomResponse::factory()->create([
            'survey_version' => SurveyVersion::query()->active()->value('number'),
            'is_demo' => $isDemo,
            'created_at' => now(),
        ]);
        $response->answers()->create([
            'survey_question_id' => $question->id,
            'survey_question_option_id' => $option->id,
            'answer_value' => $option->label,
        ]);
    }
}
