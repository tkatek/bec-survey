<?php

namespace Database\Factories;

use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyQuestionOption>
 */
class SurveyQuestionOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_question_id' => SurveyQuestion::factory(),
            'label' => fake()->sentence(3),
            'icon' => 'message-circle',
            'tone' => fake()->randomElement(['blue', 'green', 'orange', 'purple', 'red', 'neutral']),
            'group_name' => null,
            'sort_order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }
}
