<?php

namespace App\Models;

use Database\Factories\SurveyQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'survey_version_id',
    'survey_section_id',
    'stable_id',
    'key',
    'section',
    'prompt',
    'help_text',
    'type',
    'is_required',
    'max_selections',
    'sort_order',
    'is_active',
])]
class SurveyQuestion extends Model
{
    /** @use HasFactory<SurveyQuestionFactory> */
    use HasFactory, SoftDeletes;

    public const TYPE_SINGLE_CHOICE = 'single_choice';

    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    public const TYPE_GROUPED_CHOICE = 'grouped_choice';

    public const TYPE_LONG_TEXT = 'long_text';

    /**
     * @return array<string, string>
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_SINGLE_CHOICE => 'Choose one',
            self::TYPE_MULTIPLE_CHOICE => 'Choose multiple',
            self::TYPE_GROUPED_CHOICE => 'Choose one per group',
            self::TYPE_LONG_TEXT => 'Written response',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function sections(): array
    {
        return [
            'Attendance and participation',
            'The updated Conversation Room',
            'Your preferences',
            'Your feedback',
        ];
    }

    /**
     * @param  Builder<SurveyQuestion>  $query
     * @return Builder<SurveyQuestion>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<SurveyQuestion>  $query
     * @return Builder<SurveyQuestion>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy(
                SurveySection::query()
                    ->select('sort_order')
                    ->whereColumn('survey_sections.id', 'survey_questions.survey_section_id'),
            )
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** @return BelongsTo<SurveyVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(SurveyVersion::class, 'survey_version_id');
    }

    /** @return BelongsTo<SurveySection, $this> */
    public function surveySection(): BelongsTo
    {
        return $this->belongsTo(SurveySection::class, 'survey_section_id');
    }

    /**
     * @return HasMany<SurveyQuestionOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(SurveyQuestionOption::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<SurveyAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'max_selections' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
