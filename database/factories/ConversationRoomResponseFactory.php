<?php

namespace Database\Factories;

use App\Models\ConversationRoomResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversationRoomResponse>
 */
class ConversationRoomResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_version' => 1,
            'attendance_frequency' => fake()->randomElement([
                'Never',
                'Once or twice',
                'About once a week',
                'Several times a week',
                'Almost every day',
            ]),
            'attendance_change' => fake()->randomElement([
                'I attend more often now.',
                'My attendance is about the same.',
                'I attend less often now.',
                'I have stopped attending.',
                "I joined after the update / I didn't attend before.",
            ]),
            'updated_room_use' => fake()->randomElement([
                'Yes, several times.',
                'Yes, once.',
                'No, not yet.',
                "I wasn't aware it had been updated.",
            ]),
            'ease_of_use' => fake()->randomElement(['Very easy', 'Somewhat easy', 'Somewhat difficult', 'Very difficult', 'Not sure']),
            'activity_usefulness' => fake()->randomElement(['Very useful', 'Somewhat useful', 'Not very useful', 'Not useful at all', 'Not sure yet']),
            'speaking_comfort' => fake()->randomElement(['Very comfortable', 'Somewhat comfortable', 'Somewhat uncomfortable', 'Very uncomfortable', 'It depends on the group or activity.']),
            'session_format' => fake()->randomElement(['Small-group conversations', 'Pair speaking activities', 'Teacher-led discussion with feedback', 'Interactive speaking games and challenges', 'A combination of formats']),
            'preferred_session_time' => fake()->randomElement(['Morning (6 AM–12 PM)', 'Afternoon (12–5 PM)', 'Evening (5–9 PM)', 'Late evening (after 9 PM)', 'My availability changes.']),
            'time_zone' => fake()->randomElement(['Eastern', 'Central', 'Mountain', 'Pacific', 'Other']),
            'session_length' => fake()->randomElement(['15 minutes', '30 minutes', '45 minutes', '60 minutes']),
            'attendance_likelihood' => fake()->randomElement(['Very likely', 'Somewhat likely', 'Not sure', 'Somewhat unlikely', 'Very unlikely']),
            'one_change' => fake()->optional()->sentence(),
        ];
    }
}
