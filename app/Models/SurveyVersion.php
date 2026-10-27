<?php

namespace App\Models;

use Database\Factories\SurveyVersionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'status', 'published_at', 'created_by'])]
class SurveyVersion extends Model
{
    /** @use HasFactory<SurveyVersionFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ARCHIVED = 'archived';

    /** @param Builder<SurveyVersion> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /** @param Builder<SurveyVersion> $query */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<SurveySection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(SurveySection::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return HasMany<SurveyQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'published_at' => 'datetime',
        ];
    }
}
