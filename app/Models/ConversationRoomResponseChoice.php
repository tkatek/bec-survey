<?php

namespace App\Models;

use Database\Factories\ConversationRoomResponseChoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['question_key', 'option_value'])]
class ConversationRoomResponseChoice extends Model
{
    /** @use HasFactory<ConversationRoomResponseChoiceFactory> */
    use HasFactory;

    public $timestamps = false;

    /**
     * Get the survey response that owns this selected option.
     *
     * @return BelongsTo<ConversationRoomResponse, $this>
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(ConversationRoomResponse::class, 'conversation_room_response_id');
    }
}
