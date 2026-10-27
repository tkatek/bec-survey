<?php

namespace Database\Factories;

use App\Models\SurveySection;
use App\Models\SurveyVersion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SurveySection>
 */
class SurveySectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_version_id' => SurveyVersion::factory(),
            'stable_id' => (string) Str::uuid(),
            'title' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'sort_order' => fake()->unique()->numberBetween(1, 2000),
            'is_active' => true,
        ];
    }
}
