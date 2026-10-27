<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\SurveySection;
use App\Models\SurveyVersion;
use App\Models\User;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SurveyQuestionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SurveyQuestionSeeder::class);
    }

    public function test_guest_and_non_administrator_cannot_manage_survey_questions(): void
    {
        $this->get(route('admin.questions.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.questions.index'))
            ->assertForbidden();
    }

    public function test_administrator_can_open_the_database_backed_management_page(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.questions.index'))
            ->assertOk()
            ->assertViewIs('admin.questions.index')
            ->assertSee('Manage Survey Questions')
            ->assertSee('Preview survey');
    }

    public function test_administrator_can_add_all_supported_question_types(): void
    {
        $admin = User::factory()->administrator()->create();
        $section = SurveySection::query()->where('title', 'Your Feedback')->sole();

        $this->actingAs($admin)->postJson(route('admin.questions.store'), $this->questionPayload(
            section: $section,
            prompt: 'Which new topic should we discuss next?',
        ))->assertCreated()->assertJsonPath('state.version.hasDraft', true);

        $this->postJson(route('admin.questions.store'), $this->questionPayload(
            section: $section,
            prompt: 'Which topics interest you?',
            type: SurveyQuestion::TYPE_MULTIPLE_CHOICE,
            maxSelections: 2,
        ))->assertCreated();

        $textPayload = $this->questionPayload(
            section: $section,
            prompt: 'What should we improve?',
            type: SurveyQuestion::TYPE_LONG_TEXT,
        );
        $textPayload['options'] = [];
        $this->postJson(route('admin.questions.store'), $textPayload)->assertCreated();

        $draft = SurveyVersion::query()->draft()->sole();
        $this->assertCount(3, $draft->questions()->whereIn('prompt', [
            'Which new topic should we discuss next?',
            'Which topics interest you?',
            'What should we improve?',
        ])->get());
        $this->assertCount(2, $draft->questions()->where('prompt', 'Which topics interest you?')->sole()->options);
    }

    public function test_choice_question_requires_two_distinct_options(): void
    {
        $section = SurveySection::query()->firstOrFail();
        $payload = $this->questionPayload($section, 'Which topic?');
        $payload['options'] = [
            ['stable_id' => null, 'label' => 'Travel', 'icon' => 'globe', 'tone' => 'blue', 'group_name' => null],
        ];

        $this->actingAs(User::factory()->administrator()->create())
            ->postJson(route('admin.questions.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('options');

        $this->assertDatabaseMissing('survey_questions', ['prompt' => 'Which topic?']);
    }

    public function test_edit_duplicate_and_delete_only_change_the_draft_and_preserve_historical_answers(): void
    {
        $admin = User::factory()->administrator()->create();
        $question = SurveyQuestion::query()->with(['options', 'surveySection'])->where('key', 'attendance_frequency')->sole();
        $option = $question->options->first();
        $response = ConversationRoomResponse::factory()->create(['survey_version' => $question->version->number]);
        $answer = $response->answers()->create([
            'survey_question_id' => $question->id,
            'survey_question_option_id' => $option->id,
            'answer_value' => $option->label,
        ]);
        $payload = $this->questionPayload($question->surveySection, 'Updated attendance question');
        $payload['is_active'] = false;
        $payload['options'] = $question->options->map(fn ($item): array => [
            'stable_id' => $item->stable_id,
            'label' => $item->label,
            'icon' => $item->icon,
            'tone' => $item->tone,
            'group_name' => $item->group_name,
        ])->all();

        $this->actingAs($admin)
            ->putJson(route('admin.questions.update', $question), $payload)
            ->assertOk();
        $this->postJson(route('admin.questions.duplicate', $question))->assertCreated();
        $this->deleteJson(route('admin.questions.destroy', $question))->assertOk();

        $draft = SurveyVersion::query()->draft()->sole();
        $this->assertSame(1, $draft->questions()->where('prompt', 'Updated attendance question')->count());
        $this->assertDatabaseHas('survey_questions', ['id' => $question->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('survey_answers', ['id' => $answer->id]);
    }

    public function test_question_order_can_move_between_sections_and_accessible_move_persists(): void
    {
        $admin = User::factory()->administrator()->create();
        $firstSection = SurveySection::query()->orderBy('sort_order')->firstOrFail();
        $secondSection = SurveySection::query()->orderBy('sort_order')->skip(1)->firstOrFail();
        $movedQuestion = $firstSection->questions()->orderByDesc('sort_order')->firstOrFail();
        $groups = SurveySection::query()->orderBy('sort_order')->get()->map(function (SurveySection $section) use ($movedQuestion, $secondSection): array {
            $ids = $section->questions()->orderBy('sort_order')->pluck('stable_id')->reject(
                fn (string $stableId): bool => $stableId === $movedQuestion->stable_id,
            );

            if ($section->is($secondSection)) {
                $ids->prepend($movedQuestion->stable_id);
            }

            return [
                'section_stable_id' => $section->stable_id,
                'question_stable_ids' => $ids->values()->all(),
            ];
        })->all();

        $this->actingAs($admin)
            ->putJson(route('admin.question-order.update'), ['groups' => $groups])
            ->assertOk();

        $draft = SurveyVersion::query()->draft()->sole();
        $draftSecondSection = $draft->sections()->where('stable_id', $secondSection->stable_id)->sole();
        $this->assertSame($movedQuestion->stable_id, $draftSecondSection->questions()->orderBy('sort_order')->first()->stable_id);

        $questionToMove = $draftSecondSection->questions()->orderBy('sort_order')->skip(1)->firstOrFail();
        $this->patchJson(route('admin.questions.move', $questionToMove), ['direction' => 'up'])->assertOk();
        $this->assertSame($questionToMove->stable_id, $draftSecondSection->questions()->orderBy('sort_order')->first()->stable_id);
    }

    /** @return array<string, mixed> */
    private function questionPayload(
        SurveySection $section,
        string $prompt,
        string $type = SurveyQuestion::TYPE_SINGLE_CHOICE,
        ?int $maxSelections = null,
    ): array {
        return [
            'section_stable_id' => $section->stable_id,
            'position' => 1,
            'prompt' => $prompt,
            'help_text' => 'Choose an answer.',
            'type' => $type,
            'is_required' => true,
            'is_active' => true,
            'max_selections' => $maxSelections,
            'options' => [
                ['stable_id' => null, 'label' => 'Travel', 'icon' => 'globe', 'tone' => 'blue', 'group_name' => null],
                ['stable_id' => null, 'label' => 'Work', 'icon' => 'message-circle', 'tone' => 'green', 'group_name' => null],
            ],
        ];
    }
}
