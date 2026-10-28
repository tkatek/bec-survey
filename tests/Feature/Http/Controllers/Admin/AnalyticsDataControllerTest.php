<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use App\Models\SurveyVersion;
use App\Models\User;
use App\Services\SurveyAnalyticsService;
use App\Support\AnalyticsFilter;
use App\SurveyDraftManager;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AnalyticsDataControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SurveyQuestionSeeder::class);
        Carbon::setTestNow('2026-10-27 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_only_administrators_can_query_analytics(): void
    {
        $query = $this->filterQuery();

        $this->getJson(route('admin.analytics.show', $query))->assertUnauthorized();
        $this->actingAs(User::factory()->create())
            ->getJson(route('admin.analytics.show', $query))
            ->assertForbidden();
    }

    public function test_cached_analytics_are_reused_until_invalidated(): void
    {
        $analytics = app(SurveyAnalyticsService::class);
        $filter = AnalyticsFilter::fromValidated(['preset' => 'today']);
        $firstPayload = $analytics->cachedBuild($filter);

        Carbon::setTestNow(now()->addSecond());

        $cachedPayload = $analytics->cachedBuild(AnalyticsFilter::fromValidated(['preset' => 'today']));

        $this->assertSame($firstPayload['meta']['generatedAt'], $cachedPayload['meta']['generatedAt']);

        $analytics->invalidateCache();

        $refreshedPayload = $analytics->cachedBuild(AnalyticsFilter::fromValidated(['preset' => 'today']));

        $this->assertNotSame($firstPayload['meta']['generatedAt'], $refreshedPayload['meta']['generatedAt']);
    }

    public function test_today_uses_normalized_completed_non_demo_responses_only(): void
    {
        $question = SurveyQuestion::query()->with('options')->where('key', 'attendance_frequency')->sole();
        $option = $question->options->first();

        $this->answeredResponse($question, $option, now()->subHour());
        $this->answeredResponse($question, $option, now()->subDay());
        $this->answeredResponse($question, $option, now()->subHour(), true);
        ConversationRoomResponse::factory()->create([
            'survey_version' => SurveyVersion::query()->active()->value('number'),
            'created_at' => now()->subHour(),
        ]);

        $this->actingAs(User::factory()->administrator()->create())
            ->getJson(route('admin.analytics.show', $this->filterQuery()))
            ->assertOk()
            ->assertJsonPath('analytics.overview.responses', 1)
            ->assertJsonPath('analytics.dashboard.attendance.answered', 1)
            ->assertJsonPath('analytics.dashboard.attendance.distributions.0.options.0.count', 1)
            ->assertJsonStructure(['snapshotToken', 'analytics' => ['meta', 'overview', 'series', 'questions']]);
    }

    public function test_last_seven_days_and_custom_range_boundaries_are_inclusive_local_days(): void
    {
        $question = SurveyQuestion::query()->with('options')->where('key', 'attendance_frequency')->sole();
        $option = $question->options->first();

        $this->answeredResponse($question, $option, now()->subDays(6)->startOfDay()->addHour());
        $this->answeredResponse($question, $option, now()->subDays(7)->endOfDay());
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->getJson(route('admin.analytics.show', $this->filterQuery('last7')))
            ->assertOk()
            ->assertJsonPath('analytics.overview.responses', 1);

        $this->actingAs($admin)
            ->getJson(route('admin.analytics.show', $this->filterQuery('custom') + [
                'start' => '2026-10-21',
                'end' => '2026-10-21',
            ]))
            ->assertOk()
            ->assertJsonPath('analytics.overview.responses', 1);
    }

    public function test_invalid_and_future_ranges_are_rejected(): void
    {
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->getJson(route('admin.analytics.show', $this->filterQuery('custom') + [
                'start' => '2026-10-27',
                'end' => '2026-10-20',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('end');

        $this->actingAs($admin)
            ->getJson(route('admin.analytics.show', $this->filterQuery('custom') + [
                'start' => '2026-10-28',
                'end' => '2026-10-28',
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('end');
    }

    public function test_multiple_choice_percentages_use_distinct_answering_responses(): void
    {
        $question = SurveyQuestion::query()->with('options')->where('key', 'attendance_reasons')->sole();
        $firstOption = $question->options->first();
        $secondOption = $question->options->get(1);
        $firstResponse = ConversationRoomResponse::factory()->create([
            'survey_version' => SurveyVersion::query()->active()->value('number'),
            'created_at' => now()->subHour(),
        ]);
        $firstResponse->answers()->createMany([
            ['survey_question_id' => $question->id, 'survey_question_option_id' => $firstOption->id, 'answer_value' => $firstOption->label],
            ['survey_question_id' => $question->id, 'survey_question_option_id' => $secondOption->id, 'answer_value' => $secondOption->label],
        ]);
        $this->answeredResponse($question, $firstOption, now()->subHour());

        $this->actingAs(User::factory()->administrator()->create())
            ->getJson(route('admin.analytics.show', $this->filterQuery()))
            ->assertOk()
            ->assertJsonPath('analytics.dashboard.barriers.answered', 2)
            ->assertJsonPath('analytics.dashboard.barriers.distributions.0.options.0.count', 2)
            ->assertJsonPath('analytics.dashboard.barriers.distributions.0.options.0.percentage', 100)
            ->assertJsonPath('analytics.dashboard.barriers.distributions.0.options.1.count', 1)
            ->assertJsonPath('analytics.dashboard.barriers.distributions.0.options.1.percentage', 50);
    }

    public function test_answers_from_every_survey_version_are_combined_by_stable_ids(): void
    {
        $archivedQuestion = SurveyQuestion::query()->with(['options', 'version'])->where('key', 'attendance_frequency')->sole();
        $archivedOption = $archivedQuestion->options->first();
        $this->answeredResponse($archivedQuestion, $archivedOption, now()->subHour());
        $admin = User::factory()->administrator()->create();
        $surveys = app(SurveyDraftManager::class);
        $surveys->draft($admin);
        $surveys->publish($admin);
        $activeQuestion = SurveyQuestion::query()
            ->with('options')
            ->where('stable_id', $archivedQuestion->stable_id)
            ->whereHas('version', fn ($query) => $query->active())
            ->sole();
        $activeQuestion->load('version');
        $activeOption = $activeQuestion->options->firstWhere('stable_id', $archivedOption->stable_id);
        $this->answeredResponse($activeQuestion, $activeOption, now()->subMinutes(30));

        $this->actingAs($admin)
            ->getJson(route('admin.analytics.show', $this->filterQuery() + ['version' => 999999]))
            ->assertOk()
            ->assertJsonPath('analytics.meta.scope', 'All completed student submissions')
            ->assertJsonPath('analytics.overview.responses', 2)
            ->assertJsonPath('analytics.dashboard.attendance.answered', 2)
            ->assertJsonPath('analytics.dashboard.attendance.distributions.0.options.0.count', 2);
    }

    /** @return array<string, string> */
    private function filterQuery(string $preset = 'today'): array
    {
        return [
            'preset' => $preset,
        ];
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
