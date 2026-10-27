<?php

namespace Database\Factories;

use App\Models\SurveyAlert;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SurveyAlert>
 */
class SurveyAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'active_key' => fake()->unique()->slug(3),
            'rule_key' => fake()->slug(2),
            'survey_version' => 1,
            'title' => fake()->sentence(5),
            'severity' => 'warning',
            'explanation' => fake()->sentence(),
            'matching_count' => 11,
            'denominator' => 20,
            'threshold' => 11,
            'window_start' => now()->subDays(29)->startOfDay(),
            'window_end' => now(),
            'evidence_response_ids' => [],
            'source_option_stable_id' => Str::uuid(),
            'first_triggered_at' => now(),
            'last_evaluated_at' => now(),
        ];
    }
}
