<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\SurveyVersion;
use App\Models\User;
use Database\Seeders\FeedbackThemeSeeder;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CommonAnswersControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SurveyQuestionSeeder::class, FeedbackThemeSeeder::class]);
    }

    public function test_common_answers_is_protected_by_administrator_authorization(): void
    {
        $query = $this->filterQuery();

        $this->get(route('admin.common-answers', $query))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())
            ->get(route('admin.common-answers', $query))
            ->assertForbidden();
    }

    public function test_administrator_can_filter_selected_answers_and_feedback_tabs(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->get(route('admin.common-answers', $this->filterQuery() + [
                'tab' => 'selected',
                'question' => 'attendance_frequency',
                'sort' => 'count',
            ]))
            ->assertOk()
            ->assertSee('Common Answers')
            ->assertSee('How often have you attended');

        $this->actingAs($admin)
            ->get(route('admin.common-answers', $this->filterQuery() + ['tab' => 'feedback']))
            ->assertOk()
            ->assertSee('Feedback Themes')
            ->assertSee('Counts include administrator-reviewed comments only.');
    }

    /** @return array<string, int|string> */
    private function filterQuery(): array
    {
        return [
            'preset' => 'month',
            'version' => SurveyVersion::query()->active()->value('number'),
        ];
    }
}
