<?php

namespace App\Models;

use Database\Factories\SurveyAlertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'active_key',
    'rule_key',
    'survey_version',
    'title',
    'severity',
    'explanation',
    'matching_count',
    'denominator',
    'threshold',
    'window_start',
    'window_end',
    'evidence_response_ids',
    'source_option_stable_id',
    'first_triggered_at',
    'last_evaluated_at',
    'read_at',
    'resolved_at',
    'resolved_by',
    'resolution_reason',
])]
class SurveyAlert extends Model
{
    /** @use HasFactory<SurveyAlertFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'matching_count' => 'integer',
            'denominator' => 'integer',
            'threshold' => 'integer',
            'window_start' => 'datetime',
            'window_end' => 'datetime',
            'evidence_response_ids' => 'array',
            'first_triggered_at' => 'datetime',
            'last_evaluated_at' => 'datetime',
            'read_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }
}
