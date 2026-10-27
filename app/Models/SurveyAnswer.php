<?php

namespace App\Models;

use Database\Factories\SurveyAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'survey_question_id',
    'survey_question_option_id',
    'answer_value',
    'group_name',
])]
class SurveyAnswer extends Model
{
    /** @use HasFactory<SurveyAnswerFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<ConversationRoomResponse, $this>
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(ConversationRoomResponse::class, 'conversation_room_response_id');
    }

    /**
     * @return BelongsTo<SurveyQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id')->withTrashed();
    }

    /**
     * @return BelongsTo<SurveyQuestionOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestionOption::class, 'survey_question_option_id')->withTrashed();
    }

    /** @return HasMany<FeedbackThemeAssignment, $this> */
    public function themeAssignments(): HasMany
    {
        return $this->hasMany(FeedbackThemeAssignment::class);
    }
}
