<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversation_room_response_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_room_response_id');
            $table->string('question_key', 40);
            $table->string('option_value', 150);

            $table->unique(
                ['conversation_room_response_id', 'question_key', 'option_value'],
                'cr_response_choice_unique',
            );
            $table->index(['question_key', 'option_value'], 'cr_choices_question_option_idx');
            $table->foreign('conversation_room_response_id', 'cr_choices_response_fk')
                ->references('id')
                ->on('conversation_room_responses')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_room_response_choices');
    }
};
