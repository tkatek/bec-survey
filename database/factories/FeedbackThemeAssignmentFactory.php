<?php

namespace Database\Factories;

use App\Models\FeedbackTheme;
use App\Models\FeedbackThemeAssignment;
use App\Models\SurveyAnswer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedbackThemeAssignment>
 */
class FeedbackThemeAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_answer_id' => SurveyAnswer::factory(),
            'feedback_theme_id' => FeedbackTheme::factory(),
            'reviewed_by' => User::factory()->administrator(),
            'reviewed_at' => now(),
        ];
    }
}
