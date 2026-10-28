<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyQuestion;
use App\Models\SurveySection;
use App\Models\SurveyVersion;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ConversationRoomSurveyControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_home_page_displays_the_database_backed_student_survey(): void
    {
        $this->seed(SurveyQuestionSeeder::class);

        $this->get(route('conversation-room.create'))
            ->assertOk()
            ->assertViewIs('conversation-room.create')
            ->assertSee('Conversation Room Feedback Survey')
            ->assertSee('How often have you attended the Conversation Room in the past month?');
    }

    public function test_public_survey_view_is_cached_for_the_active_version(): void
    {
        config(['cache.stores.array.serialize' => true]);
        Cache::purge('array');

        $this->seed(SurveyQuestionSeeder::class);
        $version = SurveyVersion::query()->active()->sole();
        $question = SurveyQuestion::query()->where('key', 'attendance_frequency')->sole();
        $originalPrompt = $question->prompt;

        $this->get(route('conversation-room.create'))->assertSee($originalPrompt);

        $this->assertTrue(Cache::has("survey:public-view:v2:{$version->id}:{$version->number}"));

        $question->update(['prompt' => 'A prompt that is not published yet']);

        $this->get(route('conversation-room.create'))
            ->assertSee($originalPrompt)
            ->assertDontSee('A prompt that is not published yet');
    }

    public function test_public_survey_uses_a_cookie_session_without_storing_visitor_metadata(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        config([
            'session.driver' => 'database',
            'survey.public_session_driver' => 'cookie',
        ]);

        $this->get(route('conversation-room.create'))
            ->assertOk()
            ->assertCookie(config('survey.public_session_cookie'));

        $this->assertDatabaseEmpty('sessions');
        $this->assertSame('database', config('session.driver'));

        $this->get(route('login'))->assertOk();

        $this->assertDatabaseCount('sessions', 1);
    }

    public function test_conversation_room_path_redirects_to_the_student_survey(): void
    {
        $this->get('/conversation-room')->assertRedirect(route('conversation-room.create'));
    }

    #[DataProvider('sectionCounts')]
    public function test_student_stepper_renders_each_active_section_count_in_order(int $sectionCount): void
    {
        $version = $this->createActiveSurveyVersion(120);

        foreach (array_reverse(range(1, $sectionCount)) as $position) {
            $this->createSectionWithQuestion($version, "Section {$position}", $position);
        }

        $expectedTitles = collect(range(1, $sectionCount))->map(fn (int $position): string => "Section {$position}")->all();
        $expectedIndexes = range(0, $sectionCount - 1);

        $this->get(route('conversation-room.create'))
            ->assertOk()
            ->assertViewHas('sections', fn (Collection $sections): bool => $sections->all() === $expectedTitles)
            ->assertViewHas('surveyConfiguration', function (array $configuration) use ($expectedIndexes, $expectedTitles): bool {
                return array_column($configuration['sections'], 'name') === $expectedTitles
                    && array_column($configuration['sections'], 'start') === $expectedIndexes
                    && array_column($configuration['sections'], 'end') === $expectedIndexes;
            });
    }

    public function test_student_stepper_excludes_hidden_sections_and_sections_without_visible_questions(): void
    {
        $version = $this->createActiveSurveyVersion(121);
        $this->createSectionWithQuestion($version, 'Hidden section', 1, sectionActive: false);
        $this->createSectionWithQuestion($version, 'No visible questions', 2, questionActive: false);
        $this->createSectionWithQuestion($version, 'First visible section', 3);
        $this->createSectionWithQuestion($version, 'Second visible section', 4);

        $this->get(route('conversation-room.create'))
            ->assertOk()
            ->assertViewHas(
                'sections',
                fn (Collection $sections): bool => $sections->all() === ['First visible section', 'Second visible section'],
            )
            ->assertViewHas('surveyConfiguration', function (array $configuration): bool {
                return array_column($configuration['sections'], 'name') === ['First visible section', 'Second visible section']
                    && array_column($configuration['sections'], 'start') === [0, 1]
                    && array_column($configuration['sections'], 'end') === [0, 1];
            });
    }

    public function test_valid_anonymous_submission_creates_a_response_and_normalized_answers(): void
    {
        $this->seed(SurveyQuestionSeeder::class);

        $response = $this->post(route('conversation-room.store'), $this->validSurveyPayload());

        $response->assertRedirect(route('conversation-room.thank-you'));

        $surveyResponse = ConversationRoomResponse::query()->with('answers')->sole();
        $attendanceQuestion = SurveyQuestion::query()->where('key', 'attendance_frequency')->sole();

        $this->assertSame(2, $surveyResponse->survey_version);
        $this->assertSame('Never', $surveyResponse->attendance_frequency);
        $this->assertSame('More evening sessions and smaller groups.', $surveyResponse->one_change);
        $this->assertTrue($surveyResponse->answers->contains(
            fn ($answer): bool => $answer->survey_question_id === $attendanceQuestion->id
                && $answer->answer_value === 'Never',
        ));
        $this->assertDatabaseCount('conversation_room_responses', 1);
        $this->assertGreaterThan(14, $surveyResponse->answers->count());
    }

    public function test_empty_submission_is_rejected_without_creating_a_response(): void
    {
        $this->seed(SurveyQuestionSeeder::class);

        $this->from(route('conversation-room.create'))
            ->post(route('conversation-room.store'), [
                'survey_version' => SurveyVersion::query()->active()->value('number'),
            ])
            ->assertRedirect(route('conversation-room.create'))
            ->assertSessionHasErrors(['answers']);

        $this->assertDatabaseEmpty('conversation_room_responses');
        $this->assertDatabaseEmpty('survey_answers');
    }

    public function test_more_than_the_allowed_number_of_choices_is_rejected(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $payload = $this->validSurveyPayload();
        $question = SurveyQuestion::query()->with('options')->where('key', 'encouragement_changes')->sole();
        $payload['answers'][$question->id] = $question->options->take(4)->pluck('id')->all();

        $this->post(route('conversation-room.store'), $payload)
            ->assertSessionHasErrors(['answers.'.$question->id]);

        $this->assertDatabaseEmpty('conversation_room_responses');
    }

    public function test_validation_failure_restores_answers_and_opens_the_invalid_question(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $payload = $this->validSurveyPayload();
        $question = SurveyQuestion::query()->with('options')->where('key', 'encouragement_changes')->sole();
        $payload['answers'][$question->id] = $question->options->take(4)->pluck('id')->all();

        $this->from(route('conversation-room.create'))
            ->post(route('conversation-room.store'), $payload)
            ->assertRedirect(route('conversation-room.create'));

        $expectedStep = SurveyQuestion::query()->active()->ordered()->get()->search(
            fn (SurveyQuestion $surveyQuestion): bool => $surveyQuestion->is($question),
        );

        $this->get(route('conversation-room.create'))
            ->assertOk()
            ->assertViewHas('surveyConfiguration', function (array $configuration) use ($expectedStep, $payload): bool {
                return $configuration['initialStep'] === $expectedStep
                    && $configuration['initialAnswers'] === $payload['answers']
                    && $configuration['initialError'] === 'You selected more answers than this question allows.';
            });
    }

    public function test_unknown_answer_option_is_rejected(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $payload = $this->validSurveyPayload();
        $question = SurveyQuestion::query()->where('key', 'attendance_frequency')->sole();
        $payload['answers'][$question->id] = 999999;

        $this->post(route('conversation-room.store'), $payload)
            ->assertSessionHasErrors(['answers.'.$question->id]);

        $this->assertDatabaseEmpty('conversation_room_responses');
    }

    public function test_grouped_question_requires_one_answer_from_each_group(): void
    {
        $this->seed(SurveyQuestionSeeder::class);
        $payload = $this->validSurveyPayload();
        $question = SurveyQuestion::query()->with('options')->where('key', 'preferred_session_time')->sole();
        $payload['answers'][$question->id] = [$question->options->first()->id];

        $this->post(route('conversation-room.store'), $payload)
            ->assertSessionHasErrors(['answers.'.$question->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validSurveyPayload(): array
    {
        $questions = SurveyQuestion::query()->with('options')->active()->ordered()->get();
        $answers = [];

        foreach ($questions as $question) {
            $answers[$question->id] = match ($question->key) {
                'attendance_reasons' => [],
                'encouragement_changes', 'preferred_days' => $question->options->take(2)->pluck('id')->all(),
                'preferred_session_time' => $question->options
                    ->groupBy('group_name')
                    ->map(fn ($options) => $options->first()->id)
                    ->all(),
                'one_change' => 'More evening sessions and smaller groups.',
                default => $question->options->first()->id,
            };
        }

        return [
            'survey_version' => SurveyVersion::query()->active()->value('number'),
            'answers' => $answers,
        ];
    }

    private function createSectionWithQuestion(
        SurveyVersion $version,
        string $title,
        int $sortOrder,
        bool $sectionActive = true,
        bool $questionActive = true,
    ): void {
        $section = SurveySection::factory()
            ->for($version, 'version')
            ->create([
                'title' => $title,
                'sort_order' => $sortOrder,
                'is_active' => $sectionActive,
            ]);

        SurveyQuestion::factory()
            ->for($version, 'version')
            ->for($section, 'surveySection')
            ->create([
                'section' => $title,
                'sort_order' => 1,
                'is_active' => $questionActive,
            ]);
    }

    private function createActiveSurveyVersion(int $number): SurveyVersion
    {
        SurveyVersion::query()->active()->update(['status' => SurveyVersion::STATUS_ARCHIVED]);

        return SurveyVersion::factory()->active()->create(['number' => $number]);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function sectionCounts(): array
    {
        return [
            'three sections' => [3],
            'four sections' => [4],
            'five sections' => [5],
            'six sections' => [6],
            'eight sections' => [8],
            'ten sections' => [10],
        ];
    }
}
