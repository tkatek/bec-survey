<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use App\Models\User;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PublishedSurveyControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SurveyQuestionSeeder::class);
    }

    public function test_draft_changes_are_previewable_but_not_public_until_published_and_history_is_preserved(): void
    {
        $admin = User::factory()->administrator()->create();
        $activeVersion = SurveyVersion::query()->active()->sole();
        $activeQuestion = $activeVersion->questions()->with(['options', 'surveySection'])->where('key', 'attendance_frequency')->sole();
        $option = $activeQuestion->options->first();
        $historicalResponse = ConversationRoomResponse::factory()->create(['survey_version' => $activeVersion->number]);
        $historicalAnswer = $historicalResponse->answers()->create([
            'survey_question_id' => $activeQuestion->id,
            'survey_question_option_id' => $option->id,
            'answer_value' => $option->label,
        ]);

        $this->actingAs($admin)->postJson(route('admin.questions.store'), [
            'section_stable_id' => $activeQuestion->surveySection->stable_id,
            'position' => 1,
            'prompt' => 'Draft-only question',
            'help_text' => 'Visible after publishing.',
            'type' => SurveyQuestion::TYPE_LONG_TEXT,
            'is_required' => false,
            'is_active' => true,
            'max_selections' => null,
            'options' => [],
        ])->assertCreated();

        $this->get(route('conversation-room.create'))->assertDontSee('Draft-only question');
        $this->get(route('admin.survey-preview'))->assertOk()->assertSee('Draft-only question');
        $this->assertDatabaseCount('conversation_room_responses', 1);

        $this->postJson(route('admin.published-survey.store'))
            ->assertOk()
            ->assertJsonPath('state.version.hasDraft', false);

        $newActiveVersion = SurveyVersion::query()->active()->sole();
        $this->assertGreaterThan($activeVersion->number, $newActiveVersion->number);
        $this->assertSame(SurveyVersion::STATUS_ARCHIVED, $activeVersion->fresh()->status);
        $this->get(route('conversation-room.create'))->assertSee('Draft-only question');
        $this->assertDatabaseHas('survey_answers', ['id' => $historicalAnswer->id]);
        $this->assertDatabaseHas('survey_questions', ['id' => $activeQuestion->id]);
    }

    public function test_publishing_without_draft_changes_is_rejected(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->postJson(route('admin.published-survey.store'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('survey');
    }
}
