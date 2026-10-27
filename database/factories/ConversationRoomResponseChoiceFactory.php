<?php

namespace Database\Factories;

use App\Models\ConversationRoomResponse;
use App\Models\ConversationRoomResponseChoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversationRoomResponseChoice>
 */
class ConversationRoomResponseChoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $optionsByQuestion = [
            'attendance_reasons' => [
                'My schedule has changed.',
                'The available session times are inconvenient.',
                'Other personal reasons.',
            ],
            'encouragement_changes' => [
                'More real-life conversation topics',
                'Smaller groups and more speaking time',
                'More flexible session times',
            ],
            'preferred_days' => [
                'Monday',
                'Wednesday',
                'Saturday',
            ],
        ];
        $questionKey = fake()->randomElement(array_keys($optionsByQuestion));

        return [
            'conversation_room_response_id' => ConversationRoomResponse::factory(),
            'question_key' => $questionKey,
            'option_value' => fake()->randomElement($optionsByQuestion[$questionKey]),
        ];
    }
}
