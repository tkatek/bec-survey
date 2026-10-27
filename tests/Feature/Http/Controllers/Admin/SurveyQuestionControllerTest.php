<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\User;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SurveyQuestionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_manage_survey_questions(): void
    {
        $this->get(route('admin.questions.index'))->assertRedirect(route('login'));
    }

    public function test_administrator_can_add_a_question_with_options(): void
    {
        $payload = [
            'section' => 'Your feedback',
            'prompt' => 'Which new topic should we discuss next?',
            'help_text' => 'Choose one answer.',
            'type' => SurveyQuestion::TYPE_SINGLE_CHOICE,
            'is_required' => true,
            'is_active' => true,
            'max_selections' => null,
            'options' => [
                ['label' => 'Travel', 'icon' => 'globe', 'tone' => 'blue', 'group_name' => null],
                ['label' => 'Work', 'icon' => 'message-circle', 'tone' => 'green', 'group_name' => null],
            ],
        ];

        $this->actingAs(User::factory()->create())
            ->post(route('admin.questions.store'), $payload)
            ->assertRedirect(route('admin.questions.index'));

        $question = SurveyQuestion::query()->where('prompt', $payload['prompt'])->sole();
        $this->assertCount(2, $question->options);
        $this->assertSame('Travel', $question->options->first()->label);
    }

    public function test_question_requires_at_least_two_distinct_options(): void
    {
        $payload = [
            'section' => 'Your feedback',
            'prompt' => 'Which topic?',
            'help_text' => 'Choose one answer.',
            'type' => SurveyQuestion::TYPE_SINGLE_CHOICE,
            'is_required' => true,
            'is_active' => true,
            'options' => [
                ['label' => 'Travel', 'icon' => 'globe', 'tone' => 'blue', 'group_name' => null],
            ],
        ];

        $this->actingAs(User::factory()->create())
            ->post(route('admin.questions.store'), $payload)
            ->assertSessionHasErrors(['options']);

        $this->assertDatabaseMissing('survey_questions', ['prompt' => 'Which topic?']);
    }

    public function test_removing_a_question_preserves_its_existing_answers(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $question = SurveyQuestion::query()->with('options')->where('key', 'attendance_frequency')->sole();
        $option = $question->options->first();
        $response = ConversationRoomResponse::factory()->create();
        $answer = $response->answers()->create([
            'survey_question_id' => $question->id,
            'survey_question_option_id' => $option->id,
            'answer_value' => $option->label,
        ]);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.questions.destroy', $question))
            ->assertRedirect(route('admin.questions.index'));

        $this->assertSoftDeleted($question);
        $this->assertDatabaseHas('survey_answers', ['id' => $answer->id]);
    }
}
