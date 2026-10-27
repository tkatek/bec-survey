<?php

namespace Database\Factories;

use App\Models\ConversationRoomResponse;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyAnswer>
 */
class SurveyAnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_room_response_id' => ConversationRoomResponse::factory(),
            'survey_question_id' => SurveyQuestion::factory(),
            'survey_question_option_id' => null,
            'answer_value' => fake()->sentence(),
            'group_name' => null,
        ];
    }

    public function forOption(SurveyQuestionOption $option): static
    {
        return $this->state(fn (): array => [
            'survey_question_id' => $option->survey_question_id,
            'survey_question_option_id' => $option->id,
            'answer_value' => $option->label,
            'group_name' => $option->group_name,
        ]);
    }
}
