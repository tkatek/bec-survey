<?php

namespace App\Models;

use Database\Factories\FeedbackThemeAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['survey_answer_id', 'feedback_theme_id', 'reviewed_by', 'reviewed_at'])]
class FeedbackThemeAssignment extends Model
{
    /** @use HasFactory<FeedbackThemeAssignmentFactory> */
    use HasFactory;

    /** @return BelongsTo<SurveyAnswer, $this> */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(SurveyAnswer::class, 'survey_answer_id');
    }

    /** @return BelongsTo<FeedbackTheme, $this> */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(FeedbackTheme::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }
}
