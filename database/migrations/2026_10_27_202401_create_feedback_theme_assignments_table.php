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
        Schema::create('feedback_theme_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_answer_id')->constrained('survey_answers')->cascadeOnDelete();
            $table->foreignId('feedback_theme_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at');
            $table->timestamps();

            $table->unique(['survey_answer_id', 'feedback_theme_id'], 'feedback_theme_answer_unique');
            $table->index(['feedback_theme_id', 'reviewed_at'], 'feedback_theme_reviewed_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_theme_assignments');
    }
};
