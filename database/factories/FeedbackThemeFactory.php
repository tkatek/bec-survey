<?php

namespace Database\Factories;

use App\Models\FeedbackTheme;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedbackTheme>
 */
class FeedbackThemeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'label' => fake()->unique()->words(2, true),
        ];
    }
}
