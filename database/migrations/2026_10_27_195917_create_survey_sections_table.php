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
        Schema::create('survey_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_version_id')->constrained()->restrictOnDelete();
            $table->uuid('stable_id');
            $table->string('title', 120);
            $table->string('description', 255)->nullable();
            $table->unsignedSmallInteger('sort_order');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['survey_version_id', 'stable_id'], 'survey_sections_version_stable_unique');
            $table->unique(['survey_version_id', 'title'], 'survey_sections_version_title_unique');
            $table->index(
                ['survey_version_id', 'is_active', 'sort_order'],
                'survey_sections_version_active_order_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_sections');
    }
};
