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
        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_room_response_id')
                ->constrained('conversation_room_responses')
                ->cascadeOnDelete();
            $table->foreignId('survey_question_id')
                ->constrained('survey_questions')
                ->restrictOnDelete();
            $table->foreignId('survey_question_option_id')
                ->nullable()
                ->constrained('survey_question_options')
                ->nullOnDelete();
            $table->text('answer_value')->nullable();
            $table->string('group_name', 60)->nullable();
            $table->timestamps();

            $table->index(
                ['survey_question_id', 'survey_question_option_id'],
                'survey_answers_question_option_idx',
            );
            $table->index(
                ['conversation_room_response_id', 'survey_question_id'],
                'survey_answers_response_question_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
