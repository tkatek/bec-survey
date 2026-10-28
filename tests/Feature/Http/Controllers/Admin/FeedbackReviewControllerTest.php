<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\ConversationRoomResponse;
use App\Models\FeedbackTheme;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyVersion;
use App\Models\User;
use Database\Seeders\FeedbackThemeSeeder;
use Database\Seeders\SurveyQuestionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FeedbackReviewControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([SurveyQuestionSeeder::class, FeedbackThemeSeeder::class]);
    }

    public function test_administrator_can_review_written_feedback_with_multiple_themes(): void
    {
        $answer = $this->writtenAnswer('<script>alert("x")</script> More evening sessions, please.');
        $admin = User::factory()->administrator()->create();

        $this->actingAs($admin)
            ->put(route('admin.feedback.review', $answer), [
                'themes' => ['scheduling', 'other'],
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseCount('feedback_theme_assignments', 2);
        $this->assertDatabaseHas('feedback_theme_assignments', [
            'survey_answer_id' => $answer->id,
            'feedback_theme_id' => FeedbackTheme::query()->where('key', 'scheduling')->value('id'),
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_feedback_is_escaped_and_non_administrators_cannot_change_tags(): void
    {
        $answer = $this->writtenAnswer('<img src=x onerror=alert(1)>');
        $query = [
            'preset' => 'month',
            'tab' => 'feedback',
        ];

        $this->actingAs(User::factory()->create())
            ->put(route('admin.feedback.review', $answer), ['themes' => []])
            ->assertForbidden();

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.common-answers', $query))
            ->assertOk()
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    private function writtenAnswer(string $text): SurveyAnswer
    {
        $question = SurveyQuestion::query()->where('key', 'one_change')->sole();
        $response = ConversationRoomResponse::factory()->create([
            'survey_version' => SurveyVersion::query()->active()->value('number'),
            'created_at' => now(),
        ]);

        return $response->answers()->create([
            'survey_question_id' => $question->id,
            'answer_value' => $text,
        ]);
    }
}
