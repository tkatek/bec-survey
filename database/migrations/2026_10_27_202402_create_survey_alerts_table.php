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
        Schema::create('survey_alert_rule_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('rule_key', 80);
            $table->foreignId('survey_version_id')->constrained('survey_versions')->cascadeOnDelete();
            $table->foreignId('survey_question_option_id')->nullable()->constrained('survey_question_options')->nullOnDelete();
            $table->string('disabled_reason')->nullable();
            $table->timestamps();

            $table->unique(['rule_key', 'survey_version_id'], 'survey_alert_rule_version_unique');
        });

        Schema::create('survey_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('active_key', 160)->nullable()->unique();
            $table->string('rule_key', 80);
            $table->unsignedSmallInteger('survey_version');
            $table->string('title');
            $table->string('severity', 20)->default('warning');
            $table->text('explanation');
            $table->unsignedInteger('matching_count');
            $table->unsignedInteger('denominator');
            $table->unsignedInteger('threshold');
            $table->dateTime('window_start');
            $table->dateTime('window_end');
            $table->json('evidence_response_ids')->nullable();
            $table->uuid('source_option_stable_id')->nullable();
            $table->dateTime('first_triggered_at');
            $table->dateTime('last_evaluated_at');
            $table->dateTime('read_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resolution_reason', 40)->nullable();
            $table->timestamps();

            $table->index(['resolved_at', 'read_at', 'last_evaluated_at'], 'survey_alerts_state_idx');
            $table->index(['rule_key', 'survey_version', 'created_at'], 'survey_alerts_rule_history_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_alerts');
        Schema::dropIfExists('survey_alert_rule_mappings');
    }
};
