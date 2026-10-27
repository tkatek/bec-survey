<?php

namespace App\Models;

use Database\Factories\ConversationRoomResponseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'survey_version',
    'attendance_frequency',
    'attendance_change',
    'updated_room_use',
    'ease_of_use',
    'activity_usefulness',
    'speaking_comfort',
    'session_format',
    'preferred_session_time',
    'time_zone',
    'session_length',
    'attendance_likelihood',
    'one_change',
])]
class ConversationRoomResponse extends Model
{
    /** @use HasFactory<ConversationRoomResponseFactory> */
    use HasFactory;

    /**
     * Get the multi-select answers recorded for the response.
     *
     * @return HasMany<ConversationRoomResponseChoice, $this>
     */
    public function choices(): HasMany
    {
        return $this->hasMany(ConversationRoomResponseChoice::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'survey_version' => 'integer',
        ];
    }
}
