<?php

namespace Database\Factories;

use App\Models\SurveyQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SurveyQuestion>
 */
class SurveyQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stable_id' => (string) Str::uuid(),
            'key' => fake()->unique()->slug(3),
            'section' => fake()->randomElement(SurveyQuestion::sections()),
            'prompt' => fake()->sentence().'?',
            'help_text' => 'Choose one answer.',
            'type' => SurveyQuestion::TYPE_SINGLE_CHOICE,
            'is_required' => true,
            'max_selections' => null,
            'sort_order' => fake()->unique()->numberBetween(20, 2000),
            'is_active' => true,
        ];
    }
}
