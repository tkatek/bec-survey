<?php

namespace Database\Factories;

use App\Models\SurveyVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyVersion>
 */
class SurveyVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numberBetween(10, 60000),
            'status' => SurveyVersion::STATUS_ARCHIVED,
            'published_at' => now(),
            'created_by' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => SurveyVersion::STATUS_ACTIVE,
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => SurveyVersion::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }
}
