<?php

namespace Database\Seeders;

use App\Models\ConversationRoomResponse;
use Illuminate\Database\Seeder;

class ConversationRoomResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ConversationRoomResponse::factory()
            ->count(50)
            ->create()
            ->each(function (ConversationRoomResponse $response): void {
                $answersByQuestion = [
                    'attendance_reasons' => fake()->randomElements([
                        'My schedule has changed.',
                        'The available session times are inconvenient.',
                        "I haven't been motivated to join.",
                        "I'm nervous about speaking or making mistakes.",
                        'Other personal reasons.',
                    ], fake()->numberBetween(0, 3)),
                    'encouragement_changes' => fake()->randomElements([
                        'More real-life conversation topics',
                        'Smaller groups and more speaking time',
                        'More personalised feedback from teachers',
                        'More flexible session times',
                        'Friendly sessions for shy learners',
                    ], fake()->numberBetween(1, 3)),
                    'preferred_days' => fake()->randomElements([
                        'Monday',
                        'Tuesday',
                        'Wednesday',
                        'Thursday',
                        'Friday',
                        'Saturday',
                        'Sunday',
                    ], fake()->numberBetween(1, 4)),
                ];

                $response->choices()->createMany(
                    collect($answersByQuestion)
                        ->flatMap(fn (array $answers, string $questionKey): array => collect($answers)
                            ->map(fn (string $answer): array => [
                                'question_key' => $questionKey,
                                'option_value' => $answer,
                            ])
                            ->all())
                        ->all(),
                );
            });
    }
}
