<?php

namespace App\Models;

use Database\Factories\FeedbackThemeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'label'])]
class FeedbackTheme extends Model
{
    /** @use HasFactory<FeedbackThemeFactory> */
    use HasFactory;

    /** @return HasMany<FeedbackThemeAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(FeedbackThemeAssignment::class);
    }
}
