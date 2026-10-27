<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\SurveySection;
use App\Models\SurveyVersion;
use App\Models\User;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SurveySectionControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SurveyQuestionSeeder::class);
    }

    public function test_administrator_can_add_rename_hide_reorder_and_delete_an_empty_section(): void
    {
        $admin = User::factory()->administrator()->create();
        $createResponse = $this->actingAs($admin)->postJson(route('admin.sections.store'), [
            'title' => 'Learning Goals',
            'description' => 'Questions about student goals.',
            'position' => 2,
            'is_active' => true,
        ]);
        $createResponse->assertCreated();
        $stableId = $createResponse->json('focusSectionStableId');
        $activeSection = SurveySection::query()->where('stable_id', $stableId)->firstOrFail();

        $this->putJson(route('admin.sections.update', $activeSection), [
            'title' => 'Learning Priorities',
            'description' => 'Updated description.',
            'position' => 1,
            'is_active' => false,
        ])->assertOk();

        $draft = SurveyVersion::query()->draft()->sole();
        $draftSection = $draft->sections()->where('stable_id', $stableId)->sole();
        $this->assertSame('Learning Priorities', $draftSection->title);
        $this->assertFalse($draftSection->is_active);
        $this->assertSame(1, $draftSection->sort_order);

        $stableIds = $draft->sections()->orderByDesc('sort_order')->pluck('stable_id')->all();
        $this->putJson(route('admin.section-order.update'), ['section_stable_ids' => $stableIds])
            ->assertOk();
        $this->assertSame($stableIds, $draft->sections()->orderBy('sort_order')->pluck('stable_id')->all());

        $this->deleteJson(route('admin.sections.destroy', $activeSection))->assertOk();
        $this->assertSoftDeleted('survey_sections', ['id' => $draftSection->id]);
    }

    public function test_section_with_questions_cannot_be_deleted(): void
    {
        $section = SurveySection::query()->whereHas('questions')->firstOrFail();

        $this->actingAs(User::factory()->administrator()->create())
            ->deleteJson(route('admin.sections.destroy', $section))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('section');
    }
}
